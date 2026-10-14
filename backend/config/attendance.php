<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Auto check-out for forgotten check-outs
    |--------------------------------------------------------------------------
    |
    | When an employee checks in but never checks out, the attendance record
    | is closed automatically using the shift's end time. This only happens
    | once "grace_hours" have passed after the shift's scheduled end, so an
    | employee still working is never closed out early. A real check-out by
    | the employee always overrides an auto-closed record.
    |
    | lookback_days limits which records the scheduled command will touch, so
    | a long outage cannot silently rewrite months of history. Records with a
    | missing check-out older than this window are left untouched.
    |
    */

    'auto_checkout_grace_hours' => (int) env('ATTENDANCE_AUTO_CHECKOUT_GRACE_HOURS', 2),

    'auto_checkout_lookback_days' => (int) env('ATTENDANCE_AUTO_CHECKOUT_LOOKBACK_DAYS', 7),

];
