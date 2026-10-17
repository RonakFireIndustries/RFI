<?php

namespace App\Services\Accounting;

use App\Models\FinancialYear;
use App\Models\Voucher;
use App\Models\VoucherBillAllocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * The double-entry voucher engine.
 *
 * Everything that writes a voucher goes through here so the invariants live in
 * one place: the voucher must balance, its number is generated per type and
 * financial year, and its Dr/Cr lines and bill-wise details are rebuilt
 * atomically on save.
 */
class VoucherService
{
    public function create(array $data, int $userId): Voucher
    {
        $type = $this->assertType($data['voucher_type']);
        $entries = $this->normaliseEntries($data['entries'] ?? []);
        $total = $this->assertBalanced($entries);

        return DB::transaction(function () use ($data, $type, $entries, $total, $userId) {
            $financialYear = $this->resolveFinancialYear($data['voucher_date']);
            $status = $data['status'] ?? 'posted';

            $voucher = Voucher::create([
                'voucher_type' => $type,
                'voucher_no' => $this->nextNumber($type, $financialYear, $data['voucher_date']),
                'voucher_date' => $data['voucher_date'],
                'financial_year_id' => $financialYear?->id,
                'reference_no' => $data['reference_no'] ?? null,
                'narration' => $data['narration'] ?? null,
                'status' => $status,
                'party_ledger_id' => $this->resolvePartyLedger($type, $data, $entries),
                'amount' => $total,
                'created_by' => $userId,
                'updated_by' => $userId,
                'posted_at' => $status === 'posted' ? now() : null,
            ]);

            $this->saveEntries($voucher, $entries);
            $this->saveAllocations($voucher, $data, $total);

            return $voucher->load($this->relations());
        });
    }

    public function update(Voucher $voucher, array $data, int $userId): Voucher
    {
        $type = $this->assertType($data['voucher_type'] ?? $voucher->voucher_type);
        $entries = $this->normaliseEntries($data['entries'] ?? []);
        $total = $this->assertBalanced($entries);

        return DB::transaction(function () use ($voucher, $data, $type, $entries, $total, $userId) {
            $financialYear = $this->resolveFinancialYear($data['voucher_date']);

            // The number embeds the type prefix and the financial year, so it is
            // only regenerated when either of those actually moved.
            $renumber = $voucher->voucher_type !== $type
                || $voucher->financial_year_id !== $financialYear?->id;

            $voucher->update([
                'voucher_type' => $type,
                'voucher_date' => $data['voucher_date'],
                'financial_year_id' => $financialYear?->id,
                'reference_no' => $data['reference_no'] ?? null,
                'narration' => $data['narration'] ?? null,
                'status' => $data['status'] ?? $voucher->status,
                'party_ledger_id' => $this->resolvePartyLedger($type, $data, $entries),
                'amount' => $total,
                'updated_by' => $userId,
                'posted_at' => ($data['status'] ?? $voucher->status) === 'posted'
                    ? ($voucher->posted_at ?: now())
                    : null,
                'voucher_no' => $renumber
                    ? $this->nextNumber($type, $financialYear, $data['voucher_date'])
                    : $voucher->voucher_no,
            ]);

            $voucher->entries()->delete();
            $voucher->billAllocations()->delete();

            $this->saveEntries($voucher, $entries);
            $this->saveAllocations($voucher, $data, $total);

            return $voucher->fresh()->load($this->relations());
        });
    }

    public function delete(Voucher $voucher): void
    {
        DB::transaction(function () use ($voucher) {
            $voucher->billAllocations()->delete();
            $voucher->entries()->delete();
            $voucher->delete();
        });
    }

    /** @return array<int, string> */
    public function relations(): array
    {
        return [
            'entries.ledger:id,name,group_id,is_bank,is_cash,party_type',
            'party:id,name',
            'billAllocations.ledger:id,name',
            'financialYear:id,name',
            'creator:id,name',
        ];
    }

    public function typeConfig(string $type): array
    {
        return config("accounting.voucher_types.{$type}", []);
    }

    /* ------------------------------ internals ----------------------------- */

    protected function assertType(?string $type): string
    {
        if (! $type || ! isset(config('accounting.voucher_types')[$type])) {
            throw ValidationException::withMessages([
                'voucher_type' => 'Unknown voucher type.',
            ]);
        }

        return $type;
    }

    /**
     * @param  array<int, array<string, mixed>>  $entries
     * @return array<int, array<string, mixed>>
     */
    protected function normaliseEntries(array $entries): array
    {
        return array_values(array_map(static fn ($entry) => [
            'ledger_id' => (int) $entry['ledger_id'],
            'entry_type' => $entry['entry_type'],
            'amount' => round((float) $entry['amount'], 2),
            'narration' => $entry['narration'] ?? null,
        ], $entries));
    }

