<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeeSite;
use App\Http\Requests\StoreEmployeeSiteRequest;

class EmployeeSiteController extends Controller
{
    public function index()
    {
        $this->authorize('site.view');
        $items = EmployeeSite::with(['employee.user', 'site'])->get();
        return response()->json(['success' => true, 'message' => 'Success', 'data' => $items], 200);
    }

    public function store(StoreEmployeeSiteRequest $request)
    {
        $this->authorize('site.assign');
        $data = $request->validated();

        $es = EmployeeSite::create($data);
        return response()->json(['success' => true, 'message' => 'Assigned', 'data' => $es], 201);
    }

    public function destroy(EmployeeSite $employeeSite)
    {
        $this->authorize('site.assign');
        $employeeSite->delete();
        return response()->json(['success' => true, 'message' => 'Removed', 'data' => null], 200);
    }
}
