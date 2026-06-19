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
        return UnitResource::collection(Unit::all());
    }

    public function store(StoreUnitRequest $request)
    {
        $unit = Unit::create($request->validated());
        return new UnitResource($unit);
    }

    public function show(Unit $unit)
    {
        return new UnitResource($unit);
    }

    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $unit->update($request->validated());
        return new UnitResource($unit);
    }

    public function destroy(Unit $unit)
    {
        if ($unit->fromConversions()->exists() || $unit->toConversions()->exists()) {
            return response()->json(['message' => 'Cannot delete unit with existing conversions'], 400);
        }
        $unit->delete();
        return response()->noContent();
    }
}
