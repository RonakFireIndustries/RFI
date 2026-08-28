<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\BuildingContact;
use App\Models\BuildingAmc;
use App\Models\BuildingStatus;
use App\Models\BuildingWing;
use App\Models\BuildingFloor;
use App\Models\BuildingFlat;
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
        $this->authorize('buildings.view');
        $wings = $building->wings()->get();
        return $this->success('Wings retrieved', ['wings' => $wings]);
    }

    public function storeWing(Request $request, Building $building): JsonResponse
    {
        $this->authorize('buildings.update');

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
        $this->authorize('buildings.update');

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
        $this->authorize('buildings.delete');
        $wing->delete();
        return $this->success('Wing deleted');
    }

    public function floors(Building $building): JsonResponse
    {
        $this->authorize('buildings.view');
        $floors = $building->floors()->with('wing')->get();
        return $this->success('Floors retrieved', ['floors' => $floors]);
    }

    public function storeFloor(Request $request, Building $building): JsonResponse
    {
        $this->authorize('buildings.update');

        $validated = $request->validate([
            'wing_id' => ['required', 'exists:building_wings,id'],
            'name' => ['required', 'string', 'max:255'],
            'floor_number' => ['nullable', 'integer'],
            'type' => ['nullable', 'string', 'max:255'],
        ]);

        $floor = $building->floors()->create($validated);
        return $this->success('Floor created', ['floor' => $floor], [], 201);
    }

    public function updateFloor(Request $request, Building $building, BuildingFloor $floor): JsonResponse
    {
        $this->authorize('buildings.update');

        $validated = $request->validate([
            'wing_id' => ['sometimes', 'exists:building_wings,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'floor_number' => ['nullable', 'integer'],
            'type' => ['nullable', 'string', 'max:255'],
        ]);

        $floor->update($validated);
        return $this->success('Floor updated', ['floor' => $floor]);
    }

    public function destroyFloor(Building $building, BuildingFloor $floor): JsonResponse
    {
        $this->authorize('buildings.delete');
        $floor->delete();
        return $this->success('Floor deleted');
    }

    public function flats(Building $building): JsonResponse
    {
        $this->authorize('buildings.view');
        $flats = $building->flats()->with(['wing', 'floor'])->get();
        return $this->success('Flats retrieved', ['flats' => $flats]);
    }

    public function storeFlat(Request $request, Building $building): JsonResponse
    {
        $this->authorize('buildings.update');

        $validated = $request->validate([
            'wing_id' => ['required', 'exists:building_wings,id'],
            'floor_id' => ['required', 'exists:building_floors,id'],
            'name' => ['required', 'string', 'max:255'],
            'flat_number' => ['nullable', 'string', 'max:255'],
            'bhk_type' => ['nullable', 'string', 'max:50'],
            'area' => ['nullable', 'numeric', 'min:0'],
        ]);

        $flat = $building->flats()->create($validated);
        return $this->success('Flat created', ['flat' => $flat], [], 201);
    }

    public function updateFlat(Request $request, Building $building, BuildingFlat $flat): JsonResponse
    {
        $this->authorize('buildings.update');

        $validated = $request->validate([
            'wing_id' => ['sometimes', 'exists:building_wings,id'],
            'floor_id' => ['sometimes', 'exists:building_floors,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'flat_number' => ['nullable', 'string', 'max:255'],
            'bhk_type' => ['nullable', 'string', 'max:50'],
            'area' => ['nullable', 'numeric', 'min:0'],
        ]);

        $flat->update($validated);
        return $this->success('Flat updated', ['flat' => $flat]);
    }

    public function destroyFlat(Building $building, BuildingFlat $flat): JsonResponse
    {
        $this->authorize('buildings.delete');
        $flat->delete();
        return $this->success('Flat deleted');
    }

    public function fireSystems(Building $building): JsonResponse
    {
        $this->authorize('buildings.view');
        $systems = $building->fireSystems()->get();
        return $this->success('Fire systems retrieved', ['fire_systems' => $systems]);
    }

    public function storeFireSystem(Request $request, Building $building): JsonResponse
    {
        $this->authorize('buildings.update');

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
        $this->authorize('buildings.update');

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
        $this->authorize('buildings.delete');
        $fireSystem->delete();
        return $this->success('Fire system deleted');
    }

    public function contacts(Building $building): JsonResponse
    {
        $this->authorize('buildings.view');
        $contacts = $building->contacts()->get();
        return $this->success('Contacts retrieved', ['contacts' => $contacts]);
    }

    public function storeContact(Request $request, Building $building): JsonResponse
    {
        $this->authorize('buildings.update');

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
        $this->authorize('buildings.update');

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
        $this->authorize('buildings.delete');
        $contact->delete();
        return $this->success('Contact deleted');
    }

    public function amcs(Building $building): JsonResponse
    {
        $this->authorize('buildings.view');
        $amcs = $building->amcs()->get();
        return $this->success('AMCs retrieved', ['amcs' => $amcs]);
    }

    public function storeAmc(Request $request, Building $building): JsonResponse
    {
        $this->authorize('buildings.update');

        $validated = $request->validate([
            'vendor_name' => ['nullable', 'string', 'max:255'],
            'contract_number' => ['nullable', 'string', 'max:255'],
            'contract_type' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'frequency' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
            'scope' => ['nullable', 'string'],
            'last_service_date' => ['nullable', 'date'],
            'next_service_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $amc = $building->amcs()->create($validated);
        return $this->success('AMC created', ['amc' => $amc], [], 201);
    }

    public function updateAmc(Request $request, Building $building, BuildingAmc $amc): JsonResponse
    {
        $this->authorize('buildings.update');

        $validated = $request->validate([
            'vendor_name' => ['sometimes', 'string', 'max:255'],
            'contract_number' => ['sometimes', 'string', 'max:255'],
            'contract_type' => ['sometimes', 'string', 'max:255'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'frequency' => ['sometimes', 'string', 'max:50'],
            'status' => ['sometimes', 'string', 'max:50'],
            'scope' => ['sometimes', 'string'],
            'last_service_date' => ['sometimes', 'date'],
            'next_service_date' => ['sometimes', 'date'],
            'notes' => ['sometimes', 'string'],
        ]);

        $amc->update($validated);
        return $this->success('AMC updated', ['amc' => $amc]);
    }

    public function destroyAmc(Building $building, BuildingAmc $amc): JsonResponse
    {
        $this->authorize('buildings.delete');
        $amc->delete();
        return $this->success('AMC deleted');
    }

    public function statuses(): JsonResponse
    {
        $this->authorize('buildings.view');
        $statuses = BuildingStatus::all();
        return $this->success('Statuses retrieved', ['statuses' => $statuses]);
    }

    public function assignStatus(Request $request, Building $building): JsonResponse
    {
        $this->authorize('buildings.update');

        $validated = $request->validate([
            'building_status_id' => ['required', 'exists:building_statuses,id'],
        ]);

        $building->statuses()->syncWithoutDetaching([$validated['building_status_id']]);
        return $this->success('Status assigned', ['statuses' => $building->statuses]);
    }

    public function removeStatus(Building $building, BuildingStatus $buildingStatus): JsonResponse
    {
        $this->authorize('buildings.update');
        $building->statuses()->detach($buildingStatus->id);
        return $this->success('Status removed');
    }

    public function dashboardStats(): JsonResponse
    {
        $this->authorize('buildings.view');

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

    public function siteVisits(Building $building): JsonResponse
    {
        $this->authorize('buildings.view');
        $visits = $building->siteVisits()->with('user')->latest('visit_date')->get();
        return $this->success('Site visits retrieved', ['site_visits' => $visits]);
    }

    public function followUps(Building $building): JsonResponse
    {
        $this->authorize('buildings.view');
        $followUps = $building->followUps()->with('user')->latest('reminder_date')->get();
        return $this->success('Follow-ups retrieved', ['follow_ups' => $followUps]);
    }

    public function opportunities(Building $building): JsonResponse
    {
        $this->authorize('buildings.view');
        $opportunities = $building->opportunities()->latest()->get();
        return $this->success('Opportunities retrieved', ['opportunities' => $opportunities]);
    }

    public function invoices(Building $building): JsonResponse
    {
        $this->authorize('buildings.view');

        $invoices = collect();

        if ($building->site_id) {
            $salesOrderIds = \App\Models\SalesOrder::where('site_id', $building->site_id)->pluck('id');
            if ($salesOrderIds->isNotEmpty()) {
                $invoices = \App\Models\Invoice::whereIn('sales_order_id', $salesOrderIds)
                    ->with('customer')
                    ->latest()
                    ->get();
            }
        }

        return $this->success('Invoices retrieved', ['invoices' => $invoices]);
    }
}
