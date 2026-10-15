<?php

namespace App\Observers;

use App\Models\Payslip;
use App\Services\NotificationService;

/**
 * A payslip has no status - its creation IS the event, and the only person
 * who needs telling is the employee it belongs to.
 */
class PayslipObserver extends NotificationObserver
{
    public function created(Payslip $payslip): void
    {
        $payroll = $payslip->getAttribute('payroll');
        $employee = $payroll?->getAttribute('employee');
        $userId = $employee?->getAttribute('user_id');

        if ($userId === null) {
            return;
        }

        NotificationService::notify(
            'payslip.published',
            [
                'id' => $payslip->id,
                'period' => $this->periodLabel($payroll),
            ],
            null,
            [(int) $userId],
            '/dashboard/my-payroll',
        );
    }

    /**
     * Payrolls carry month/year on most installs and month_year on some, so
     * read whichever is present.
     */
    private function periodLabel(?object $payroll): string
    {
        if ($payroll === null) {
            return 'the latest period';
        }

        if (! empty($payroll->month_year)) {
            try {
                return \Illuminate\Support\Carbon::parse($payroll->month_year . '-01')->format('M Y');
            } catch (\Throwable $e) {
                return (string) $payroll->month_year;
            }
        }

        if (! empty($payroll->month) && ! empty($payroll->year)) {
            try {
                return \Illuminate\Support\Carbon::createFromDate($payroll->year, $payroll->month, 1)->format('M Y');
            } catch (\Throwable $e) {
                return "{$payroll->month}/{$payroll->year}";
            }
        }

        return 'the latest period';
    }
}
