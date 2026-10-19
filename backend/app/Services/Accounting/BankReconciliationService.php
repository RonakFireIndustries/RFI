<?php

namespace App\Services\Accounting;

use App\Models\LedgerAccount;
use App\Models\VoucherEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

/**
 * Manual bank reconciliation (slice 4).
 *
 * The bank book for a ledger is its opening balance brought forward plus every
 * posted voucher line that hits it. Reconciliation is purely manual: an entry
 * is "cleared" once it carries a reconciled date (the date it appeared on the
 * statement).
 *
 * The statement works in Tally's own terms:
 *
 *   balance as per books   = opening + Drs - Crs (all posted lines)
 *   less  deposits in transit   (unreconciled Dr lines)
 *   add   cheques not presented (unreconciled Cr lines)
 *   ------------------------------------------------
 *   balance as per bank    = what the statement should show
 */
class BankReconciliationService
{
    /**
     * Every bank ledger with its current book balance and unreconciled totals.
     *
     * @return array<int, array<string, mixed>>
     */
    public function bankLedgers(): array
    {
        return LedgerAccount::where('is_bank', true)
            ->orderBy('name')
            ->get()
            ->map(fn (LedgerAccount $ledger) => $this->ledgerSummary($ledger))
            ->all();
    }

    /**
     * The reconciliation statement for one bank ledger.
     *
     * Filters: `from` / `to` frame the listed period (entries before `from`
     * roll into the opening balance); `status` = all|reconciled|unreconciled
     * only trims the returned list — the summary is always over the whole
     * period so the reconciliation stays meaningful.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function statement(LedgerAccount $ledger, array $filters = []): array
    {
        $this->assertBank($ledger);

        $from = $this->dateOrNull($filters['from'] ?? null);
        $to = $this->dateOrNull($filters['to'] ?? null);
        $status = $filters['status'] ?? 'all';

        $opening = $this->openingBalance($ledger, $from);

        $entries = $this->entries($ledger, $from, $to);

        $running = $opening;
        $rows = [];
        $reconciledDr = 0.0;
        $reconciledCr = 0.0;
        $unreconciledDr = 0.0;
        $unreconciledCr = 0.0;

        foreach ($entries as $entry) {
            $amount = round((float) $entry->amount, 2);
            $isDebit = $entry->entry_type === 'Dr';

            $running = round($running + ($isDebit ? $amount : -$amount), 2);

            if ($entry->reconciled_at !== null) {
                $isDebit ? $reconciledDr += $amount : $reconciledCr += $amount;
            } else {
                $isDebit ? $unreconciledDr += $amount : $unreconciledCr += $amount;
            }

            $rows[] = [
                'id' => $entry->id,
                'voucher_id' => $entry->voucher_id,
                'voucher_no' => $entry->voucher?->voucher_no,
                'voucher_type' => $entry->voucher?->voucher_type,
                'voucher_date' => $entry->voucher?->voucher_date?->toDateString(),
                'reference_no' => $entry->voucher?->reference_no,
                'narration' => $entry->narration ?: $entry->voucher?->narration,
                'entry_type' => $entry->entry_type,
                'amount' => $amount,
                'balance' => $running,
                'reconciled' => $entry->reconciled_at !== null,
                'reconciled_at' => $entry->reconciled_at?->toDateString(),
                'reconciled_by' => $entry->reconciledBy?->name,
                'reconciliation_note' => $entry->reconciliation_note,
            ];
        }

        $unreconciledDr = round($unreconciledDr, 2);
        $unreconciledCr = round($unreconciledCr, 2);
        $bookBalance = $running;
        $statementBalance = round($bookBalance - $unreconciledDr + $unreconciledCr, 2);

        $visible = match ($status) {
            'reconciled' => array_values(array_filter($rows, fn ($r) => $r['reconciled'])),
            'unreconciled' => array_values(array_filter($rows, fn ($r) => ! $r['reconciled'])),
            default => $rows,
        };

        return [
            'ledger' => $this->ledgerInfo($ledger),
            'opening_balance' => round($opening, 2),
            'entries' => $visible,
            'summary' => [
                'book_balance' => $bookBalance,
                'reconciled_dr' => round($reconciledDr, 2),
                'reconciled_cr' => round($reconciledCr, 2),
                'unreconciled_dr' => $unreconciledDr,
                'unreconciled_cr' => $unreconciledCr,
                'unreconciled_count' => count(array_filter($rows, fn ($r) => ! $r['reconciled'])),
                'total_count' => count($rows),
                'statement_balance' => $statementBalance,
            ],
        ];
    }

    /**
     * Mark the given lines (belonging to this ledger and posted) as cleared.
     *
     * @param  array<int, mixed>  $entryIds
     * @return int number of lines actually reconciled
     */
    public function reconcile(LedgerAccount $ledger, array $entryIds, string $date, ?string $note, int $userId): int
    {
        $this->assertBank($ledger);
        $ids = $this->normaliseIds($entryIds, 'entry_ids');

        return VoucherEntry::query()
            ->whereIn('id', $ids)
            ->where('ledger_id', $ledger->id)
            ->whereHas('voucher', fn (Builder $q) => $q->where('status', 'posted'))
            ->update([
                'reconciled_at' => $date,
                'reconciled_by' => $userId,
                'reconciliation_note' => $note,
                'updated_at' => now(),
            ]);
    }

