<?php

namespace App\Services\Accounting;

use App\Models\AccountGroup;
use App\Models\FinancialYear;
use App\Models\LedgerAccount;
use App\Models\Voucher;
use App\Models\VoucherEntry;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * The financial report pack (slice 5): Ledger Statement, Day Book, Outstandings,
 * Profit & Loss, Balance Sheet, Cash/Bank Book and Trial Balance.
 *
 * Everything is derived from the books — posted voucher lines plus each ledger's
 * brought-forward opening — so no report ever stores a number of its own.
 *
 * Sign convention used throughout: a balance is "net" = opening + Dr - Cr, i.e.
 * DEBITS are positive. Each report then flips the sign for the side that is
 * natively a credit (liabilities, income) to present Tally-style columns.
 */
class AccountingReportService
{
    public function __construct(protected PartyOutstandingService $outstanding)
    {
    }

    /* ------------------------------------------------------------------ */
    /* Ledger / Account statement                                          */
    /* ------------------------------------------------------------------ */

    /**
     * Chronological statement of one account for a period: opening balance,
     * every line, a running balance, and the closing balance.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function ledgerStatement(LedgerAccount $ledger, array $filters = []): array
    {
        [$from, $to] = $this->resolvePeriod($filters);

        $opening = $this->signedOpening($ledger);
        if ($from !== null) {
            $opening = round($opening + $this->netOf($this->movements($from, null, '<')[$ledger->id] ?? null), 2);
        }

        $query = VoucherEntry::query()
            ->join('vouchers', 'vouchers.id', '=', 'voucher_entries.voucher_id')
            ->where('voucher_entries.ledger_id', $ledger->id)
            ->where('vouchers.status', 'posted')
            ->select('voucher_entries.*')
            ->with(['voucher:id,voucher_no,voucher_type,voucher_date,reference_no,narration,status,party_ledger_id']);

        $this->applyDates($query, $from, $to);

        $rows = $query
            ->orderBy('vouchers.voucher_date')
            ->orderBy('vouchers.id')
            ->orderBy('voucher_entries.line_no')
            ->get();

        $running = $opening;
        $debitTotal = 0.0;
        $creditTotal = 0.0;
        $entries = [];

        foreach ($rows as $row) {
            $amount = round((float) $row->amount, 2);
            $isDebit = $row->entry_type === 'Dr';

            $isDebit ? $debitTotal += $amount : $creditTotal += $amount;
            $running = round($running + ($isDebit ? $amount : -$amount), 2);

            $entries[] = [
                'id' => $row->id,
                'voucher_id' => $row->voucher_id,
                'voucher_no' => $row->voucher?->voucher_no,
                'voucher_type' => $row->voucher?->voucher_type,
                'voucher_date' => $this->dateStr($row->voucher?->voucher_date),
                'reference_no' => $row->voucher?->reference_no,
                'narration' => $row->narration ?: $row->voucher?->narration,
                'entry_type' => $row->entry_type,
                'amount' => $amount,
                'debit' => $isDebit ? $amount : 0.0,
                'credit' => $isDebit ? 0.0 : $amount,
                'balance' => $running,
                'balance_side' => $running >= 0 ? 'Dr' : 'Cr',
            ];
        }

        return [
            'ledger' => $this->ledgerInfo($ledger),
            'period' => ['from' => $from, 'to' => $to],
            'opening_balance' => round($opening, 2),
            'opening_side' => $opening >= 0 ? 'Dr' : 'Cr',
            'entries' => $entries,
            'debit_total' => round($debitTotal, 2),
            'credit_total' => round($creditTotal, 2),
            'closing_balance' => round($running, 2),
            'closing_side' => $running >= 0 ? 'Dr' : 'Cr',
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Day Book                                                            */
    /* ------------------------------------------------------------------ */

