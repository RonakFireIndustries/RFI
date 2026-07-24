<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FollowUp;
use App\Services\NotificationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowUpController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('building.view');

        $query = FollowUp::with(['building', 'opportunity', 'siteVisit', 'buildingContact', 'user']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        } else {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'Pending');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->where('reminder_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('reminder_date', '<=', $request->date_to);
        }

        $perPage = (int) $request->input('per_page', 15);
        $followUps = $query->orderBy('reminder_date')->paginate($perPage);

        return $this->success('Follow-ups retrieved', [
            'follow_ups' => $followUps->items(),
            'pagination' => [
                'total' => $followUps->total(),
                'per_page' => $followUps->perPage(),
                'current_page' => $followUps->currentPage(),
                'last_page' => $followUps->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('building.edit');

        $validated = $request->validate([
            'building_id' => ['nullable', 'exists:buildings,id'],
            'opportunity_id' => ['nullable', 'exists:opportunities,id'],
            'site_visit_id' => ['nullable', 'exists:site_visits,id'],
            'building_contact_id' => ['nullable', 'exists:building_contacts,id'],
            'reminder_date' => ['required', 'date'],
            'reminder_time' => ['nullable', 'date_format:H:i'],
            'type' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['status'] = 'Pending';

        $followUp = FollowUp::create($validated);

        NotificationService::create(
            $request->user()->id,
            'Follow-up scheduled',
            ucfirst($followUp->type) . ' follow-up on ' . $followUp->reminder_date,
            'info',
            '/dashboard/follow-ups',
        );

        return $this->success('Follow-up created', ['follow_up' => $followUp->load(['building', 'opportunity', 'user'])], [], 201);
    }

    public function show(FollowUp $followUp): JsonResponse
    {
        $this->authorize('building.view');
        $followUp->load(['building', 'opportunity', 'siteVisit', 'buildingContact', 'user']);
        return $this->success('Follow-up retrieved', ['follow_up' => $followUp]);
    }

    public function update(Request $request, FollowUp $followUp): JsonResponse
    {
        $this->authorize('building.edit');

        $validated = $request->validate([
            'reminder_date' => ['sometimes', 'date'],
            'reminder_time' => ['nullable', 'date_format:H:i'],
            'type' => ['sometimes', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', 'in:Pending,Completed,Cancelled'],
        ]);

        $followUp->update($validated);

        return $this->success('Follow-up updated', ['follow_up' => $followUp->fresh()->load(['building', 'opportunity', 'user'])]);
    }

    public function destroy(FollowUp $followUp): JsonResponse
    {
        $this->authorize('building.delete');
        $followUp->delete();
        return $this->success('Follow-up deleted');
    }

    public function markComplete(FollowUp $followUp): JsonResponse
    {
        $this->authorize('building.edit');
        $followUp->update(['status' => 'Completed']);
        return $this->success('Follow-up marked complete', ['follow_up' => $followUp->fresh()->load(['building', 'opportunity', 'user'])]);
    }

    public function myFollowUps(Request $request): JsonResponse
    {
        $followUps = FollowUp::with(['building', 'opportunity'])
            ->where('user_id', $request->user()->id)
            ->where('status', 'Pending')
            ->orderBy('reminder_date')
            ->limit(20)
            ->get();

        return $this->success('My follow-ups retrieved', ['follow_ups' => $followUps]);
    }
}
