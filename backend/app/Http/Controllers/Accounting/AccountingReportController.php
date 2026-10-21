<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\LedgerAccount;
use App\Services\Accounting\AccountingReportService;
use App\Services\Accounting\StockReceiptService;
use Illuminate\Http\Request;

/**
 * The financial report pack (slice 5).
 *
 * Every endpoint is read-only and gated by `finance-reports.view`. Query
 * parameters are validated here (a malformed date is a 422, not a silently
 * empty report) and the heavy lifting stays in the service.
 */
class AccountingReportController extends Controller
{
    public function __construct(protected AccountingReportService $reports)
    {
    }

    /** Ledger / Account statement for one account. */
    public function ledgerStatement(Request $request, LedgerAccount $ledger)
    {
        $this->authorize('finance-reports.view');

        $filters = $this->filters($request);

        return response()->json($this->reports->ledgerStatement($ledger, $filters));
    }

    public function dayBook(Request $request)
    {
        $this->authorize('finance-reports.view');

        $filters = $this->filters($request, [
            'voucher_type' => 'nullable|string|max:40',
        ]);

        return response()->json($this->reports->dayBook($filters));
    }

    public function outstanding(Request $request)
    {
        $this->authorize('finance-reports.view');

        $filters = $this->filters($request, [
            'side' => 'nullable|in:receivable,payable,both',
            'as_of' => 'nullable|date',
            'ledger_id' => 'nullable|integer',
        ]);

        return response()->json($this->reports->outstanding($filters));
    }

    public function profitAndLoss(Request $request)
    {
        $this->authorize('finance-reports.view');

        return response()->json($this->reports->profitAndLoss($this->filters($request)));
    }

    public function balanceSheet(Request $request)
    {
        $this->authorize('finance-reports.view');

        $filters = $this->filters($request, ['as_of' => 'nullable|date']);

        return response()->json($this->reports->balanceSheet($filters));
    }

    public function cashBankBook(Request $request)
    {
        $this->authorize('finance-reports.view');

        $filters = $this->filters($request, ['ledger_id' => 'nullable|integer']);

        return response()->json($this->reports->cashBankBook($filters));
    }

    public function trialBalance(Request $request)
    {
        $this->authorize('finance-reports.view');

        $filters = $this->filters($request, ['as_of' => 'nullable|date']);

        return response()->json($this->reports->trialBalance($filters));
    }

    /**
     * Goods receipt / delivery breakdown for a period.
     *
     * Gated on `stock-receipts.view` rather than `finance-reports.view`: this is
     * warehouse data, so the storekeeper who books the receipts can read it
     * without being given the accounts pack.
     */
    public function receiptBreakdown(Request $request, StockReceiptService $receipts)
    {
        $this->authorize('stock-receipts.view');

        $filters = $this->filters($request, [
            'voucher_type' => 'nullable|string|max:40',
            'party_ledger_id' => 'nullable|integer',
            'location_id' => 'nullable|integer',
        ]);

        return response()->json($receipts->breakdown($filters));
    }

    /**
     * Shared query-parameter validation. Returns the whitelisted keys only so
     * nothing unexpected reaches the service.
     *
     * @return array<string, mixed>
     */
    protected function filters(Request $request, array $extra = []): array
    {
        return $request->validate(array_merge([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ], $extra));
    }
}