    /**
     * Every posted line in a period, grouped visually by voucher — the "what
     * actually got posted" view.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function dayBook(array $filters = []): array
    {
        [$from, $to] = $this->resolvePeriod($filters);

        $query = Voucher::query()
            ->with([
                'entries:id,voucher_id,ledger_id,entry_type,amount,narration,line_no',
                'entries.ledger:id,name',
                'party:id,name',
            ])
            ->where('status', 'posted')
            ->orderBy('voucher_date')
            ->orderBy('id');

        if ($type = $this->strOrNull($filters['voucher_type'] ?? null)) {
            $query->where('voucher_type', $type);
        }

        $this->applyDates($query, $from, $to);

        $vouchers = $query->get();

        $rows = [];
        $debitTotal = 0.0;
        $creditTotal = 0.0;

        foreach ($vouchers as $voucher) {
            $voucherDr = 0.0;
            $voucherCr = 0.0;

            foreach ($voucher->entries as $entry) {
                $amount = round((float) $entry->amount, 2);
                $isDebit = $entry->entry_type === 'Dr';
                $isDebit ? $voucherDr += $amount : $voucherCr += $amount;

                $rows[] = [
                    'voucher_id' => $voucher->id,
                    'voucher_no' => $voucher->voucher_no,
                    'voucher_type' => $voucher->voucher_type,
                    'voucher_date' => $this->dateStr($voucher->voucher_date),
                    'reference_no' => $voucher->reference_no,
                    'party_name' => $voucher->party?->name,
                    'narration' => $entry->narration ?: $voucher->narration,
                    'ledger_id' => $entry->ledger_id,
                    'ledger_name' => $entry->ledger?->name,
                    'entry_type' => $entry->entry_type,
                    'amount' => $amount,
                    'debit' => $isDebit ? $amount : 0.0,
                    'credit' => $isDebit ? 0.0 : $amount,
                ];
            }

            $debitTotal += $voucherDr;
            $creditTotal += $voucherCr;
        }

        return [
            'period' => ['from' => $from, 'to' => $to],
            'voucher_count' => $vouchers->count(),
            'rows' => $rows,
            'debit_total' => round($debitTotal, 2),
            'credit_total' => round($creditTotal, 2),
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Outstanding receivables / payables                                  */
    /* ------------------------------------------------------------------ */

    /**
     * Bill-wise outstanding with aging.
     *
     * `side` = receivable (what customers owe), payable (what we owe suppliers)
     * or both. Bill lines come straight from PartyOutstandingService so the
     * report and the per-party screen can never disagree.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function outstanding(array $filters = []): array
    {
        $side = $this->strOrNull($filters['side'] ?? null) ?? 'receivable';
        if (! in_array($side, ['receivable', 'payable', 'both'], true)) {
            throw ValidationException::withMessages([
                'side' => 'Side must be receivable, payable or both.',
            ]);
        }

        $asOf = $this->dateOrNull($filters['as_of'] ?? null) ?? now()->toDateString();
        $sides = $side === 'both' ? ['customer', 'supplier'] : [$side === 'payable' ? 'supplier' : 'customer'];

        $ledgerQuery = LedgerAccount::whereIn('party_type', $sides);
        if ($ledgerId = (int) ($filters['ledger_id'] ?? 0)) {
            $ledgerQuery->whereKey($ledgerId);
        }

        $rows = [];
        $totals = ['receivable' => 0.0, 'payable' => 0.0];
        $buckets = $this->emptyBuckets();

        foreach ($ledgerQuery->orderBy('name')->get() as $ledger) {
            $report = $this->outstanding->forLedger($ledger);

            foreach ($report['bills'] as $bill) {
                if (abs($bill['balance']) < 0.005) {
                    continue;
                }

                $kind = $ledger->party_type === 'supplier' ? 'payable' : 'receivable';
                $days = $this->ageInDays($bill, $asOf);
                $bucket = $this->bucketFor($days);
                $dueDate = $this->dateStr($bill['due_date'] ?? null);

                $rows[] = [
                    'ledger_id' => $ledger->id,
                    'party_name' => $ledger->name,
                    'side' => $kind,
                    'bill_type' => $bill['bill_type'],
                    'bill_id' => $bill['bill_id'],
                    'bill_name' => $bill['bill_name'],
                    'date' => $this->dateStr($bill['date']),
                    'due_date' => $dueDate,
                    'total' => $bill['total'],
                    'settled' => $bill['settled'],
                    'balance' => $bill['balance'],
                    'days' => $days,
                    'bucket' => $bucket,
                    // Only a real due date can make a bill "overdue"; without one
                    // `days`/`bucket` age the bill from its date instead.
                    'overdue' => $dueDate !== null && $dueDate < $asOf,
                ];

                $totals[$kind] = round($totals[$kind] + $bill['balance'], 2);
                $buckets[$bucket] = round($buckets[$bucket] + abs($bill['balance']), 2);
            }
        }

        usort($rows, fn (array $a, array $b) => [$a['side'], $a['party_name'], (string) $a['date']]
            <=> [$b['side'], $b['party_name'], (string) $b['date']]);

        return [
            'as_of' => $asOf,
            'side' => $side,
            'bills' => $rows,
            'party_count' => count(array_unique(array_column($rows, 'ledger_id'))),
            'bill_count' => count($rows),
            'totals' => $totals,
            'grand_total' => round($totals['receivable'] + $totals['payable'], 2),
            'aging' => $buckets,
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Profit & Loss                                                       */
    /* ------------------------------------------------------------------ */

