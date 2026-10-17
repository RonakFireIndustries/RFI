<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\FinancialYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Financial years. Exactly one year is active at a time.
 */
class FinancialYearController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('financial-years.view');

        return response()->json(
            FinancialYear::orderByDesc('start_date')->get()
        );
    }

    public function current(Request $request)
    {
        $this->authorize('financial-years.view');

        return response()->json(FinancialYear::active());
    }

    public function store(Request $request)
    {
        $this->authorize('financial-years.create');

        $validated = $this->validated($request);

        $year = DB::transaction(function () use ($validated, $request) {
            $year = FinancialYear::create($validated);

            // First year created, or explicitly requested, becomes active.
            if ($request->boolean('is_active') || FinancialYear::count() === 1) {
                $this->activateYear($year);
            }

            return $year->fresh();
        });

        return response()->json($year, 201);
    }

    public function show(FinancialYear $financialYear)
    {
        $this->authorize('financial-years.view');

        return response()->json($financialYear);
    }

    public function update(Request $request, FinancialYear $financialYear)
    {
        $this->authorize('financial-years.update');

        $validated = $this->validated($request, $financialYear->id);

        if (($validated['is_closed'] ?? false) && $financialYear->is_active) {
            return response()->json(['message' => 'The active year cannot be closed.'], 422);
        }

        $financialYear->update($validated);

        return response()->json($financialYear->fresh());
    }

    public function activate(FinancialYear $financialYear)
    {
        $this->authorize('financial-years.update');

        if ($financialYear->is_closed) {
            return response()->json(['message' => 'A closed year cannot be activated.'], 422);
        }

        DB::transaction(fn () => $this->activateYear($financialYear));

        return response()->json($financialYear->fresh());
    }

    public function destroy(FinancialYear $financialYear)
    {
        $this->authorize('financial-years.delete');

        if ($financialYear->is_active) {
            return response()->json(['message' => 'The active year cannot be removed.'], 422);
        }

        if ($financialYear->is_closed) {
            return response()->json(['message' => 'A closed year cannot be removed.'], 422);
        }

        $financialYear->delete();

        return response()->noContent();
    }

    protected function activateYear(FinancialYear $year): void
    {
        FinancialYear::where('id', '!=', $year->id)->update(['is_active' => false]);
        $year->update(['is_active' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:50',
                Rule::unique('financial_years', 'name')->ignore($ignoreId),
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
            'is_closed' => 'boolean',
        ]);
    }
}