    /**
     * @param  array<int, array<string, mixed>>  $entries
     */
    protected function assertBalanced(array $entries): float
    {
        if (count($entries) < 2) {
            throw ValidationException::withMessages([
                'entries' => 'A voucher needs at least one debit and one credit line.',
            ]);
        }

        $debit = round(array_sum(array_map(
            static fn ($e) => $e['entry_type'] === 'Dr' ? $e['amount'] : 0,
            $entries
        )), 2);

        $credit = round(array_sum(array_map(
            static fn ($e) => $e['entry_type'] === 'Cr' ? $e['amount'] : 0,
            $entries
        )), 2);

        if ($debit <= 0) {
            throw ValidationException::withMessages([
                'entries' => 'The voucher amount must be greater than zero.',
            ]);
        }

        if (abs($debit - $credit) >= 0.01) {
            throw ValidationException::withMessages([
                'entries' => 'The voucher is not balanced: debits must equal credits.',
            ]);
        }

        return $debit;
    }

    /**
     * Find the financial year covering the date, falling back to the active
     * year when none is configured for it yet.
     */
    protected function resolveFinancialYear(string $date): ?FinancialYear
    {
        $year = FinancialYear::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        return $year ?: FinancialYear::active();
    }

    /**
     * Sequential per-type, per-financial-year number, e.g. PMT-2026-0001.
     */
    protected function nextNumber(string $type, ?FinancialYear $financialYear, string $date): string
    {
        $prefix = config("accounting.voucher_types.{$type}.prefix", strtoupper($type)) . '-';
        $year = $financialYear
            ? (int) $financialYear->start_date->format('Y')
            : (int) date('Y', strtotime($date));

        $stem = $prefix . $year . '-';

        $last = Voucher::where('voucher_no', 'like', $stem . '%')
            ->orderByDesc('voucher_no')
            ->value('voucher_no');

        $sequence = $last ? ((int) substr($last, strlen($stem))) + 1 : 1;

        do {
            $candidate = $stem . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $exists = Voucher::where('voucher_no', $candidate)->exists();
            $sequence++;
        } while ($exists);

        return $candidate;
    }

    /**
     * @param  array<int, array<string, mixed>>  $entries
     */
    protected function resolvePartyLedger(string $type, array $data, array $entries): ?int
    {
        if (! empty($data['party_ledger_id'])) {
            return (int) $data['party_ledger_id'];
        }

        // Party vouchers default to the first party-owned ledger on the line.
        if (! $this->typeConfig($type)['bills']) {
            return null;
        }

        $ledgerIds = array_unique(array_map(static fn ($e) => $e['ledger_id'], $entries));
        $partyLedger = \App\Models\LedgerAccount::whereIn('id', $ledgerIds)
            ->whereNotNull('party_type')
            ->value('id');

        return $partyLedger ? (int) $partyLedger : null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $entries
     */
    protected function saveEntries(Voucher $voucher, array $entries): void
    {
        $line = 1;
        foreach ($entries as $entry) {
            $voucher->entries()->create([
                'line_no' => $line++,
                'ledger_id' => $entry['ledger_id'],
                'entry_type' => $entry['entry_type'],
                'amount' => $entry['amount'],
                'narration' => $entry['narration'],
            ]);
        }
    }

    protected function saveAllocations(Voucher $voucher, array $data, float $total): void
    {
        $allocations = array_values(array_filter($data['bill_allocations'] ?? []));

        if (empty($allocations)) {
            return;
        }

        if (! $this->typeConfig($voucher->voucher_type)['bills']) {
            throw ValidationException::withMessages([
                'bill_allocations' => 'This voucher type does not accept bill-wise details.',
            ]);
        }

        if (! $voucher->party_ledger_id) {
            throw ValidationException::withMessages([
                'bill_allocations' => 'Bill-wise details require a party ledger.',
            ]);
        }

        $allocated = round(array_sum(array_map(
            static fn ($a) => round((float) $a['amount'], 2),
            $allocations
        )), 2);

        if ($allocated - $total >= 0.01) {
            throw ValidationException::withMessages([
                'bill_allocations' => 'Bill allocations cannot exceed the voucher amount.',
            ]);
        }

        foreach ($allocations as $allocation) {
            $amount = round((float) $allocation['amount'], 2);
            if ($amount <= 0) {
                continue;
            }

            VoucherBillAllocation::create([
                'voucher_id' => $voucher->id,
                'ledger_id' => $voucher->party_ledger_id,
                'bill_type' => $allocation['bill_type'],
                'bill_id' => $allocation['bill_id'] ?? null,
                'bill_name' => $allocation['bill_name'] ?? null,
                'amount' => $amount,
            ]);
        }
    }
}