    /**
     * Income vs expenses for a period, split Tally-style into direct (gross
     * profit) and indirect (net profit).
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function profitAndLoss(array $filters = []): array
    {
        [$from, $to] = $this->resolvePeriod($filters);

        $movements = $this->movements($from, $to);
        $ledgers = $this->ledgersWithGroups();

        // Two buckets keyed by side; keyed access avoids PHP's reference-quirky
        // `$bucket = $cond ? &$a : &$b` assignment.
        $buckets = ['income' => [], 'expenses' => []];

        foreach ($ledgers as $ledger) {
            $group = $ledger->group;
            if ($group === null) {
                continue;
            }
            if ($group->nature !== 'Income' && $group->nature !== 'Expenses') {
                continue;
            }

            $balance = $this->netOf($movements[$ledger->id] ?? null);

            // Present the way Tally does: income is a credit, expense a debit.
            $presented = $group->nature === 'Income' ? -$balance : $balance;
            if (abs($presented) < 0.005) {
                continue;
            }

            $key = $group->nature === 'Income' ? 'income' : 'expenses';

            $buckets[$key][$group->name] ??= [
                'group' => $group->name,
                'direct' => (bool) $group->affects_gross_profit,
                'total' => 0.0,
                'accounts' => [],
            ];
            $buckets[$key][$group->name]['total'] = round($buckets[$key][$group->name]['total'] + $presented, 2);
            $buckets[$key][$group->name]['accounts'][] = [
                'ledger_id' => $ledger->id,
                'name' => $ledger->name,
                'amount' => $presented,
            ];
        }

        $income = $this->sortGroups($buckets['income']);
        $expenses = $this->sortGroups($buckets['expenses']);

        $directIncome = $this->sumWhere($income, true);
        $directExpenses = $this->sumWhere($expenses, true);
        $totalIncome = $this->sumAll($income);
        $totalExpenses = $this->sumAll($expenses);

        $grossProfit = round($directIncome - $directExpenses, 2);
        $netProfit = round($totalIncome - $totalExpenses, 2);

        return [
            'period' => ['from' => $from, 'to' => $to],
            'income' => $income,
            'expenses' => $expenses,
            'direct_income' => $directIncome,
            'direct_expenses' => $directExpenses,
            'gross_profit' => $grossProfit,
            'indirect_income' => round($totalIncome - $directIncome, 2),
            'indirect_expenses' => round($totalExpenses - $directExpenses, 2),
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'net_profit' => $netProfit,
            'is_loss' => $netProfit < 0,
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Balance Sheet                                                       */
    /* ------------------------------------------------------------------ */

