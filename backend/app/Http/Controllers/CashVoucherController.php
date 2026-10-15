<?php

namespace App\Http\Controllers;

use App\Models\CashVoucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Petty-cash vouchers.
 *
 * Lifecycle: Draft -> Approved -> Paid, with Rejected as an alternative end.
 * Only Draft vouchers can be edited, deleted, approved or rejected.
 */
class CashVoucherController extends Controller
{
    /** Columns/relations shared by every response payload. */
    protected const EAGER = [
        'employee:id,full_name,emp_id',
        'creator:id,name',
        'approver:id,name',
    ];

    public function index(Request $request)
    {
        $this->authorize('cash-vouchers.view');

        $query = CashVoucher::with(self::EAGER);

        // Raising a voucher is a baseline (self-service) permission, so the
        // register is scoped: only approvers/rejecters see everybody's claims,
        // everyone else sees just the vouchers they raised.
        if (! $this->canViewAll($request)) {
            $query->where('created_by', $request->user()->id);
        }

        if ($status = $request->input('status')) {
            $statuses = is_array($status) ? $status : explode(',', (string) $status);
            $statuses = array_values(array_filter(array_map('trim', $statuses)));
            if (count($statuses) > 1) {
                $query->whereIn('status', $statuses);
            } elseif ($statuses) {
                $query->where('status', $statuses[0]);
            }
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('voucher_no', 'like', "%{$search}%")
                    ->orWhere('paid_to', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%")
                    ->orWhere('reference_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('from')) {
            $query->whereDate('voucher_date', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('voucher_date', '<=', $request->input('to'));
        }

        return response()->json(
            $query->orderByDesc('voucher_date')->orderByDesc('id')->get()
        );
    }

    public function store(Request $request)
    {
        $this->authorize('cash-vouchers.create');

        $validated = $this->validated($request);

        $voucher = DB::transaction(function () use ($request, $validated) {
            $validated['voucher_no'] = $this->nextVoucherNo($validated['voucher_date'] ?? null);
            $validated['created_by'] = $request->user()->id;
            // The reimbursement always belongs to the employee raising the
            // voucher, so it is taken from the session rather than the form.
            $validated['employee_id'] = $request->user()->employee?->id;
            $validated['status'] = 'Draft';

            if ($request->hasFile('attachment')) {
                $validated['attachment_path'] = $request->file('attachment')->store('cash-vouchers', 'public');
            }
            unset($validated['attachment']);

            return CashVoucher::create($validated);
        });

        return response()->json($voucher->load(self::EAGER), 201);
    }

    public function show(Request $request, CashVoucher $cashVoucher)
    {
        $this->authorize('cash-vouchers.view');

        if (! $this->canViewAll($request) && ! $this->owns($request, $cashVoucher)) {
            abort(403, 'You can only view vouchers you raised.');
        }

        return response()->json($cashVoucher->load(self::EAGER));
    }

    public function update(Request $request, CashVoucher $cashVoucher)
    {
        if (! $this->canManage($request, $cashVoucher, 'update')) {
            abort(403, 'You can only edit vouchers you raised.');
        }

        if ($cashVoucher->status !== 'Draft') {
            return response()->json(['message' => 'Only draft vouchers can be edited.'], 403);
        }

        $validated = $this->validated($request);

        if ($request->hasFile('attachment')) {
            $validated['attachment_path'] = $request->file('attachment')->store('cash-vouchers', 'public');
        }
        unset($validated['attachment']);

        // The number embeds the year; only renumber if the year actually moved.
        if (!empty($validated['voucher_date'])) {
            $newYear = date('Y', strtotime($validated['voucher_date']));
            $currentYear = $cashVoucher->voucher_date?->format('Y');
            if ($newYear !== $currentYear) {
                $validated['voucher_no'] = $this->nextVoucherNo($validated['voucher_date']);
            }
        }

        $cashVoucher->update($validated);

        return response()->json($cashVoucher->load(self::EAGER));
    }

    public function destroy(Request $request, CashVoucher $cashVoucher)
    {
        if (! $this->canManage($request, $cashVoucher, 'delete')) {
            abort(403, 'You can only remove vouchers you raised.');
        }

        if ($cashVoucher->status !== 'Draft') {
            return response()->json(['message' => 'Only draft vouchers can be removed.'], 403);
        }

        $cashVoucher->delete();

        return response()->noContent();
    }

    public function approve(Request $request, CashVoucher $cashVoucher)
    {
        $this->authorize('cash-vouchers.approve');

        if ($cashVoucher->status !== 'Draft') {
            return response()->json(['message' => 'Only draft vouchers can be approved.'], 403);
        }

        $cashVoucher->update([
            'status' => 'Approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return response()->json($cashVoucher->load(self::EAGER));
    }

    public function reject(Request $request, CashVoucher $cashVoucher)
    {
        $this->authorize('cash-vouchers.reject');

        $request->validate(['remarks' => 'required|string|max:1000']);

        if (!in_array($cashVoucher->status, ['Draft', 'Approved'], true)) {
            return response()->json(['message' => 'This voucher can no longer be rejected.'], 403);
        }

        $cashVoucher->update([
            'status' => 'Rejected',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'remarks' => $request->input('remarks'),
        ]);

        return response()->json($cashVoucher->load(self::EAGER));
    }

    public function markPaid(Request $request, CashVoucher $cashVoucher)
    {
        $this->authorize('cash-vouchers.approve');

        if ($cashVoucher->status !== 'Approved') {
            return response()->json(['message' => 'Only approved vouchers can be marked paid.'], 403);
        }

        $cashVoucher->update([
            'status' => 'Paid',
            'paid_at' => now(),
        ]);

        return response()->json($cashVoucher->load(self::EAGER));
    }

    /**
     * @return array<string, mixed> validated voucher attributes
     */
    protected function validated(Request $request): array
    {
        // site / supplier / employee are intentionally not accepted from the
        // client any more: a voucher is a reimbursement claim for the employee
        // raising it (derived in store()).
        return $request->validate([
            'voucher_date' => 'required|date',
            'paid_to' => 'required|string|max:255',
            'purpose' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_mode' => 'nullable|string|max:50',
            'reference_no' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'attachment' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,webp,pdf',
        ]);
    }

    /**
     * Whether the signed-in user may see vouchers raised by other employees.
     * Approvers/rejecters own the register; everyone else only sees their own.
     */
    protected function canViewAll(Request $request): bool
    {
        $user = $request->user();

        return $user->can('cash-vouchers.approve') || $user->can('cash-vouchers.reject');
    }

    /** Whether the signed-in user raised this voucher. */
    protected function owns(Request $request, CashVoucher $voucher): bool
    {
        return $voucher->created_by !== null
            && (int) $voucher->created_by === (int) $request->user()->id;
    }

    /**
     * Whether the user may change this voucher: the employee who raised it can
     * always fix or withdraw their own draft, while finance users may manage
     * any voucher through the matching permission.
     */
    protected function canManage(Request $request, CashVoucher $voucher, string $action): bool
    {
        return $this->owns($request, $voucher)
            || $request->user()->can("cash-vouchers.{$action}");
    }

    /**
     * Sequential per-year number, e.g. CV-2026-0001.
     */
    protected function nextVoucherNo(?string $date): string
    {
        $year = $date ? date('Y', strtotime($date)) : date('Y');
        $prefix = "CV-{$year}-";

        $last = CashVoucher::withTrashed()
            ->where('voucher_no', 'like', $prefix . '%')
            ->orderByDesc('voucher_no')
            ->value('voucher_no');

        $sequence = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        do {
            $candidate = $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $exists = CashVoucher::withTrashed()->where('voucher_no', $candidate)->exists();
            $sequence++;
        } while ($exists);

        return $candidate;
    }
}
