<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountGroup;
use App\Models\LedgerAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Tally-style ledger accounts.
 */
class LedgerAccountController extends Controller
{
    protected const WITH = ['group:id,name,nature,is_party_group,party_type'];

    public function index(Request $request)
    {
        $this->authorize('ledgers.view');

        $query = LedgerAccount::with(self::WITH);

        if ($groupId = $request->input('group_id')) {
            $query->where('group_id', $groupId);
        }

        if ($nature = $request->input('nature')) {
            $query->whereHas('group', fn ($q) => $q->where('nature', $nature));
        }

        if ($partyType = $request->input('party_type')) {
            $query->where('party_type', $partyType);
        }

        if ($request->boolean('bank_only')) {
            $query->where('is_bank', true);
        }

        if ($request->boolean('cash_only')) {
            $query->where('is_cash', true);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('gst_number', 'like', "%{$search}%")
                    ->orWhere('bank_account_number', 'like', "%{$search}%");
            });
        }

        return response()->json(
            $query->orderBy('name')->get()
        );
    }

    public function store(Request $request)
    {
        $this->authorize('ledgers.create');

        $validated = $this->validated($request);
        $validated = $this->applyGroupDefaults($validated);

        $ledger = LedgerAccount::create($validated);

        return response()->json($ledger->load(self::WITH), 201);
    }

    public function show(LedgerAccount $ledger)
    {
        $this->authorize('ledgers.view');

        return response()->json($ledger->load(self::WITH));
    }

    public function update(Request $request, LedgerAccount $ledger)
    {
        $this->authorize('ledgers.update');

        $validated = $this->validated($request, $ledger->id);
        $validated = $this->applyGroupDefaults($validated);

        $ledger->update($validated);

        return response()->json($ledger->load(self::WITH));
    }

    public function destroy(LedgerAccount $ledger)
    {
        $this->authorize('ledgers.delete');

        if ($ledger->is_system) {
            return response()->json(['message' => 'This is a default ledger and cannot be removed.'], 403);
        }

        $ledger->delete();

        return response()->noContent();
    }

    /**
     * When the ledger sits in a party group, default its party linkage from the
     * group (Sundry Debtors -> customer, Sundry Creditors -> supplier).
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function applyGroupDefaults(array $validated): array
    {
        if (empty($validated['group_id'])) {
            return $validated;
        }

        $group = AccountGroup::find($validated['group_id']);
        if ($group && $group->is_party_group && empty($validated['party_type'])) {
            $validated['party_type'] = $group->party_type;
        }

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('ledger_accounts', 'name')->ignore($ignoreId),
            ],
            'group_id' => 'required|integer|exists:account_groups,id',
            'opening_balance' => 'nullable|numeric|min:0',
            'opening_type' => ['nullable', Rule::in(['Dr', 'Cr'])],
            'is_bank' => 'boolean',
            'is_cash' => 'boolean',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_ifsc' => 'nullable|string|max:50',
            'bank_branch' => 'nullable|string|max:255',
            'gst_number' => 'nullable|string|max:50',
            'gst_type' => 'nullable|string|max:50',
            'pan_number' => 'nullable|string|max:50',
            'party_type' => ['nullable', Rule::in(['customer', 'supplier'])],
            'party_id' => 'nullable|integer',
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'notes' => 'nullable|string',
        ]);
    }
}