    /**
     * Clear the reconciliation on the given lines.
     *
     * @param  array<int, mixed>  $entryIds
     * @return int number of lines cleared
     */
    public function unreconcile(LedgerAccount $ledger, array $entryIds): int
    {
        $this->assertBank($ledger);
        $ids = $this->normaliseIds($entryIds, 'entry_ids');

        return VoucherEntry::query()
            ->whereIn('id', $ids)
            ->where('ledger_id', $ledger->id)
            ->update([
                'reconciled_at' => null,
                'reconciled_by' => null,
                'reconciliation_note' => null,
                'updated_at' => now(),
            ]);
    }

    /* ------------------------------ internals ----------------------------- */

    /**
     * @return array<string, mixed>
     */
    protected function ledgerSummary(LedgerAccount $ledger): array
    {
        $opening = $this->signedOpening($ledger);
        $book = round($opening + $this->net($this->entryQuery($ledger)), 2);

        $unreconciled = $this->net($this->entryQuery($ledger)->unreconciled());
        $unreconciledDr = $this->sumSide($this->entryQuery($ledger)->unreconciled(), 'Dr');
        $unreconciledCr = $this->sumSide($this->entryQuery($ledger)->unreconciled(), 'Cr');

        $count = $this->entryQuery($ledger)->unreconciled()->count();

        return array_merge($this->ledgerInfo($ledger), [
            'opening_balance' => round($opening, 2),
            'book_balance' => $book,
            'unreconciled_dr' => $unreconciledDr,
            'unreconciled_cr' => $unreconciledCr,
            'unreconciled_count' => $count,
            'unreconciled_net' => round($unreconciled, 2),
            'statement_balance' => round($book - $unreconciledDr + $unreconciledCr, 2),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function ledgerInfo(LedgerAccount $ledger): array
    {
        return [
            'id' => $ledger->id,
            'name' => $ledger->name,
            'bank_name' => $ledger->bank_name,
            'bank_account_number' => $ledger->bank_account_number,
            'is_cash' => (bool) $ledger->is_cash,
        ];
    }

    /**
     * Opening balance expressed with a sign: Dr positive, Cr negative.
     */
    protected function signedOpening(LedgerAccount $ledger): float
    {
        $opening = round((float) $ledger->opening_balance, 2);

        return $ledger->opening_type === 'Cr' ? -$opening : $opening;
    }

    /**
     * The balance carried into the statement period. Entries dated before
     * `from` are folded in so the running balance stays correct when the user
     * frames a period.
     */
    protected function openingBalance(LedgerAccount $ledger, ?string $from): float
    {
        $opening = $this->signedOpening($ledger);

        if (! $from) {
            return $opening;
        }

        $before = $this->net($this->entryQuery($ledger)->whereDate('vouchers.voucher_date', '<', $from));

        return round($opening + $before, 2);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, VoucherEntry>
     */
    protected function entries(LedgerAccount $ledger, ?string $from, ?string $to)
    {
        $query = $this->entryQuery($ledger)
            ->select('voucher_entries.*')
            ->with([
                'voucher:id,voucher_no,voucher_type,voucher_date,reference_no,narration,status',
                'reconciledBy:id,name',
            ]);

        $this->applyDates($query, $from, $to);

        return $query
            ->orderBy('vouchers.voucher_date')
            ->orderBy('vouchers.id')
            ->orderBy('voucher_entries.line_no')
            ->get();
    }

    /**
     * Base query: posted voucher lines against this ledger, joined so entries
     * can be ordered and filtered by their voucher's date.
     */
    protected function entryQuery(LedgerAccount $ledger): Builder
    {
        return VoucherEntry::query()
            ->join('vouchers', 'vouchers.id', '=', 'voucher_entries.voucher_id')
            ->where('voucher_entries.ledger_id', $ledger->id)
            ->where('vouchers.status', 'posted');
    }

    protected function applyDates(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $query->whereDate('vouchers.voucher_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('vouchers.voucher_date', '<=', $to);
        }

        return $query;
    }

    /**
     * Net movement (Dr minus Cr) over whatever the query selects.
     */
    protected function net(Builder $query): float
    {
        $row = $query
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN voucher_entries.entry_type = 'Dr' "
                . 'THEN voucher_entries.amount ELSE -voucher_entries.amount END), 0) as net'
            )
            ->first();

        return round((float) ($row->net ?? 0), 2);
    }

    protected function sumSide(Builder $query, string $side): float
    {
        $row = $query
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN voucher_entries.entry_type = ? "
                . 'THEN voucher_entries.amount ELSE 0 END), 0) as total',
                [$side]
            )
            ->first();

        return round((float) ($row->total ?? 0), 2);
    }

    /**
     * @param  array<int, mixed>  $entryIds
     * @return array<int, int>
     */
    protected function normaliseIds(array $entryIds, string $field): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map('intval', $entryIds),
            fn (int $id) => $id > 0
        )));

        if (empty($ids)) {
            throw ValidationException::withMessages([
                $field => 'Select at least one transaction to reconcile.',
            ]);
        }

        return $ids;
    }

    protected function assertBank(LedgerAccount $ledger): void
    {
        if (! $ledger->is_bank) {
            throw ValidationException::withMessages([
                'ledger' => 'Only bank ledgers can be reconciled.',
            ]);
        }
    }

    protected function dateOrNull(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        return substr((string) $value, 0, 10);
    }
}
