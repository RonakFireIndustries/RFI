<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use App\Http\Requests\StoreDesignationRequest;
use App\Http\Requests\UpdateDesignationRequest;
use App\Http\Resources\DesignationResource;

class DesignationController extends Controller
{
    public function index()
    {
        $designations = Designation::with('branch')->get();
        return response()->json([
            'success' => true,
            'message' => 'Designations retrieved',
            'data' => DesignationResource::collection($designations)
        ], 200);
    }

    public function show(Designation $designation)
    {
        $designation->load('branch', 'employees');
        return response()->json([
            'success' => true,
            'message' => 'Designation retrieved',
            'data' => new DesignationResource($designation)
        ], 200);
    }

    public function store(StoreDesignationRequest $request)
    {
        $data = $request->validated();
        $designation = Designation::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Designation created',
            'data' => new DesignationResource($designation)
        ], 201);
    }

    public function update(UpdateDesignationRequest $request, Designation $designation)
    {
        $data = $request->validated();
        $designation->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Designation updated',
            'data' => new DesignationResource($designation)
        ], 200);
    }

    public function destroy(Designation $designation)
    {
        $designation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Designation deleted',
            'data' => null
        ], 200);
    }
}
