<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        return Branch::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);
        return Branch::create($validated);
    }

    public function show(Branch $branch)
    {
        return $branch;
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);
        $branch->update($validated);
        return $branch;
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();
        return response()->noContent();
    }
}
