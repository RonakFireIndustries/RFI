<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Opportunity;
use App\Models\OpportunityWorkType;
use App\Services\NotificationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OpportunityController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('building.view');

        $query = Opportunity::with(['building', 'user', 'workTypes']);

        if ($request->filled('building_id')) {
            $query->where('building_id', $request->building_id);
        }
        if ($request->filled('stage')) {
            $query->where('stage', $request->stage);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('building', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 15);
        $opportunities = $query->latest()->paginate($perPage);

        return $this->success('Opportunities retrieved', [
            'opportunities' => $opportunities->items(),
            'pagination' => [
                'total' => $opportunities->total(),
                'per_page' => $opportunities->perPage(),
                'current_page' => $opportunities->currentPage(),
                'last_page' => $opportunities->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('building.edit');

        $validated = $request->validate([
            'building_id' => ['required', 'exists:buildings,id'],
            'name' => ['required', 'string', 'max:255'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'stage' => ['sometimes', 'string', 'in:' . implode(',', Opportunity::STAGES)],
            'expected_closing_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'work_types' => ['nullable', 'array'],
            'work_types.*' => ['string', 'max:255'],
        ]);

        $workTypes = $validated['work_types'] ?? [];
        unset($validated['work_types']);

        $validated['user_id'] = $request->user()->id;
        $validated['stage'] = $validated['stage'] ?? 'Prospect';

        $opportunity = Opportunity::create($validated);

        foreach ($workTypes as $type) {
            $opportunity->workTypes()->create(['work_type' => $type]);
        }

        $this->logActivity($opportunity, 'created', 'Opportunity created');

        NotificationService::create(
            $request->user()->id,
            'New opportunity created',
            $opportunity->name . ' for ' . ($opportunity->building->name ?? 'unknown building'),
            'success',
            '/dashboard/opportunities/' . $opportunity->id,
        );

        return $this->success('Opportunity created', ['opportunity' => $opportunity->load(['building', 'user', 'workTypes'])], [], 201);
    }

    public function show(Opportunity $opportunity): JsonResponse
    {
        $this->authorize('building.view');
        $opportunity->load(['building', 'user', 'workTypes', 'documents', 'followUps']);
        return $this->success('Opportunity retrieved', ['opportunity' => $opportunity]);
    }

    public function update(Request $request, Opportunity $opportunity): JsonResponse
    {
        $this->authorize('building.edit');

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'stage' => ['sometimes', 'string', 'in:' . implode(',', Opportunity::STAGES)],
            'expected_closing_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'actual_final_value' => ['nullable', 'numeric', 'min:0'],
            'project_start_date' => ['nullable', 'date'],
            'lost_reason' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', 'in:Active,Inactive'],
            'work_types' => ['nullable', 'array'],
            'work_types.*' => ['string', 'max:255'],
        ]);

        if (isset($request->work_types)) {
            $opportunity->workTypes()->delete();
            foreach ($request->work_types as $type) {
                $opportunity->workTypes()->create(['work_type' => $type]);
            }
            unset($validated['work_types']);
        }

        if (isset($validated['stage']) && in_array($validated['stage'], ['Won', 'Lost'])) {
            $validated['status'] = 'Inactive';
        }

        $oldStage = $opportunity->stage;
        $opportunity->update($validated);

        $this->logActivity($opportunity, 'updated', 'Opportunity updated');

        if (isset($validated['stage']) && $validated['stage'] !== $oldStage) {
            NotificationService::create(
                $opportunity->user_id,
                'Opportunity stage changed',
                $opportunity->name . ' moved to ' . $validated['stage'],
                'info',
                '/dashboard/opportunities/' . $opportunity->id,
            );
        }

        return $this->success('Opportunity updated', ['opportunity' => $opportunity->fresh()->load(['building', 'user', 'workTypes'])]);
    }

    public function destroy(Opportunity $opportunity): JsonResponse
    {
        $this->authorize('building.delete');

        $opportunity->workTypes()->delete();
        $opportunity->delete();

        return $this->success('Opportunity deleted');
    }

    protected function logActivity($model, string $action, string $description): void
    {
        $model->activityLogs()->create([
            'user_id' => request()->user()?->id,
            'loggable_type' => get_class($model),
            'loggable_id' => $model->id,
            'action' => $action,
            'description' => $description,
        ]);
    }
}