    /**
     * Assets vs liabilities as at a date, with the year's profit carried in.
     *
     * The identity `Assets = Liabilities + Profit` holds because every entry
     * balances and the brought-forward openings balance; when it doesn't, the
     * `difference` field makes that visible instead of hiding it.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function balanceSheet(array $filters = []): array
    {
        $asOf = $this->dateOrNull($filters['to'] ?? null) ?? $this->dateOrNull($filters['as_of'] ?? null) ?? now()->toDateString();

        $movements = $this->movements(null, $asOf);
        $ledgers = $this->ledgersWithGroups();

        $buckets = ['assets' => [], 'liabilities' => []];
        $incomeTotal = 0.0;
        $expensesTotal = 0.0;

        foreach ($ledgers as $ledger) {
            $group = $ledger->group;
            if ($group === null) {
                continue;
            }

            $net = round($this->signedOpening($ledger) + $this->netOf($movements[$ledger->id] ?? null), 2);

            if ($group->nature === 'Income') {
                $incomeTotal += -$net;
                continue;
            }
            if ($group->nature === 'Expenses') {
                $expensesTotal += $net;
                continue;
            }

            if (abs($net) < 0.005) {
                continue;
            }

            // Assets are debit balances; liabilities are credit balances.
            $presented = $group->nature === 'Assets' ? $net : -$net;
            $key = $group->nature === 'Assets' ? 'assets' : 'liabilities';

            $buckets[$key][$group->name] ??= ['group' => $group->name, 'total' => 0.0, 'accounts' => []];
            $buckets[$key][$group->name]['total'] = round($buckets[$key][$group->name]['total'] + $presented, 2);
            $buckets[$key][$group->name]['accounts'][] = [
                'ledger_id' => $ledger->id,
                'name' => $ledger->name,
                'amount' => $presented,
            ];
        }

        $assets = $this->sortGroups($buckets['assets']);
        $liabilities = $this->sortGroups($buckets['liabilities']);

        $assetsTotal = round($this->sumAll($assets), 2);
        $liabilitiesTotal = round($this->sumAll($liabilities), 2);
        $profit = round($incomeTotal - $expensesTotal, 2);
        $difference = round($assetsTotal - $liabilitiesTotal - $profit, 2);

        return [
            'as_of' => $asOf,
            'assets' => $assets,
            'liabilities' => $liabilities,
            'assets_total' => $assetsTotal,
            'liabilities_total' => $liabilitiesTotal,
            'profit' => $profit,
            'profit_line' => $profit >= 0 ? 'Profit for the period' : 'Loss for the period',
            'liabilities_grand_total' => round($liabilitiesTotal + $profit, 2),
            'difference' => $difference,
            'is_balanced' => abs($difference) < 0.01,
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Cash / Bank Book                                                    */
    /* ------------------------------------------------------------------ */

    /**
     * Movement of every cash and bank account over a period. With `ledger_id`
     * the individual transaction lines are included too.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function cashBankBook(array $filters = []): array
    {
        [$from, $to] = $this->resolvePeriod($filters);

        $movements = $this->movements($from, $to);
        $before = $from !== null ? $this->movements($from, null, '<') : [];

        $ledgers = LedgerAccount::where(fn ($q) => $q->where('is_bank', true)->orWhere('is_cash', true))
            ->orderBy('name')
            ->get();

        $focus = (int) ($filters['ledger_id'] ?? 0);
        $focusLedger = $focus ? $ledgers->firstWhere('id', $focus) : null;

        $accounts = [];
        $totals = ['opening' => 0.0, 'debit' => 0.0, 'credit' => 0.0, 'closing' => 0.0];

        foreach ($ledgers as $ledger) {
            $movement = $movements[$ledger->id] ?? null;
            $debit = round((float) ($movement['dr'] ?? 0), 2);
            $credit = round((float) ($movement['cr'] ?? 0), 2);

            $opening = $this->signedOpening($ledger);
            if ($from !== null) {
                $opening = round($opening + $this->netOf($before[$ledger->id] ?? null), 2);
            }
            $closing = round($opening + $debit - $credit, 2);

            $accounts[] = [
                'ledger_id' => $ledger->id,
                'name' => $ledger->name,
                'bank_name' => $ledger->bank_name,
                'account_number' => $ledger->bank_account_number,
                'is_bank' => (bool) $ledger->is_bank,
                'opening' => $opening,
                'debit' => $debit,
                'credit' => $credit,
                'closing' => $closing,
            ];

            $totals['opening'] = round($totals['opening'] + $opening, 2);
            $totals['debit'] = round($totals['debit'] + $debit, 2);
            $totals['credit'] = round($totals['credit'] + $credit, 2);
            $totals['closing'] = round($totals['closing'] + $closing, 2);
        }

        return [
            'period' => ['from' => $from, 'to' => $to],
            'accounts' => $accounts,
            'totals' => $totals,
            'ledger' => $focusLedger ? $this->ledgerInfo($focusLedger) : null,
            'entries' => $focusLedger ? $this->ledgerStatement($focusLedger, $filters)['entries'] : [],
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Trial Balance                                                       */
    /* ------------------------------------------------------------------ */

