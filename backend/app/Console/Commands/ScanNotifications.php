<?php

namespace App\Console\Commands;

use App\Models\BuildingAmc;
use App\Models\Document;
use App\Models\Employee;
use App\Models\FollowUp;
use App\Models\Invoice;
use App\Models\LeaveBalance;
use App\Models\Product;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Produces the proactive notifications - the ones nobody triggers by doing
 * something, but that quietly go wrong if nobody is told.
 *
 * Every check is de-duplicated by the catalogue's `dedupe` flag, so a
 * condition that persists day after day does not stack copies on top of an
 * unread alert, but does re-alert once the previous one is acknowledged.
 *
 * Scheduled from routes/console.php.
 */
class ScanNotifications extends Command
{
    protected $signature = 'notifications:scan';

    protected $description = 'Emit proactive notifications: due and overdue follow-ups, overdue invoices, low stock, expiring AMCs and documents, low leave balances, missing attendance.';

    public function handle(): int
    {
        $expiryDays = (int) config('notifications.schedule.expiry_days', 30);

        $checks = [
            'due-today follow-ups' => fn () => $this->followUps(dueToday: true),
            'overdue follow-ups' => fn () => $this->followUps(dueToday: false),
            'overdue invoices' => fn () => $this->overdueInvoices(),
            'low stock' => fn () => $this->lowStock(),
            'expiring AMCs' => fn () => $this->expiringAmcs($expiryDays),
            'expiring documents' => fn () => $this->expiringDocuments($expiryDays),
            'low leave balances' => fn () => $this->lowLeaveBalances(),
            'missing attendance' => fn () => $this->missingAttendance(),
        ];

        foreach ($checks as $label => $check) {
            try {
                $this->info(sprintf('%-24s %d sent', $label, $check()));
            } catch (\Throwable $e) {
                // One broken check must not stop the others.
                Log::error("notifications:scan failed on [{$label}]: " . $e->getMessage());
                $this->warn(sprintf('%-24s failed - %s', $label, $e->getMessage()));
            }
        }

        return self::SUCCESS;
    }

    private function followUps(bool $dueToday): int
    {
        $today = Carbon::today()->toDateString();
        $sent = 0;

        $query = FollowUp::query()
            ->where('status', 'Pending')
            ->with('user');

        $dueToday
            ? $query->whereDate('reminder_date', $today)
            : $query->whereDate('reminder_date', '<', $today);

        foreach ($query->get() as $followUp) {
            $sent += NotificationService::notify(
                $dueToday ? 'followup.due_today' : 'followup.overdue',
                [
                    'id' => $followUp->id,
                    'type' => $followUp->type,
                    'date' => Carbon::parse($followUp->reminder_date)->format('d M Y'),
                    'employee' => $followUp->user?->name,
                ],
                null,
                [$followUp->user_id],
            )->count();
        }

        return $sent;
    }

    private function overdueInvoices(): int
    {
        $sent = 0;

        // Status is written inconsistently across the codebase, so overdue is
        // decided by the due date and only explicitly-settled invoices are
        // skipped.
        $settled = ['paid', 'cancelled', 'void'];

        $invoices = Invoice::query()
            ->whereDate('due_date', '<', Carbon::today())
            ->with('customer')
            ->get()
            ->reject(fn ($invoice) => in_array(strtolower((string) $invoice->status), $settled, true));

        foreach ($invoices as $invoice) {
            $sent += NotificationService::notify('invoice.overdue', [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer' => $invoice->customer?->name,
                'amount' => number_format((float) ($invoice->total_amount ?? $invoice->grand_total ?? 0), 2),
                'due_date' => Carbon::parse($invoice->due_date)->format('d M Y'),
            ])->count();
        }

        return $sent;
    }

