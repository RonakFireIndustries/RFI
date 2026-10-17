<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Tally-style account groups (the Chart of Accounts folders).
 */
class AccountGroupController extends Controller
{
    /** Columns shared by every response payload. */
    protected const WITH = ['parent:id,name'];

    protected const WITH_COUNT = ['parent:id,name'];

    public function index(Request $request)
    {
        $this->authorize('account-groups.view');

        $query = AccountGroup::with(self::WITH_COUNT)->withCount(['ledgers', 'children']);

        if ($nature = $request->input('nature')) {
            $query->where('nature', $nature);
        }

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->boolean('primary_only')) {
            $query->where('is_primary', true);
        }

        return response()->json(
            $query->orderBy('sort_order')->orderBy('name')->get()
        );
    }

    public function store(Request $request)
    {
        $this->authorize('account-groups.create');

        $validated = $this->validated($request);

        // A sub-group inherits its nature from its parent; a primary group must
        // state one.
        if (! empty($validated['parent_id']) && empty($validated['nature'])) {
            $parent = AccountGroup::find($validated['parent_id']);
            $validated['nature'] = $parent?->nature;
        }

        if (empty($validated['nature'])) {
            return response()->json([
                'message' => 'A nature is required.',
                'errors' => ['nature' => ['A nature is required for a primary group.']],
            ], 422);
        }

        $group = AccountGroup::create($validated);

        return response()->json($group->load(self::WITH)->loadCount(['ledgers', 'children']), 201);
    }

    public function show(AccountGroup $accountGroup)
    {
        $this->authorize('account-groups.view');

        return response()->json(
            $accountGroup->load(self::WITH)->loadCount(['ledgers', 'children'])
        );
    }

    public function update(Request $request, AccountGroup $accountGroup)
    {
        $this->authorize('account-groups.update');

        $validated = $this->validated($request, $accountGroup->id);

        // A group cannot be its own ancestor.
        if (isset($validated['parent_id']) && $this->isDescendant($accountGroup, $validated['parent_id'])) {
            return response()->json(['message' => 'A group cannot be moved under itself.'], 422);
        }

        // Keep the nature consistent when moving to a sub-group without stating one.
        if (! empty($validated['parent_id']) && empty($validated['nature'])) {
            $parent = AccountGroup::find($validated['parent_id']);
            $validated['nature'] = $parent?->nature;
        }

        if (empty($validated['nature'])) {
            $validated['nature'] = $accountGroup->nature;
        }

        $accountGroup->update($validated);

        return response()->json(
            $accountGroup->load(self::WITH)->loadCount(['ledgers', 'children'])
        );
    }

    public function destroy(AccountGroup $accountGroup)
    {
        $this->authorize('account-groups.delete');

        if ($accountGroup->is_system) {
            return response()->json(['message' => 'This is a default group and cannot be removed.'], 403);
        }

        if ($accountGroup->ledgers()->exists()) {
            return response()->json(['message' => 'Move or delete the ledgers in this group first.'], 422);
        }

        if ($accountGroup->children()->exists()) {
            return response()->json(['message' => 'Remove the sub-groups first.'], 422);
        }

        $accountGroup->delete();

        return response()->noContent();
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('account_groups', 'name')->ignore($ignoreId),
            ],
            'parent_id' => 'nullable|integer|exists:account_groups,id',
            'nature' => ['nullable', Rule::in(['Assets', 'Liabilities', 'Income', 'Expenses'])],
            'is_primary' => 'boolean',
            'affects_gross_profit' => 'boolean',
            'is_party_group' => 'boolean',
            'party_type' => ['nullable', Rule::in(['customer', 'supplier'])],
            'sort_order' => 'nullable|integer|min:0',
        ]);
    }

    /** Whether $candidateId is $group itself or one of its descendants. */
    protected function isDescendant(AccountGroup $group, int $candidateId): bool
    {
        if ($group->id === $candidateId) {
            return true;
        }

        $walk = AccountGroup::where('parent_id', $group->id)->pluck('id')->all();

        while ($walk) {
            if (in_array($candidateId, $walk, true)) {
                return true;
            }
            $walk = AccountGroup::whereIn('parent_id', $walk)->pluck('id')->all();
        }

        return false;
    }
}
