<?php

/*
|--------------------------------------------------------------------------
| Module report overrides
|--------------------------------------------------------------------------
|
| Every module report in the application is declared as data in
| app/Support/Reports (see Registry.php and the Modules directory). This file
| is merged over the top of that registry, so an installation can add a
| report, relabel one, or retire one without editing PHP.
|
| The shape is the registry shape:
|
|   'finance' => [
|       'label'      => 'Finance',
|       'permission' => 'reports.view',
|       'reports'    => [
|           'my-report' => [
|               'label'      => 'My Report',
|               'base'       => 'vouchers',
|               'permission' => 'reports.view',
|               'columns'    => [
|                   ['key' => 'no', 'label' => 'Voucher No', 'expression' => 'vouchers.voucher_number', 'format' => 'text'],
|               ],
|               'filters'    => [
|                   ['key' => 'from', 'label' => 'From', 'type' => 'date', 'column' => 'vouchers.date', 'operator' => '>='],
|               ],
|           ],
|       ],
|   ],
|
| The terse builders in App\Support\Reports\Spec are the recommended way to
| write one of these:
|
|   use App\Support\Reports\Spec;
|
|   'employees' => [
|       'reports' => [
|           'seniority' => [
|               'label'   => 'Seniority Report',
|               'base'    => 'employees',
|               'columns' => [
|                   Spec::col('name', 'Employee', 'employees.full_name'),
|                   Spec::num('years', 'Years', '...'),
|               ],
|           ],
|       ],
|   ],
|
| Note that a report's `permission` decides who can see it in the catalogue,
| so an override that drops the permission makes the report visible to
| everyone with access to the pack.
|
*/

return [
    //
];
