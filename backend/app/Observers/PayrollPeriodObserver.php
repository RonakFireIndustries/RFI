<?php

namespace App\Observers;

use App\Models\PayrollPeriod;
use App\Services\NotificationService;

/**
 * Payroll PERIODS are observed rather than individual payroll rows.
 *
 * A payrolls row exists per employee, so reacting to those would emit one
 * notification per person on every run. A period turning Processing is the
 * single moment the payroll team actually has something to do.
 */
class PayrollPeriodObserver extends NotificationObserver
{
    public function updated(PayrollPeriod $period): void
    {
        if (! $this->changed($period, 'status')) {
            return;
        }

        if ($period->status !== 'Processing') {
            return;
        }

        NotificationService::notify('payroll.period_ready', [
            'id' => $period->id,
            'period' => $this->periodLabel($period),
        ], $this->currentActor());
    }

    private function periodLabel(PayrollPeriod $period): string
    {
        if (! empty($period->name)) {
            return (string) $period->name;
        }

        if (! empty($period->month) && ! empty($period->year)) {
            try {
                return \Illuminate\Support\Carbon::createFromDate($period->year, $period->month, 1)->format('M Y');
            } catch (\Throwable $e) {
                return "{$period->month}/{$period->year}";
            }
        }

        $start = $period->period_start ?? $period->start_date ?? null;

        if ($start !== null) {
            return $this->formatDate($start);
        }

        return 'the current period';
    }
}
