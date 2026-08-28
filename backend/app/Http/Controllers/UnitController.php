<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Http\Resources\UnitResource;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;

class UnitController extends Controller
{
    public function index()
    {
        $this->authorize('units.view');
        return UnitResource::collection(Unit::all());
    }

    public function store(StoreUnitRequest $request)
    {
        $this->authorize('units.create');
        $unit = Unit::create($request->validated());
        return new UnitResource($unit);
    }

    public function show(Unit $unit)
    {
        $this->authorize('units.view');
        return new UnitResource($unit);
    }

    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $this->authorize('units.update');
        $unit->update($request->validated());
        return new UnitResource($unit);
    }

    public function destroy(Unit $unit)
    {
        $this->authorize('units.delete');
        if ($unit->fromConversions()->exists() || $unit->toConversions()->exists()) {
            return response()->json(['message' => 'Cannot delete unit with existing conversions'], 400);
        }
        $unit->delete();
        return response()->noContent();
    }
}
