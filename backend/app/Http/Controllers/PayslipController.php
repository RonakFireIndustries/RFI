<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\Payslip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PayslipController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view_payroll');
        $query = Payslip::with(['payroll.employee', 'payroll.payrollPeriod']);
        
        if ($request->has('employee_id')) {
            $query->whereHas('payroll', function ($q) use ($request) {
                $q->where('employee_id', $request->employee_id);
            });
        }

        return response()->json($query->orderBy('created_at', 'desc')->get());
    }

    public function show(Payslip $payslip)
    {
        $this->authorize('view_payroll');
        return response()->json($payslip->load(['payroll.employee.department', 'payroll.employee.designation', 'payroll.payrollPeriod']));
    }

    public function generate(Request $request, Payroll $payroll)
    {
        $this->authorize('manage_payroll');
        if ($payroll->status === 'Draft' || $payroll->status === 'Processing') {
            return response()->json(['message' => 'Cannot generate payslip for payrolls that are not approved.'], 403);
        }

        // Check if payslip already exists
        $payslip = $payroll->payslip;
        
        if (!$payslip) {
            $payslip = Payslip::create([
                'payroll_id' => $payroll->id,
                'generated_at' => now(),
                // 'file_path' => '/storage/payslips/something.pdf' // if using PDF generation
            ]);
            Log::info("Payslip generated", ['payroll_id' => $payroll->id, 'payslip_id' => $payslip->id, 'user_id' => auth()->id() ?? 'system']);
        }

        return response()->json(['message' => 'Payslip generated successfully', 'payslip' => $payslip]);
    }

    public function download(Payslip $payslip)
    {
        $this->authorize('view_payroll');
        Log::info("Payslip downloaded/viewed", ['payslip_id' => $payslip->id, 'user_id' => auth()->id() ?? 'system']);
        
        // If a physical file exists:
        if ($payslip->file_path && Storage::exists($payslip->file_path)) {
            return Storage::download($payslip->file_path);
        }

        // Alternatively, return a JSON payload specifically formatted for the frontend to render and print:
        return response()->json([
            'message' => 'No PDF file generated yet. Returning raw payslip data for frontend rendering.',
            'data' => $payslip->load([
                'payroll.employee.department', 
                'payroll.employee.designation', 
                'payroll.payrollPeriod'
            ])
        ]);
    }
}