    /**
     * Closing balances per account, each forced onto its natural side, so the
     * two columns must tie. A non-zero `difference` means the books themselves
     * don't add up.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function trialBalance(array $filters = []): array
    {
        $asOf = $this->dateOrNull($filters['to'] ?? null) ?? $this->dateOrNull($filters['as_of'] ?? null) ?? now()->toDateString();

        $movements = $this->movements(null, $asOf);
        $rows = [];

        foreach ($this->ledgersWithGroups() as $ledger) {
            $net = round($this->signedOpening($ledger) + $this->netOf($movements[$ledger->id] ?? null), 2);
            if (abs($net) < 0.005) {
                continue;
            }

            $rows[] = [
                'ledger_id' => $ledger->id,
                'name' => $ledger->name,
                'group' => $ledger->group?->name,
                'nature' => $ledger->group?->nature,
                'debit' => $net > 0 ? $net : 0.0,
                'credit' => $net < 0 ? round(-$net, 2) : 0.0,
            ];
        }

        usort($rows, fn (array $a, array $b) => [$a['nature'], $a['group'], $a['name']]
            <=> [$b['nature'], $b['group'], $b['name']]);

        $totalDebit = round(array_sum(array_column($rows, 'debit')), 2);
        $totalCredit = round(array_sum(array_column($rows, 'credit')), 2);

        return [
            'as_of' => $asOf,
            'rows' => $rows,
            'account_count' => count($rows),
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'difference' => round($totalDebit - $totalCredit, 2),
            'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Shared helpers                                                      */
    /* ------------------------------------------------------------------ */

    /**
     * Every ledger together with its group, the single load each report uses.
     *
     * @return Collection<int, LedgerAccount>
     */
    protected function ledgersWithGroups(): Collection
    {
        return LedgerAccount::with([
            'group:id,name,nature,affects_gross_profit,is_party_group,sort_order,parent_id',
        ])->get();
    }

    /**
     * Dr and Cr totals per ledger for a date window, in ONE grouped query.
     *
     * Pass `$fromOp = '<'` with only `$from` to get everything strictly before
     * a period — that is how the statement's carried-in opening is built.
     *
     * @return array<int, array{dr: float, cr: float}>
     */
    protected function movements(?string $from, ?string $to, string $fromOp = '>='): array
    {
        $query = VoucherEntry::query()
            ->join('vouchers', 'vouchers.id', '=', 'voucher_entries.voucher_id')
            ->where('vouchers.status', 'posted');

        if ($from !== null) {
            $query->whereDate('vouchers.voucher_date', $fromOp, $from);
        }
        if ($to !== null) {
            $query->whereDate('vouchers.voucher_date', '<=', $to);
        }

        $rows = $query
            ->groupBy('voucher_entries.ledger_id')
            ->selectRaw(
                "voucher_entries.ledger_id as ledger_id, "
                . "SUM(CASE WHEN voucher_entries.entry_type = 'Dr' THEN voucher_entries.amount ELSE 0 END) as dr, "
                . "SUM(CASE WHEN voucher_entries.entry_type = 'Cr' THEN voucher_entries.amount ELSE 0 END) as cr"
            )
            ->get();

        $totals = [];
        foreach ($rows as $row) {
            $totals[(int) $row->ledger_id] = [
                'dr' => round((float) $row->dr, 2),
                'cr' => round((float) $row->cr, 2),
            ];
        }

        return $totals;
    }

    /** Net movement (Dr - Cr) of a single movement entry. */
    protected function netOf(?array $movement): float
    {
        if ($movement === null) {
            return 0.0;
        }

        return round($movement['dr'] - $movement['cr'], 2);
    }

    /** Opening balance with a sign: Dr positive, Cr negative. */
    protected function signedOpening(LedgerAccount $ledger): float
    {
        $opening = round((float) $ledger->opening_balance, 2);

        return $ledger->opening_type === 'Cr' ? -$opening : $opening;
    }

