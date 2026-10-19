<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\LedgerAccount;
use App\Services\Accounting\BankReconciliationService;
use Illuminate\Http\Request;

/**
 * Manual bank reconciliation (slice 4).
 *
 * A BRS is read-mostly: `index` lists the bank ledgers, `show` renders one
 * ledger's statement, and reconcile/unreconcile stamp or clear the cleared
 * date on the voucher lines. All gated by the `bank-reconciliation` module.
 */
class BankReconciliationController extends Controller
{
    public function __construct(protected BankReconciliationService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('bank-reconciliation.view');

        return response()->json($this->service->bankLedgers());
    }

    public function show(Request $request, LedgerAccount $ledger)
    {
        $this->authorize('bank-reconciliation.view');

        return response()->json(
            $this->service->statement($ledger, $request->only(['from', 'to', 'status']))
        );
    }

    public function reconcile(Request $request, LedgerAccount $ledger)
    {
        $this->authorize('bank-reconciliation.update');

        $data = $request->validate([
            'entry_ids' => 'required|array|min:1',
            'entry_ids.*' => 'integer',
            'reconciled_at' => 'required|date',
            'note' => 'nullable|string|max:255',
        ]);

        $updated = $this->service->reconcile(
            $ledger,
            $data['entry_ids'],
            $data['reconciled_at'],
            $data['note'] ?? null,
            $request->user()->id,
        );

        return response()->json(['updated' => $updated]);
    }

    public function unreconcile(Request $request, LedgerAccount $ledger)
    {
        $this->authorize('bank-reconciliation.update');

        $data = $request->validate([
            'entry_ids' => 'required|array|min:1',
            'entry_ids.*' => 'integer',
        ]);

        $updated = $this->service->unreconcile($ledger, $data['entry_ids']);

        return response()->json(['updated' => $updated]);
    }
}