    private function lowStock(): int
    {
        $sent = 0;

        $products = Product::query()
            ->whereNotNull('reorder_level')
            ->where('reorder_level', '>', 0)
            ->with('stock')
            ->get()
            // One row per location, so the threshold compares against the total.
            ->filter(fn ($product) => $product->stock->sum('quantity') <= (float) $product->reorder_level);

        foreach ($products as $product) {
            $sent += NotificationService::notify('stock.low', [
                'id' => $product->id,
                'product' => $product->name,
                'quantity' => rtrim(rtrim(number_format((float) $product->stock->sum('quantity'), 2), '0'), '.'),
                'unit' => $product->unit?->name ?? '',
                'reorder_level' => rtrim(rtrim(number_format((float) $product->reorder_level, 2), '0'), '.'),
            ])->count();
        }

        return $sent;
    }

    private function expiringAmcs(int $days): int
    {
        $sent = 0;
        $limit = Carbon::today()->addDays($days);

        $amcs = BuildingAmc::query()
            ->whereNotNull('end_date')
            ->whereDate('end_date', '>=', Carbon::today())
            ->whereDate('end_date', '<=', $limit)
            ->with('building')
            ->get();

        foreach ($amcs as $amc) {
            $sent += NotificationService::notify('amc.expiring', [
                'id' => $amc->building_id ?? $amc->id,
                'building' => $amc->building?->name ?? 'a building',
                'end_date' => Carbon::parse($amc->end_date)->format('d M Y'),
            ])->count();
        }

        return $sent;
    }

    private function expiringDocuments(int $days): int
    {
        $sent = 0;
        $limit = Carbon::today()->addDays($days);

        $documents = Document::query()
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', Carbon::today())
            ->whereDate('expiry_date', '<=', $limit)
            ->get();

        foreach ($documents as $document) {
            $employee = $document->employee ?? null;
            $recipient = $document->uploaded_by ?? null;

            $sent += NotificationService::notify('document.expiring', [
                'id' => $document->id,
                'document' => $document->name ?? $document->title ?? 'a document',
                'employee' => $employee?->full_name ?? 'an employee',
                'expiry_date' => Carbon::parse($document->expiry_date)->format('d M Y'),
            ], null, array_values(array_filter([$recipient])))->count();
        }

        return $sent;
    }

    private function lowLeaveBalances(): int
    {
        $sent = 0;

        $balances = LeaveBalance::query()
            ->where('year', Carbon::today()->year)
            ->where('remaining', '<=', 1)
            ->where('remaining', '>', 0)
            ->with(['employee.user', 'leaveType'])
            ->get();

        foreach ($balances as $balance) {
            $userId = $balance->employee?->user_id;

            if ($userId === null) {
                continue;
            }

            $sent += NotificationService::notify('leave.balance_low', [
                'id' => $balance->id,
                'remaining' => rtrim(rtrim(number_format((float) $balance->remaining, 2), '0'), '.'),
                'leave_type' => $balance->leaveType?->name ?? 'leave',
            ], null, [(int) $userId])->count();
        }

        return $sent;
    }

    private function missingAttendance(): int
    {
        // Weekends and holidays are not working days, so absence means nothing.
        $today = Carbon::today();

        if ($today->isSunday()) {
            return 0;
        }

        try {
            if (\App\Models\Holiday::query()->whereDate('date', $today)->exists()) {
                return 0;
            }
        } catch (\Throwable $e) {
            // Holiday table optional; carry on without it.
        }

        // Anyone whose approved leave covers today is not absent - approving a
        // leave also writes an "On Leave" attendance row, but be explicit.
        $onLeave = \App\Models\Leave::query()
            ->where('status', 'Approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->pluck('employee_id')
            ->all();

        $sent = 0;

        $employees = Employee::query()
            ->where('status', 'active')
            ->whereNotNull('user_id')
            ->with('user')
            ->get()
            ->reject(fn ($employee) => in_array($employee->id, $onLeave, true));

        foreach ($employees as $employee) {
            $alreadyMarked = \App\Models\Attendance::query()
                ->where('employee_id', $employee->id)
                ->whereDate('date', $today)
                ->exists();

            if ($alreadyMarked) {
                continue;
            }

            $sent += NotificationService::notify('attendance.missing', [
                'id' => $employee->id,
                'employee' => $employee->full_name ?? $employee->user?->name,
                'date' => $today->format('d M Y'),
            ], null, [(int) $employee->user_id])->count();
        }

        return $sent;
    }
}