    /**
     * Apply `from` / `to` to a query joined onto `vouchers`.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyDates($query, ?string $from, ?string $to)
    {
        if ($from !== null) {
            $query->whereDate('vouchers.voucher_date', '>=', $from);
        }
        if ($to !== null) {
            $query->whereDate('vouchers.voucher_date', '<=', $to);
        }

        return $query;
    }

    /**
     * Resolve a report period. `to` defaults to today, `from` to the start of
     * the active financial year so a fresh report always shows "this year".
     *
     * @param  array<string, mixed>  $filters
     * @return array{0: ?string, 1: ?string}
     */
    protected function resolvePeriod(array $filters): array
    {
        $to = $this->dateOrNull($filters['to'] ?? null) ?? now()->toDateString();
        $from = $this->dateOrNull($filters['from'] ?? null);

        if ($from === null) {
            $from = $this->activeFinancialYearStart();
        }

        if ($from !== null && $from > $to) {
            throw ValidationException::withMessages([
                'from' => 'The from date must be on or before the to date.',
            ]);
        }

        return [$from, $to];
    }

    protected function activeFinancialYearStart(): ?string
    {
        $year = FinancialYear::where('is_active', true)->orderByDesc('start_date')->first();

        return $this->dateStr($year?->start_date);
    }

    /**
     * Group prepared `['group' => ..., 'total' => ..., 'accounts' => ...]`
     * buckets by name and sort them largest-first for a stable report.
     *
     * @param  array<string, array<string, mixed>>  $groups
     * @return array<int, array<string, mixed>>
     */
    protected function sortGroups(array $groups): array
    {
        $groups = array_values($groups);

        usort($groups, function (array $a, array $b) {
            // Direct (gross-profit) groups first, then by size. `direct` only
            // exists on the P&L buckets, so fall back for the Balance Sheet.
            $directA = (bool) ($a['direct'] ?? false);
            $directB = (bool) ($b['direct'] ?? false);
            if ($directA !== $directB) {
                return $directB <=> $directA;
            }

            return [$a['total'] < 0, abs($a['total'])] <=> [$b['total'] < 0, abs($b['total'])];
        });

        foreach ($groups as &$group) {
            usort($group['accounts'], fn (array $a, array $b) => abs($b['amount']) <=> abs($a['amount']));
        }

        return $groups;
    }

    /**
     * @param  array<int, array<string, mixed>>  $groups
     */
    protected function sumAll(array $groups): float
    {
        return round(array_sum(array_column($groups, 'total')), 2);
    }

    /**
     * @param  array<int, array<string, mixed>>  $groups
     */
    protected function sumWhere(array $groups, bool $direct): float
    {
        return round(array_sum(array_map(
            fn (array $g) => ((bool) ($g['direct'] ?? false) === $direct) ? $g['total'] : 0.0,
            $groups
        )), 2);
    }

    /** @return array<string, float> */
    protected function emptyBuckets(): array
    {
        return ['Current' => 0.0, '1-30 days' => 0.0, '31-60 days' => 0.0, '61-90 days' => 0.0, 'Over 90 days' => 0.0];
    }

    /** Days a bill has been outstanding as at `asOf` (due date preferred). */
    protected function ageInDays(array $bill, string $asOf): int
    {
        $anchor = $this->dateStr($bill['due_date'] ?? null) ?? $this->dateStr($bill['date'] ?? null);
        if ($anchor === null) {
            return 0;
        }

        $days = (int) round(
            (strtotime($asOf) - strtotime($anchor)) / 86400
        );

        return max($days, 0);
    }

    protected function bucketFor(int $days): string
    {
        return match (true) {
            $days <= 0 => 'Current',
            $days <= 30 => '1-30 days',
            $days <= 60 => '31-60 days',
            $days <= 90 => '61-90 days',
            default => 'Over 90 days',
        };
    }

    /** @return array<string, mixed> */
    protected function ledgerInfo(LedgerAccount $ledger): array
    {
        return [
            'id' => $ledger->id,
            'name' => $ledger->name,
            'party_type' => $ledger->party_type,
            'is_bank' => (bool) $ledger->is_bank,
            'is_cash' => (bool) $ledger->is_cash,
            'opening_balance' => round((float) $ledger->opening_balance, 2),
            'opening_type' => $ledger->opening_type,
            'group' => $ledger->group?->name,
            'group_nature' => $ledger->group?->nature,
        ];
    }

    protected function strOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    protected function dateOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $this->dateStr($value);
    }

    /** Normalise casts, strings and Carbon values to Y-m-d. */
    protected function dateStr(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_object($value) && method_exists($value, 'toDateString')) {
            return $value->toDateString();
        }

        return substr((string) $value, 0, 10);
    }
}
