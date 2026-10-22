<?php

namespace App\Support\Reports\Modules;

use App\Support\Reports\Spec;

/**
 * Human Resources reports.
 *
 * Everything here is about people: who is on the roster, where they work,
 * how they are spending their days and what they have taken as leave.
 */
class HumanResources
{
    public static function modules(): array
    {
        return [
            'employees' => self::employees(),
            'attendance' => self::attendance(),
            'shifts' => self::shifts(),
            'daily-reports' => self::dailyReports(),
            'leaves' => self::leaves(),
            'leave-types' => self::leaveTypes(),
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function employees(): array
    {
        $department = Spec::join('departments', 'employees.department_id', 'departments.id');
        $designation = Spec::join('designations', 'employees.designation_id', 'designations.id');

        return [
            'label' => 'Employees',
            'category' => 'Human Resources',
            'permission' => 'employees.view',
            'reports' => [

                'directory' => [
                    'label' => 'Employee Directory',
                    'description' => 'The full roster with department, designation and contact details.',
                    'base' => 'employees',
                    'joins' => [$department, $designation, Spec::join('users', 'employees.user_id', 'users.id')],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::col('department', 'Department', 'departments.name'),
                        Spec::col('designation', 'Designation', 'designations.name'),
                        Spec::col('employment_type', 'Type', 'employees.employment_type'),
                        Spec::col('email', 'Email', 'users.email'),
                        Spec::col('contact_number', 'Contact', 'employees.contact_number'),
                        Spec::date('joining_date', 'Joined', 'employees.joining_date'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'Joined from', 'employees.joining_date', '>='),
                        Spec::dateFilter('to', 'Joined to', 'employees.joining_date', '<='),
                        Spec::sourceFilter('employment_type', 'Employment type', 'employees', 'employment_type'),
                        Spec::textFilter('q', 'Search', ['employees.full_name', 'employees.emp_id']),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Employees', 'count(*)'),
                        Spec::summary('departments', 'Departments', 'count(distinct employees.department_id)'),
                    ],
                    'order' => [Spec::order('name')],
                ],

                'headcount-by-department' => [
                    'label' => 'Headcount by Department',
                    'description' => 'How many people each department carries.',
                    'base' => 'employees',
                    'joins' => [$department],
                    'group_by' => ['departments.name'],
                    'columns' => [
                        Spec::col('department', 'Department', 'departments.name', ['primary' => true]),
                        Spec::num('headcount', 'Employees', 'count(distinct employees.id)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'Joined from', 'employees.joining_date', '>='),
                        Spec::dateFilter('to', 'Joined to', 'employees.joining_date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Employees', 'count(*)'),
                        Spec::summary('departments', 'Departments', 'count(distinct departments.id)'),
                    ],
                    'order' => [Spec::order('headcount', 'desc')],
                    'chart' => Spec::chart('bar', 'department', 'headcount'),
                ],

                'headcount-by-designation' => [
                    'label' => 'Headcount by Designation',
                    'description' => 'The shape of the team - who does what.',
                    'base' => 'employees',
                    'joins' => [$designation],
                    'group_by' => ['designations.name'],
                    'columns' => [
                        Spec::col('designation', 'Designation', 'designations.name', ['primary' => true]),
                        Spec::num('headcount', 'Employees', 'count(distinct employees.id)'),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Employees', 'count(*)'),
                        Spec::summary('designations', 'Designations', 'count(distinct designations.id)'),
                    ],
                    'order' => [Spec::order('headcount', 'desc')],
                    'chart' => Spec::chart('bar', 'designation', 'headcount'),
                ],

                'employment-type-mix' => [
                    'label' => 'Employment Type Mix',
                    'description' => 'Full-time, part-time, contract - the shape of the contract book.',
                    'base' => 'employees',
                    'group_by' => ['employees.employment_type'],
                    'columns' => [
                        Spec::col('employment_type', 'Employment type', 'employees.employment_type', ['primary' => true]),
                        Spec::num('headcount', 'Employees', 'count(*)'),
                    ],
                    'summary' => [Spec::summary('total', 'Employees', 'count(*)')],
                    'order' => [Spec::order('headcount', 'desc')],
                    'chart' => Spec::chart('pie', 'employment_type', 'headcount'),
                ],

                'recent-joiners' => [
                    'label' => 'Recent Joiners',
                    'description' => 'Everyone who joined in the chosen window.',
                    'base' => 'employees',
                    'joins' => [$department, $designation],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::col('department', 'Department', 'departments.name'),
                        Spec::col('designation', 'Designation', 'designations.name'),
                        Spec::col('employment_type', 'Type', 'employees.employment_type'),
                        Spec::date('joining_date', 'Joined', 'employees.joining_date'),
                        Spec::date('probation_end_date', 'Probation ends', 'employees.probation_end_date'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'Joined from', 'employees.joining_date', '>='),
                        Spec::dateFilter('to', 'Joined to', 'employees.joining_date', '<='),
                    ],
                    'summary' => [Spec::summary('total', 'Joiners', 'count(*)')],
                    'order' => [Spec::order('joining_date', 'desc')],
                ],

                'probation-tracker' => [
                    'label' => 'Probation Tracker',
                    'description' => 'Who is still inside their probation period, and how long is left.',
                    'base' => 'employees',
                    'joins' => [$department],
                    'constraints' => [
                        ['column' => 'employees.probation_end_date', 'operator' => '!=', 'value' => null],
                    ],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::col('department', 'Department', 'departments.name'),
                        Spec::date('joining_date', 'Joined', 'employees.joining_date'),
                        Spec::date('probation_end_date', 'Probation ends', 'employees.probation_end_date'),
                        Spec::num('days_left', 'Days left', 'datediff(employees.probation_end_date, curdate())'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['employees.full_name', 'employees.emp_id']),
                    ],
                    'summary' => [
                        Spec::summary('total', 'On probation', 'count(*)'),
                        Spec::summary('ending_soon', 'Ending in 30 days', "sum(case when datediff(employees.probation_end_date, curdate()) between 0 and 30 then 1 else 0 end)"),
                    ],
                    'order' => [Spec::order('probation_end_date')],
                ],

                'reporting-structure' => [
                    'label' => 'Reporting Structure',
                    'description' => 'Every employee and the manager they report to.',
                    'base' => 'employees',
                    'joins' => [
                        $department,
                        $designation,
                        Spec::join('employees', 'employees.reporting_manager_id', 'managers.id', 'left', 'managers'),
                    ],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::col('designation', 'Designation', 'designations.name'),
                        Spec::col('department', 'Department', 'departments.name'),
                        Spec::col('manager', 'Reports to', 'managers.full_name'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['employees.full_name', 'employees.emp_id', 'managers.full_name']),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Employees', 'count(*)'),
                        Spec::summary('with_manager', 'With a manager', 'sum(case when employees.reporting_manager_id is not null then 1 else 0 end)'),
                    ],
                    'order' => [Spec::order('name')],
                ],

                'banking-and-statutory' => [
                    'label' => 'Banking & Statutory Details',
                    'description' => 'Where each salary is credited, and the statutory registrations behind it.',
                    'base' => 'employees',
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::col('bank_name', 'Bank', 'employees.bank_name'),
                        Spec::col('account_holder_name', 'Account holder', 'employees.account_holder_name'),
                        Spec::col('bank_account_number', 'Account no', 'employees.bank_account_number'),
                        Spec::col('bank_ifsc', 'IFSC', 'employees.bank_ifsc'),
                        Spec::col('pf_number', 'PF no', 'employees.pf_number'),
                        Spec::col('uan', 'UAN', 'employees.uan'),
                        Spec::col('esic_number', 'ESIC no', 'employees.esic_number'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['employees.full_name', 'employees.emp_id', 'employees.bank_account_number']),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Employees', 'count(*)'),
                        Spec::summary('with_bank', 'Bank details on file', 'sum(case when employees.bank_account_number is not null then 1 else 0 end)'),
                        Spec::summary('with_pf', 'PF details on file', 'sum(case when employees.pf_number is not null then 1 else 0 end)'),
                    ],
                    'order' => [Spec::order('name')],
                ],

                'contact-directory' => [
                    'label' => 'Contact Directory',
                    'description' => 'Phone numbers, addresses and emergency contacts.',
                    'base' => 'employees',
                    'joins' => [Spec::join('users', 'employees.user_id', 'users.id')],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::col('email', 'Email', 'users.email'),
                        Spec::col('contact_number', 'Contact', 'employees.contact_number'),
                        Spec::col('personal_number', 'Personal', 'employees.personal_number'),
                        Spec::col('emergency_contact', 'Emergency', 'employees.emergency_contact'),
                        Spec::col('blood_group', 'Blood group', 'employees.blood_group'),
                        Spec::col('address', 'Address', 'employees.address'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['employees.full_name', 'employees.emp_id', 'employees.contact_number']),
                    ],
                    'summary' => [Spec::summary('total', 'Employees', 'count(*)')],
                    'order' => [Spec::order('name')],
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function attendance(): array
    {
        $employee = Spec::join('employees', 'attendances.employee_id', 'employees.id');
        $site = Spec::join('sites', 'attendances.site_id', 'sites.id');
        $shift = Spec::join('shifts', 'attendances.shift_id', 'shifts.id');

        return [
            'label' => 'Attendance',
            'category' => 'Human Resources',
            'permission' => 'attendance.view',
            'reports' => [

                'daily-register' => [
                    'label' => 'Attendance Register',
                    'description' => 'Every check-in and check-out in the period.',
                    'base' => 'attendances',
                    'joins' => [$employee, $site, $shift],
                    'columns' => [
                        Spec::date('date', 'Date', 'attendances.date'),
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::col('site', 'Site', 'sites.name'),
                        Spec::col('shift', 'Shift', 'shifts.name'),
                        Spec::datetime('check_in', 'Check in', 'attendances.check_in'),
                        Spec::datetime('check_out', 'Check out', 'attendances.check_out'),
                        Spec::num('working_hours', 'Hours', 'attendances.working_hours'),
                        Spec::badge('status', 'Status', 'attendances.status'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'attendances.date', '>='),
                        Spec::dateFilter('to', 'To', 'attendances.date', '<='),
                        Spec::sourceFilter('status', 'Status', 'attendances', 'status'),
                        Spec::sourceFilter('site_id', 'Site', 'sites', 'id'),
                        Spec::textFilter('q', 'Search', ['employees.full_name', 'employees.emp_id']),
                    ],
                    'summary' => [
                        Spec::summary('records', 'Records', 'count(*)'),
                        Spec::summary('hours', 'Hours worked', 'sum(attendances.working_hours)'),
                        Spec::summary('late', 'Late arrivals', 'sum(case when attendances.is_late = 1 then 1 else 0 end)'),
                    ],
                    'order' => [Spec::order('date', 'desc')],
                ],

                'summary-by-employee' => [
                    'label' => 'Attendance by Employee',
                    'description' => 'Days present, hours worked and overtime for each person.',
                    'base' => 'attendances',
                    'joins' => [$employee],
                    'group_by' => ['employees.emp_id', 'employees.full_name'],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::num('days', 'Days', 'count(*)'),
                        Spec::num('present', 'Present', "sum(case when attendances.status = 'Present' then 1 else 0 end)"),
                        Spec::num('absent', 'Absent', "sum(case when attendances.status is null or attendances.status <> 'Present' then 1 else 0 end)"),
                        Spec::num('total_hours', 'Hours', 'sum(attendances.working_hours)'),
                        Spec::num('overtime', 'Overtime', 'sum(attendances.overtime_hours)'),
                        Spec::num('avg_hours', 'Avg / day', 'round(avg(attendances.working_hours), 2)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'attendances.date', '>='),
                        Spec::dateFilter('to', 'To', 'attendances.date', '<='),
                        Spec::sourceFilter('status', 'Status', 'attendances', 'status'),
                    ],
                    'summary' => [
                        Spec::summary('employees', 'Employees', 'count(distinct attendances.employee_id)'),
                        Spec::summary('records', 'Records', 'count(*)'),
                    ],
                    'order' => [Spec::order('total_hours', 'desc')],
                    'chart' => Spec::chart('bar', 'name', 'total_hours'),
                ],

                'late-arrivals' => [
                    'label' => 'Late Arrivals',
                    'description' => 'Everyone who came in after their shift threshold.',
                    'base' => 'attendances',
                    'joins' => [$employee, $site, $shift],
                    'constraints' => [
                        ['column' => 'attendances.is_late', 'operator' => '=', 'value' => 1],
                    ],
                    'columns' => [
                        Spec::date('date', 'Date', 'attendances.date'),
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::col('site', 'Site', 'sites.name'),
                        Spec::col('shift', 'Shift', 'shifts.name'),
                        Spec::datetime('check_in', 'Check in', 'attendances.check_in'),
                        Spec::num('late_minutes', 'Late by (min)', 'attendances.late_minutes'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'attendances.date', '>='),
                        Spec::dateFilter('to', 'To', 'attendances.date', '<='),
                        Spec::sourceFilter('site_id', 'Site', 'sites', 'id'),
                    ],
                    'summary' => [
                        Spec::summary('late_days', 'Late days', 'count(*)'),
                        Spec::summary('late_minutes', 'Minutes lost', 'sum(attendances.late_minutes)'),
                    ],
                    'order' => [Spec::order('date', 'desc')],
                ],

                'absenteeism' => [
                    'label' => 'Absenteeism',
                    'description' => 'Days each person was not marked present.',
                    'base' => 'attendances',
                    'joins' => [$employee],
                    'group_by' => ['employees.emp_id', 'employees.full_name'],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::num('days', 'Days recorded', 'count(*)'),
                        Spec::num('absent', 'Absent days', "sum(case when attendances.status is null or attendances.status <> 'Present' then 1 else 0 end)"),
                        Spec::num('present', 'Present days', "sum(case when attendances.status = 'Present' then 1 else 0 end)"),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'attendances.date', '>='),
                        Spec::dateFilter('to', 'To', 'attendances.date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('employees', 'Employees', 'count(distinct attendances.employee_id)'),
                        Spec::summary('absent_days', 'Absent days', 'sum(case when attendances.status is null or attendances.status <> \'Present\' then 1 else 0 end)'),
                    ],
                    'order' => [Spec::order('absent', 'desc')],
                    'chart' => Spec::chart('bar', 'name', 'absent'),
                ],

                'overtime-summary' => [
                    'label' => 'Overtime Summary',
                    'description' => 'Who is working beyond their shift, and by how much.',
                    'base' => 'attendances',
                    'joins' => [$employee],
                    'group_by' => ['employees.emp_id', 'employees.full_name'],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::num('overtime_days', 'Days with overtime', 'sum(case when attendances.overtime_hours > 0 then 1 else 0 end)'),
                        Spec::num('overtime_hours', 'Overtime hours', 'sum(attendances.overtime_hours)'),
                        Spec::num('max_overtime', 'Best day', 'max(attendances.overtime_hours)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'attendances.date', '>='),
                        Spec::dateFilter('to', 'To', 'attendances.date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('employees', 'Employees', 'count(distinct attendances.employee_id)'),
                        Spec::summary('overtime_hours', 'Overtime hours', 'sum(attendances.overtime_hours)'),
                    ],
                    'order' => [Spec::order('overtime_hours', 'desc')],
                    'chart' => Spec::chart('bar', 'name', 'overtime_hours'),
                ],

                'attendance-by-site' => [
                    'label' => 'Attendance by Site',
                    'description' => 'Headcount and hours at each site.',
                    'base' => 'attendances',
                    'joins' => [$site],
                    'group_by' => ['sites.name'],
                    'columns' => [
                        Spec::col('site', 'Site', 'sites.name', ['primary' => true]),
                        Spec::num('records', 'Records', 'count(*)'),
                        Spec::num('people', 'People', 'count(distinct attendances.employee_id)'),
                        Spec::num('total_hours', 'Hours', 'sum(attendances.working_hours)'),
                        Spec::num('avg_hours', 'Avg / record', 'round(avg(attendances.working_hours), 2)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'attendances.date', '>='),
                        Spec::dateFilter('to', 'To', 'attendances.date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('sites', 'Sites', 'count(distinct attendances.site_id)'),
                        Spec::summary('total_hours', 'Hours', 'sum(attendances.working_hours)'),
                    ],
                    'order' => [Spec::order('total_hours', 'desc')],
                    'chart' => Spec::chart('bar', 'site', 'total_hours'),
                ],

                'working-hours-by-employee' => [
                    'label' => 'Working Hours by Employee',
                    'description' => 'Total and average hours in the period.',
                    'base' => 'attendances',
                    'joins' => [$employee],
                    'group_by' => ['employees.emp_id', 'employees.full_name'],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::num('total_hours', 'Total hours', 'sum(attendances.working_hours)'),
                        Spec::num('avg_hours', 'Average', 'round(avg(attendances.working_hours), 2)'),
                        Spec::num('max_hours', 'Longest day', 'max(attendances.working_hours)'),
                        Spec::num('days', 'Days', 'count(*)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'attendances.date', '>='),
                        Spec::dateFilter('to', 'To', 'attendances.date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('employees', 'Employees', 'count(distinct attendances.employee_id)'),
                        Spec::summary('total_hours', 'Hours', 'sum(attendances.working_hours)'),
                    ],
                    'order' => [Spec::order('total_hours', 'desc')],
                ],

                'location-verification' => [
                    'label' => 'Location Verification',
                    'description' => 'How many check-ins were verified against the site geofence.',
                    'base' => 'attendances',
                    'joins' => [$employee, $site],
                    'group_by' => ['attendances.location_verified'],
                    'columns' => [
                        Spec::badge('location_verified', 'Verified', 'attendances.location_verified'),
                        Spec::num('records', 'Check-ins', 'count(*)'),
                        Spec::num('people', 'People', 'count(distinct attendances.employee_id)'),
                        Spec::num('avg_accuracy', 'Avg accuracy (m)', 'round(avg(attendances.accuracy), 2)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'attendances.date', '>='),
                        Spec::dateFilter('to', 'To', 'attendances.date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('records', 'Check-ins', 'count(*)'),
                        Spec::summary('verified', 'Verified', 'sum(case when attendances.location_verified = 1 then 1 else 0 end)'),
                    ],
                    'order' => [Spec::order('records', 'desc')],
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function shifts(): array
    {
        return [
            'label' => 'Shifts',
            'category' => 'Human Resources',
            'permission' => 'shifts.view',
            'reports' => [

                'shift-roster' => [
                    'label' => 'Shift Roster',
                    'description' => 'The shifts the company runs, and their timings.',
                    'base' => 'shifts',
                    'columns' => [
                        Spec::col('name', 'Shift', 'shifts.name', ['primary' => true]),
                        Spec::col('start_time', 'Starts', 'shifts.start_time'),
                        Spec::col('end_time', 'Ends', 'shifts.end_time'),
                        Spec::num('grace_period', 'Grace (min)', 'shifts.grace_period'),
                        Spec::num('late_threshold', 'Late after (min)', 'shifts.late_threshold'),
                        Spec::num('half_day_threshold', 'Half day after (min)', 'shifts.half_day_threshold'),
                        Spec::badge('status', 'Status', 'shifts.status'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('status', 'Status', 'shifts', 'status'),
                        Spec::textFilter('q', 'Search', ['shifts.name']),
                    ],
                    'summary' => [Spec::summary('total', 'Shifts', 'count(*)')],
                    'order' => [Spec::order('name')],
                ],

                'shift-wise-attendance' => [
                    'label' => 'Attendance by Shift',
                    'description' => 'How each shift is being used.',
                    'base' => 'attendances',
                    'joins' => [Spec::join('shifts', 'attendances.shift_id', 'shifts.id')],
                    'group_by' => ['shifts.name'],
                    'columns' => [
                        Spec::col('shift', 'Shift', 'shifts.name', ['primary' => true]),
                        Spec::num('records', 'Check-ins', 'count(*)'),
                        Spec::num('people', 'People', 'count(distinct attendances.employee_id)'),
                        Spec::num('total_hours', 'Hours', 'sum(attendances.working_hours)'),
                        Spec::num('avg_hours', 'Avg / record', 'round(avg(attendances.working_hours), 2)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'attendances.date', '>='),
                        Spec::dateFilter('to', 'To', 'attendances.date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('shifts', 'Shifts used', 'count(distinct attendances.shift_id)'),
                        Spec::summary('total_hours', 'Hours', 'sum(attendances.working_hours)'),
                    ],
                    'order' => [Spec::order('records', 'desc')],
                    'chart' => Spec::chart('bar', 'shift', 'records'),
                ],

                'employee-shift-assignment' => [
                    'label' => 'Current Shift Assignment',
                    'description' => 'The shift each person is on.',
                    'base' => 'employee_shifts',
                    'joins' => [
                        Spec::join('employees', 'employee_shifts.employee_id', 'employees.id'),
                        Spec::join('shifts', 'employee_shifts.shift_id', 'shifts.id'),
                    ],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::col('shift', 'Shift', 'shifts.name'),
                        Spec::col('shift_start', 'Starts', 'shifts.start_time'),
                        Spec::col('shift_end', 'Ends', 'shifts.end_time'),
                        Spec::datetime('assigned_at', 'Assigned', 'employee_shifts.assigned_at'),
                        Spec::col('remarks', 'Remarks', 'employee_shifts.remarks'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('shift_id', 'Shift', 'shifts', 'id'),
                        Spec::textFilter('q', 'Search', ['employees.full_name', 'employees.emp_id']),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Assignments', 'count(*)'),
                        Spec::summary('shifts', 'Shifts in use', 'count(distinct employee_shifts.shift_id)'),
                    ],
                    'order' => [Spec::order('name')],
                ],

                'shift-assignment-history' => [
                    'label' => 'Shift Assignment History',
                    'description' => 'Every move from one shift to another.',
                    'base' => 'employee_shift_histories',
                    'joins' => [
                        Spec::join('employees', 'employee_shift_histories.employee_id', 'employees.id'),
                        Spec::join('shifts', 'employee_shift_histories.previous_shift_id', 'previous_shifts.id', 'left', 'previous_shifts'),
                        Spec::join('shifts', 'employee_shift_histories.new_shift_id', 'new_shifts.id', 'left', 'new_shifts'),
                    ],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::col('from_shift', 'From shift', 'previous_shifts.name'),
                        Spec::col('to_shift', 'To shift', 'new_shifts.name'),
                        Spec::datetime('assigned_at', 'Assigned', 'employee_shift_histories.assigned_at'),
                        Spec::datetime('effective_date', 'Effective', 'employee_shift_histories.effective_date'),
                        Spec::col('remarks', 'Remarks', 'employee_shift_histories.remarks'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['employees.full_name', 'employees.emp_id']),
                    ],
                    'summary' => [Spec::summary('total', 'Changes', 'count(*)')],
                    'order' => [Spec::order('assigned_at', 'desc')],
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function dailyReports(): array
    {
        $employee = Spec::join('employees', 'daily_reports.employee_id', 'employees.id');
        $site = Spec::join('sites', 'daily_reports.site_id', 'sites.id');

        return [
            'label' => 'Daily Reports',
            'category' => 'Human Resources',
            'permission' => 'daily-reports.view',
            'reports' => [

                'register' => [
                    'label' => 'Daily Report Register',
                    'description' => 'What the site team reported on each day.',
                    'base' => 'daily_reports',
                    'joins' => [$employee, $site],
                    'columns' => [
                        Spec::date('date', 'Date', 'daily_reports.date'),
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::col('site', 'Site', 'sites.name'),
                        Spec::col('work_description', 'Work done', 'daily_reports.work_description'),
                        Spec::num('hours_worked', 'Hours', 'daily_reports.hours_worked'),
                        Spec::badge('status', 'Status', 'daily_reports.status'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'daily_reports.date', '>='),
                        Spec::dateFilter('to', 'To', 'daily_reports.date', '<='),
                        Spec::sourceFilter('status', 'Status', 'daily_reports', 'status'),
                        Spec::sourceFilter('site_id', 'Site', 'sites', 'id'),
                        Spec::textFilter('q', 'Search', ['employees.full_name', 'employees.emp_id']),
                    ],
                    'summary' => [
                        Spec::summary('reports', 'Reports', 'count(*)'),
                        Spec::summary('hours', 'Hours reported', 'sum(daily_reports.hours_worked)'),
                    ],
                    'order' => [Spec::order('date', 'desc')],
                ],

                'hours-by-employee' => [
                    'label' => 'Reported Hours by Employee',
                    'description' => 'How much each person reported working.',
                    'base' => 'daily_reports',
                    'joins' => [$employee],
                    'group_by' => ['employees.emp_id', 'employees.full_name'],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::num('reports', 'Reports', 'count(*)'),
                        Spec::num('hours', 'Hours', 'sum(daily_reports.hours_worked)'),
                        Spec::num('avg_hours', 'Avg per report', 'round(avg(daily_reports.hours_worked), 2)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'daily_reports.date', '>='),
                        Spec::dateFilter('to', 'To', 'daily_reports.date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('employees', 'Employees', 'count(distinct daily_reports.employee_id)'),
                        Spec::summary('hours', 'Hours', 'sum(daily_reports.hours_worked)'),
                    ],
                    'order' => [Spec::order('hours', 'desc')],
                    'chart' => Spec::chart('bar', 'name', 'hours'),
                ],

                'status-summary' => [
                    'label' => 'Report Status Summary',
                    'description' => 'How many reports are submitted, approved or still open.',
                    'base' => 'daily_reports',
                    'group_by' => ['daily_reports.status'],
                    'columns' => [
                        Spec::badge('status', 'Status', 'daily_reports.status', ['primary' => true]),
                        Spec::num('reports', 'Reports', 'count(*)'),
                        Spec::num('hours', 'Hours', 'sum(daily_reports.hours_worked)'),
                    ],
                    'summary' => [Spec::summary('total', 'Reports', 'count(*)')],
                    'order' => [Spec::order('reports', 'desc')],
                    'chart' => Spec::chart('pie', 'status', 'reports'),
                ],

                'site-productivity' => [
                    'label' => 'Site Productivity',
                    'description' => 'Reports and hours filed per site.',
                    'base' => 'daily_reports',
                    'joins' => [$site],
                    'group_by' => ['sites.name'],
                    'columns' => [
                        Spec::col('site', 'Site', 'sites.name', ['primary' => true]),
                        Spec::num('reports', 'Reports', 'count(*)'),
                        Spec::num('people', 'People', 'count(distinct daily_reports.employee_id)'),
                        Spec::num('hours', 'Hours', 'sum(daily_reports.hours_worked)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'daily_reports.date', '>='),
                        Spec::dateFilter('to', 'To', 'daily_reports.date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('sites', 'Sites', 'count(distinct daily_reports.site_id)'),
                        Spec::summary('hours', 'Hours', 'sum(daily_reports.hours_worked)'),
                    ],
                    'order' => [Spec::order('hours', 'desc')],
                    'chart' => Spec::chart('bar', 'site', 'hours'),
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function leaves(): array
    {
        $employee = Spec::join('employees', 'leaves.employee_id', 'employees.id');
        $leaveType = Spec::join('leave_types', 'leaves.leave_type_id', 'leave_types.id');
        $department = Spec::join('departments', 'employees.department_id', 'departments.id');

        return [
            'label' => 'Leaves',
            'category' => 'Human Resources',
            'permission' => 'leaves.view',
            'reports' => [

                'leave-register' => [
                    'label' => 'Leave Register',
                    'description' => 'Every leave request, with its decision and the days it cost.',
                    'base' => 'leaves',
                    'joins' => [$employee, $leaveType],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::col('leave_type', 'Type', 'leave_types.name'),
                        Spec::date('start_date', 'From', 'leaves.start_date'),
                        Spec::date('end_date', 'To', 'leaves.end_date'),
                        Spec::num('total_days', 'Days', 'leaves.total_days'),
                        Spec::badge('status', 'Status', 'leaves.status'),
                        Spec::col('reason', 'Reason', 'leaves.reason'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'leaves.start_date', '>='),
                        Spec::dateFilter('to', 'To', 'leaves.start_date', '<='),
                        Spec::sourceFilter('status', 'Status', 'leaves', 'status'),
                        Spec::sourceFilter('leave_type_id', 'Leave type', 'leave_types', 'id'),
                        Spec::textFilter('q', 'Search', ['employees.full_name', 'employees.emp_id']),
                    ],
                    'summary' => [
                        Spec::summary('requests', 'Requests', 'count(*)'),
                        Spec::summary('days', 'Days', 'sum(leaves.total_days)'),
                    ],
                    'order' => [Spec::order('start_date', 'desc')],
                ],

                'pending-approvals' => [
                    'label' => 'Pending Approvals',
                    'description' => 'Leave requests still waiting on a decision.',
                    'base' => 'leaves',
                    'joins' => [$employee, $leaveType],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::col('leave_type', 'Type', 'leave_types.name'),
                        Spec::date('start_date', 'From', 'leaves.start_date'),
                        Spec::date('end_date', 'To', 'leaves.end_date'),
                        Spec::num('total_days', 'Days', 'leaves.total_days'),
                        Spec::badge('status', 'Status', 'leaves.status'),
                    ],
                    'filters' => [
                        Spec::multiFilter('status', 'Status', 'leaves.status', ['Submitted', 'Pending']),
                        Spec::dateFilter('from', 'From', 'leaves.start_date', '>='),
                        Spec::dateFilter('to', 'To', 'leaves.start_date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('requests', 'Pending requests', 'count(*)'),
                        Spec::summary('days', 'Days requested', 'sum(leaves.total_days)'),
                    ],
                    'order' => [Spec::order('start_date', 'desc')],
                ],

                'leave-balance' => [
                    'label' => 'Leave Balance',
                    'description' => 'Allocation against usage for every employee and leave type.',
                    'base' => 'employee_leave_balances',
                    'joins' => [
                        Spec::join('employees', 'employee_leave_balances.employee_id', 'employees.id'),
                        Spec::join('leave_types', 'employee_leave_balances.leave_type_id', 'leave_types.id'),
                    ],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::col('leave_type', 'Leave type', 'leave_types.name'),
                        Spec::num('year', 'Year', 'employee_leave_balances.year'),
                        Spec::num('allocated', 'Allocated', 'employee_leave_balances.allocated'),
                        Spec::num('used', 'Used', 'employee_leave_balances.used'),
                        Spec::num('remaining', 'Remaining', 'employee_leave_balances.remaining'),
                        Spec::num('carry_forward', 'Carried over', 'employee_leave_balances.carry_forward'),
                        Spec::num('expired', 'Expired', 'employee_leave_balances.expired'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('leave_type_id', 'Leave type', 'leave_types', 'id'),
                        Spec::textFilter('q', 'Search', ['employees.full_name', 'employees.emp_id']),
                    ],
                    'summary' => [
                        Spec::summary('employees', 'Employees', 'count(distinct employee_leave_balances.employee_id)'),
                        Spec::summary('allocated', 'Days allocated', 'sum(employee_leave_balances.allocated)'),
                        Spec::summary('used', 'Days used', 'sum(employee_leave_balances.used)'),
                        Spec::summary('remaining', 'Days left', 'sum(employee_leave_balances.remaining)'),
                    ],
                    'order' => [Spec::order('name')],
                ],

                'leave-by-type' => [
                    'label' => 'Leave by Type',
                    'description' => 'Which kinds of leave the company actually takes.',
                    'base' => 'leaves',
                    'joins' => [$leaveType],
                    'group_by' => ['leave_types.name'],
                    'columns' => [
                        Spec::col('leave_type', 'Leave type', 'leave_types.name', ['primary' => true]),
                        Spec::num('requests', 'Requests', 'count(*)'),
                        Spec::num('days', 'Days', 'sum(leaves.total_days)'),
                        Spec::num('people', 'People', 'count(distinct leaves.employee_id)'),
                    ],
                    'summary' => [
                        Spec::summary('requests', 'Requests', 'count(*)'),
                        Spec::summary('days', 'Days', 'sum(leaves.total_days)'),
                    ],
                    'order' => [Spec::order('days', 'desc')],
                    'chart' => Spec::chart('bar', 'leave_type', 'days'),
                ],

                'leave-by-department' => [
                    'label' => 'Leave by Department',
                    'description' => 'Where the leave is being taken.',
                    'base' => 'leaves',
                    'joins' => [$employee, $department],
                    'group_by' => ['departments.name'],
                    'columns' => [
                        Spec::col('department', 'Department', 'departments.name', ['primary' => true]),
                        Spec::num('requests', 'Requests', 'count(*)'),
                        Spec::num('days', 'Days', 'sum(leaves.total_days)'),
                        Spec::num('people', 'People', 'count(distinct leaves.employee_id)'),
                    ],
                    'summary' => [
                        Spec::summary('requests', 'Requests', 'count(*)'),
                        Spec::summary('days', 'Days', 'sum(leaves.total_days)'),
                    ],
                    'order' => [Spec::order('days', 'desc')],
                    'chart' => Spec::chart('bar', 'department', 'days'),
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function leaveTypes(): array
    {
        return [
            'label' => 'Leave Types',
            'category' => 'Human Resources',
            'permission' => 'leave-types.view',
            'reports' => [

                'master' => [
                    'label' => 'Leave Type Master',
                    'description' => 'The kinds of leave the company allows, and their rules.',
                    'base' => 'leave_types',
                    'columns' => [
                        Spec::col('name', 'Leave type', 'leave_types.name', ['primary' => true]),
                        Spec::col('code', 'Code', 'leave_types.code'),
                        Spec::num('annual_allocation', 'Annual allocation', 'leave_types.annual_allocation'),
                        Spec::col('carry_forward', 'Carry forward', 'leave_types.carry_forward'),
                        Spec::num('max_consecutive_days', 'Max consecutive days', 'leave_types.max_consecutive_days'),
                        Spec::col('requires_approval', 'Needs approval', 'leave_types.requires_approval'),
                        Spec::badge('status', 'Status', 'leave_types.status'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('status', 'Status', 'leave_types', 'status'),
                        Spec::textFilter('q', 'Search', ['leave_types.name', 'leave_types.code']),
                    ],
                    'summary' => [Spec::summary('total', 'Leave types', 'count(*)')],
                    'order' => [Spec::order('name')],
                ],

                'allocation-vs-usage' => [
                    'label' => 'Allocation vs Usage',
                    'description' => 'How much leave was granted against how much was taken.',
                    'base' => 'employee_leave_balances',
                    'joins' => [Spec::join('leave_types', 'employee_leave_balances.leave_type_id', 'leave_types.id')],
                    'group_by' => ['leave_types.name'],
                    'columns' => [
                        Spec::col('leave_type', 'Leave type', 'leave_types.name', ['primary' => true]),
                        Spec::num('allocated', 'Allocated', 'sum(employee_leave_balances.allocated)'),
                        Spec::num('used', 'Used', 'sum(employee_leave_balances.used)'),
                        Spec::num('remaining', 'Remaining', 'sum(employee_leave_balances.remaining)'),
                        Spec::num('expired', 'Expired', 'sum(employee_leave_balances.expired)'),
                    ],
                    'summary' => [
                        Spec::summary('allocated', 'Allocated', 'sum(employee_leave_balances.allocated)'),
                        Spec::summary('used', 'Used', 'sum(employee_leave_balances.used)'),
                    ],
                    'order' => [Spec::order('allocated', 'desc')],
                    'chart' => Spec::chart('bar', 'leave_type', 'allocated'),
                ],
            ],
        ];
    }
}
