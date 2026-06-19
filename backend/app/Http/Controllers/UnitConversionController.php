<?php

namespace App\Http\Controllers;

use App\Models\UnitConversion;
use App\Http\Resources\UnitConversionResource;
use App\Http\Requests\StoreUnitConversionRequest;
use App\Http\Requests\UpdateUnitConversionRequest;

class UnitConversionController extends Controller
{
    public function index()
    {
        return UnitConversionResource::collection(
            UnitConversion::with(['fromUnit', 'toUnit'])->get()
        );
    }

    public function store(StoreUnitConversionRequest $request)
    {
        $conversion = UnitConversion::create($request->validated());
        return new UnitConversionResource($conversion->load(['fromUnit', 'toUnit']));
    }

    public function show(UnitConversion $unitConversion)
    {
        return new UnitConversionResource($unitConversion->load(['fromUnit', 'toUnit']));
    }

    public function update(UpdateUnitConversionRequest $request, UnitConversion $unitConversion)
    {
        $unitConversion->update($request->validated());
        return new UnitConversionResource($unitConversion->load(['fromUnit', 'toUnit']));
    }

    public function destroy(UnitConversion $unitConversion)
    {
        $unitConversion->delete();
        return response()->noContent();
    }

    public function convert($from, $to, $quantity)
    {
        $service = app(\App\Services\UnitConversionService::class);
        $result = $service->convert($quantity, $from, $to);
        $path = $service->getConversionPath($from, $to);

        if ($result === null) {
            return response()->json(['message' => 'No conversion path found'], 404);
        }

        return response()->json([
            'from_unit_id' => (int) $from,
            'to_unit_id' => (int) $to,
            'quantity' => (float) $quantity,
            'result' => $result,
            'path' => $path,
        ]);
    }
}
