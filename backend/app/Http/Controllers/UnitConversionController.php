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
        $this->authorize('unit-conversions.view');
        return UnitConversionResource::collection(
            UnitConversion::with(['fromUnit', 'toUnit'])->get()
        );
    }

    public function store(StoreUnitConversionRequest $request)
    {
        $this->authorize('unit-conversions.create');
        $conversion = UnitConversion::create($request->validated());
        return new UnitConversionResource($conversion->load(['fromUnit', 'toUnit']));
    }

    public function show(UnitConversion $unitConversion)
    {
        $this->authorize('unit-conversions.view');
        return new UnitConversionResource($unitConversion->load(['fromUnit', 'toUnit']));
    }

    public function update(UpdateUnitConversionRequest $request, UnitConversion $unitConversion)
    {
        $this->authorize('unit-conversions.update');
        $unitConversion->update($request->validated());
        return new UnitConversionResource($unitConversion->load(['fromUnit', 'toUnit']));
    }

    public function destroy(UnitConversion $unitConversion)
    {
        $this->authorize('unit-conversions.delete');
        $unitConversion->delete();
        return response()->noContent();
    }

    /**
     * Pure calculator behind the forms' live "≈ X product unit" hints.
     *
     * Deliberately open to any authenticated user: the same factor is applied
     * to their save anyway, so hiding it behind unit-conversions.view would
     * only make the hint silently disappear for ordinary data-entry roles.
     * Creating and editing conversion rates still need the unit-conversions.*
     * grants above.
     */
    public function convert($from, $to, $quantity)
    {
        $service = app(\App\Services\UnitConversionService::class);
        $result = $service->convert((float) $quantity, (int) $from, (int) $to);
        $path = $service->getConversionPath((int) $from, (int) $to);

        if ($result === null) {
            return response()->json(['message' => 'No conversion path found'], 404);
        }

        return response()->json([
            'from_unit_id' => (int) $from,
            'to_unit_id' => (int) $to,
            'from_unit' => \App\Models\Unit::find($from)?->name,
            'to_unit' => \App\Models\Unit::find($to)?->name,
            'quantity' => (float) $quantity,
            'result' => $result,
            'path' => $path,
        ]);
    }
}
