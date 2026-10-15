<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| Requires a single cron entry on the server:
|   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
|
*/

// Close attendance records where an employee checked in but forgot to check out,
// using their shift end time (after the configured grace period).
Schedule::command('attendance:auto-close-forgotten')
    ->everyFifteenMinutes()
    ->withoutOverlapping();

// Proactive notifications: due and overdue follow-ups, overdue invoices, stock
// below reorder level, expiring AMCs and documents, low leave balances and
// missing attendance. Alerts are de-duplicated by the catalogue's dedupe flag,
// so re-running does not stack copies on top of an unread one.
Schedule::command('notifications:scan')
    ->dailyAt(config('notifications.schedule.scan_at', '09:00'))
    ->withoutOverlapping();
