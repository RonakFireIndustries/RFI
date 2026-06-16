<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Http\Requests\StorePayrollRequest;
use App\Http\Requests\UpdatePayrollRequest;
use App\Http\Resources\PayrollResource;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index()
    {
        $payrolls = Payroll::with('employee', 'branch')->latest()->get();
        return response()->json([
            'success' => true,
            'message' => 'Payrolls retrieved',
            'data' => PayrollResource::collection($payrolls)
        ], 200);
    }

    public function show(Payroll $payroll)
    {
        $payroll->load('employee', 'branch');
        return response()->json([
            'success' => true,
            'message' => 'Payroll retrieved',
            'data' => new PayrollResource($payroll)
        ], 200);
    }

    public function store(StorePayrollRequest $request)
    {
        $data = $request->validated();
        $data['branch_id'] = auth()->user()->branch_id;
        $payroll = Payroll::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Payroll created',
            'data' => new PayrollResource($payroll)
        ], 201);
    }

    public function update(UpdatePayrollRequest $request, Payroll $payroll)
    {
        $data = $request->validated();
        $payroll->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Payroll updated',
            'data' => new PayrollResource($payroll)
        ], 200);
    }

    public function destroy(Payroll $payroll)
    {
        $payroll->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payroll deleted',
            'data' => null
        ], 200);
    }

    public function process(Request $request)
    {
        $request->validate([
            'month' => 'required|numeric|between:1,12',
            'year' => 'required|numeric|digits:4',
        ]);

        $payrolls = Payroll::where('month', $request->month)
            ->where('year', $request->year)
            ->where('status', 'Pending')
            ->get();

        foreach ($payrolls as $payroll) {
            $payroll->update([
                'status' => 'Processed',
                'processed_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => count($payrolls) . ' payrolls processed',
            'data' => ['count' => count($payrolls)]
        ], 200);
    }
}
