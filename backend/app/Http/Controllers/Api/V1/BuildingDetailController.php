<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\BuildingContact;
use App\Models\BuildingStatus;
use App\Models\BuildingWing;
use App\Models\FireSystem;
use App\Http\Resources\BuildingResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuildingDetailController extends Controller
{
    use ApiResponse;

    public function wings(Building $building): JsonResponse
    {
        $this->authorize('building.view');
        $wings = $building->wings()->get();
        return $this->success('Wings retrieved', ['wings' => $wings]);
    }

    public function storeWing(Request $request, Building $building): JsonResponse
    {
        $this->authorize('building.edit');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'floors' => ['nullable', 'integer', 'min:0'],
            'flats_per_floor' => ['nullable', 'integer', 'min:0'],
            'flat_configuration' => ['nullable', 'string'],
            'total_flats' => ['nullable', 'integer', 'min:0'],
        ]);

        $wing = $building->wings()->create($validated);
        return $this->success('Wing created', ['wing' => $wing], [], 201);
    }

    public function updateWing(Request $request, Building $building, BuildingWing $wing): JsonResponse
    {
        $this->authorize('building.edit');

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'floors' => ['nullable', 'integer', 'min:0'],
            'flats_per_floor' => ['nullable', 'integer', 'min:0'],
            'flat_configuration' => ['nullable', 'string'],
            'total_flats' => ['nullable', 'integer', 'min:0'],
        ]);

        $wing->update($validated);
        return $this->success('Wing updated', ['wing' => $wing]);
    }

    public function destroyWing(Building $building, BuildingWing $wing): JsonResponse
    {
        $this->authorize('building.delete');
        $wing->delete();
        return $this->success('Wing deleted');
    }

    public function fireSystems(Building $building): JsonResponse
    {
        $this->authorize('building.view');
        $systems = $building->fireSystems()->get();
        return $this->success('Fire systems retrieved', ['fire_systems' => $systems]);
    }

    public function storeFireSystem(Request $request, Building $building): JsonResponse
    {
        $this->authorize('building.edit');

        $validated = $request->validate([
            'system_type' => ['required', 'string', 'max:255'],
            'sub_type' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'capacity' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'installation_year' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'last_testing_date' => ['nullable', 'date'],
        ]);

        $system = $building->fireSystems()->create($validated);
        return $this->success('Fire system created', ['fire_system' => $system], [], 201);
    }

    public function updateFireSystem(Request $request, Building $building, FireSystem $fireSystem): JsonResponse
    {
        $this->authorize('building.edit');

        $validated = $request->validate([
            'system_type' => ['sometimes', 'string', 'max:255'],
            'sub_type' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'capacity' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'installation_year' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'last_testing_date' => ['nullable', 'date'],
        ]);

        $fireSystem->update($validated);
        return $this->success('Fire system updated', ['fire_system' => $fireSystem]);
    }

    public function destroyFireSystem(Building $building, FireSystem $fireSystem): JsonResponse
    {
        $this->authorize('building.delete');
        $fireSystem->delete();
        return $this->success('Fire system deleted');
    }

    public function contacts(Building $building): JsonResponse
    {
        $this->authorize('building.view');
        $contacts = $building->contacts()->get();
        return $this->success('Contacts retrieved', ['contacts' => $contacts]);
    }

    public function storeContact(Request $request, Building $building): JsonResponse
    {
        $this->authorize('building.edit');

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'role_category' => ['sometimes', 'string', 'in:society,developer,architect,pmc,other'],
            'mobile_number' => ['nullable', 'string', 'max:20'],
            'whatsapp_number' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $contact = $building->contacts()->create($validated);
        return $this->success('Contact created', ['contact' => $contact], [], 201);
    }

    public function updateContact(Request $request, Building $building, BuildingContact $contact): JsonResponse
    {
        $this->authorize('building.edit');

        $validated = $request->validate([
            'full_name' => ['sometimes', 'string', 'max:255'],
            'role' => ['sometimes', 'string', 'max:255'],
            'role_category' => ['sometimes', 'string', 'in:society,developer,architect,pmc,other'],
            'mobile_number' => ['nullable', 'string', 'max:20'],
            'whatsapp_number' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $contact->update($validated);
        return $this->success('Contact updated', ['contact' => $contact]);
    }

    public function destroyContact(Building $building, BuildingContact $contact): JsonResponse
    {
        $this->authorize('building.delete');
        $contact->delete();
        return $this->success('Contact deleted');
    }

    public function statuses(): JsonResponse
    {
        $this->authorize('building.view');
        $statuses = BuildingStatus::all();
        return $this->success('Statuses retrieved', ['statuses' => $statuses]);
    }

    public function assignStatus(Request $request, Building $building): JsonResponse
    {
        $this->authorize('building.edit');

        $validated = $request->validate([
            'building_status_id' => ['required', 'exists:building_statuses,id'],
        ]);

        $building->statuses()->syncWithoutDetaching([$validated['building_status_id']]);
        return $this->success('Status assigned', ['statuses' => $building->statuses]);
    }

    public function removeStatus(Building $building, BuildingStatus $buildingStatus): JsonResponse
    {
        $this->authorize('building.edit');
        $building->statuses()->detach($buildingStatus->id);
        return $this->success('Status removed');
    }

    public function dashboardStats(): JsonResponse
    {
        $this->authorize('building.view');

        $totalBuildings = Building::count();
        $totalOpportunities = \App\Models\Opportunity::where('status', 'Active')->count();
        $totalSiteVisits = \App\Models\SiteVisit::count();
        $totalFollowUps = \App\Models\FollowUp::where('status', 'Pending')->count();

        $pipeline = \App\Models\Opportunity::where('status', 'Active')
            ->selectRaw('stage, count(*) as count, sum(estimated_value) as value')
            ->groupBy('stage')
            ->get();

        $recentActivity = \App\Models\ActivityLog::with('user')
            ->latest()
            ->limit(10)
            ->get();

        $upcomingFollowUps = \App\Models\FollowUp::where('status', 'Pending')
            ->where('reminder_date', '>=', now())
            ->with(['building', 'user'])
            ->orderBy('reminder_date')
            ->limit(10)
            ->get();

        return $this->success('Dashboard stats retrieved', [
            'total_buildings' => $totalBuildings,
            'total_opportunities' => $totalOpportunities,
            'total_site_visits' => $totalSiteVisits,
            'total_pending_follow_ups' => $totalFollowUps,
            'pipeline' => $pipeline,
            'recent_activity' => $recentActivity,
            'upcoming_follow_ups' => $upcomingFollowUps,
        ]);
    }
}
