<?php

namespace App\Support\Reports\Modules;

use App\Support\Reports\Spec;

/**
 * Payroll reports.
 *
 * Money that leaves the company: what each person is paid, what they are
 * deducted, and the periods the whole run is booked against.
 */
class Payroll
{
    public static function modules(): array
    {
        return [
            'payroll' => self::payroll(),
            'salary-structures' => self::salaryStructures(),
            'payslips' => self::payslips(),
            'payroll-period' => self::payrollPeriods(),
            'process-payroll' => self::processPayroll(),
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function payroll(): array
    {
        $employee = Spec::join('employees', 'payrolls.employee_id', 'employees.id');
        $department = Spec::join('departments', 'employees.department_id', 'departments.id');
        $period = Spec::join('payroll_periods', 'payrolls.payroll_period_id', 'payroll_periods.id');

        // Gross pay is not stored as one column - it is the sum of the
        // earning heads, so every report derives it the same way.
        $gross = '(payrolls.basic_salary + payrolls.hra + payrolls.conveyance + payrolls.medical_allowance + payrolls.special_allowance + payrolls.site_allowance + payrolls.travel_allowance + payrolls.food_allowance + payrolls.other_allowance + payrolls.overtime_pay + payrolls.bonuses)';

        $deductions = '(payrolls.pf + payrolls.pt + payrolls.esic + payrolls.tds + payrolls.other_deductions + payrolls.late_penalty + payrolls.salary_advance + payrolls.loss_of_pay)';

        return [
            'label' => 'Payroll',
            'category' => 'Payroll',
            'permission' => 'payroll.view',
            'reports' => [

                'register' => [
                    'label' => 'Payroll Register',
                    'description' => 'Every payslip run, with the earnings and the deductions behind it.',
                    'base' => 'payrolls',
                    'joins' => [$employee, $department, $period],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::col('department', 'Department', 'departments.name'),
                        Spec::col('period', 'Period', "concat(payrolls.month, '/', payrolls.year)"),
                        Spec::num('month', 'Month', 'payrolls.month'),
                        Spec::num('year', 'Year', 'payrolls.year'),
                        Spec::money('gross', 'Gross', $gross),
                        Spec::money('net_salary', 'Net paid', 'payrolls.net_salary'),
                        Spec::badge('status', 'Status', 'payrolls.status'),
                    ],
                    'filters' => [
                        Spec::multiFilter('status', 'Status', 'payrolls.status'),
                        Spec::numberFilter('year', 'Year', 'payrolls.year'),
                        Spec::sourceFilter('employee_id', 'Employee', 'employees', 'id'),
                        Spec::textFilter('q', 'Search', ['employees.full_name', 'employees.emp_id']),
                    ],
                    'summary' => [
                        Spec::summary('records', 'Payslips', 'count(*)'),
                        Spec::summary('gross', 'Gross payroll', "sum{$gross}", 'money'),
                        Spec::summary('net', 'Net paid', 'sum(payrolls.net_salary)', 'money'),
                    ],
                    'order' => [Spec::order('year', 'desc'), Spec::order('month', 'desc')],
                ],

                'earnings-breakdown' => [
                    'label' => 'Earnings Breakdown',
                    'description' => 'What the salary is made of, head by head.',
                    'base' => 'payrolls',
                    'joins' => [$employee],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::num('month', 'Month', 'payrolls.month'),
                        Spec::num('year', 'Year', 'payrolls.year'),
                        Spec::money('basic', 'Basic', 'payrolls.basic_salary'),
                        Spec::money('hra', 'HRA', 'payrolls.hra'),
                        Spec::money('conveyance', 'Conveyance', 'payrolls.conveyance'),
                        Spec::money('medical', 'Medical', 'payrolls.medical_allowance'),
                        Spec::money('special', 'Special', 'payrolls.special_allowance'),
                        Spec::money('other', 'Other allowance', 'payrolls.other_allowance'),
                        Spec::money('overtime', 'Overtime pay', 'payrolls.overtime_pay'),
                        Spec::money('bonuses', 'Bonuses', 'payrolls.bonuses'),
                    ],
                    'filters' => [
                        Spec::numberFilter('year', 'Year', 'payrolls.year'),
                        Spec::sourceFilter('employee_id', 'Employee', 'employees', 'id'),
                    ],
                    'summary' => [
                        Spec::summary('records', 'Payslips', 'count(*)'),
                        Spec::summary('basic', 'Basic total', 'sum(payrolls.basic_salary)', 'money'),
                        Spec::summary('gross', 'Gross total', "sum{$gross}", 'money'),
                    ],
                    'order' => [Spec::order('year', 'desc'), Spec::order('month', 'desc')],
                ],

                'deductions-breakdown' => [
                    'label' => 'Deductions Breakdown',
                    'description' => 'What was taken off each pay, and why.',
                    'base' => 'payrolls',
                    'joins' => [$employee],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::num('month', 'Month', 'payrolls.month'),
                        Spec::num('year', 'Year', 'payrolls.year'),
                        Spec::money('pf', 'PF', 'payrolls.pf'),
                        Spec::money('pt', 'Professional tax', 'payrolls.pt'),
                        Spec::money('esic', 'ESIC', 'payrolls.esic'),
                        Spec::money('tds', 'TDS', 'payrolls.tds'),
                        Spec::money('other', 'Other deductions', 'payrolls.other_deductions'),
                        Spec::money('late_penalty', 'Late penalty', 'payrolls.late_penalty'),
                        Spec::money('advance', 'Salary advance', 'payrolls.salary_advance'),
                        Spec::money('loss_of_pay', 'Loss of pay', 'payrolls.loss_of_pay'),
                        Spec::money('total_deductions', 'Total deducted', $deductions),
                    ],
                    'filters' => [
                        Spec::numberFilter('year', 'Year', 'payrolls.year'),
                        Spec::sourceFilter('employee_id', 'Employee', 'employees', 'id'),
                    ],
                    'summary' => [
                        Spec::summary('records', 'Payslips', 'count(*)'),
                        Spec::summary('pf', 'PF total', 'sum(payrolls.pf)', 'money'),
                        Spec::summary('total', 'Total deducted', "sum{$deductions}", 'money'),
                    ],
                    'order' => [Spec::order('year', 'desc'), Spec::order('month', 'desc')],
                ],

                'net-pay-by-employee' => [
                    'label' => 'Net Pay by Employee',
                    'description' => 'What each person actually received, across the period.',
                    'base' => 'payrolls',
                    'joins' => [$employee, $department],
                    'group_by' => ['employees.emp_id', 'employees.full_name', 'departments.name'],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::col('department', 'Department', 'departments.name'),
                        Spec::num('payslips', 'Payslips', 'count(*)'),
                        Spec::money('gross', 'Gross', "sum{$gross}"),
                        Spec::money('net', 'Net paid', 'sum(payrolls.net_salary)'),
                        Spec::money('average_net', 'Average net', 'round(avg(payrolls.net_salary), 2)'),
                    ],
                    'filters' => [
                        Spec::numberFilter('year', 'Year', 'payrolls.year'),
                        Spec::textFilter('q', 'Search', ['employees.full_name', 'employees.emp_id']),
                    ],
                    'summary' => [
                        Spec::summary('employees', 'Employees paid', 'count(distinct payrolls.employee_id)'),
                        Spec::summary('net', 'Net paid', 'sum(payrolls.net_salary)', 'money'),
                    ],
                    'order' => [Spec::order('net', 'desc')],
                    'chart' => Spec::chart('bar', 'name', 'net'),
                ],

                'payroll-by-department' => [
                    'label' => 'Payroll Cost by Department',
                    'description' => 'What each department costs to run.',
                    'base' => 'payrolls',
                    'joins' => [$employee, $department],
                    'group_by' => ['departments.name'],
                    'columns' => [
                        Spec::col('department', 'Department', 'departments.name', ['primary' => true]),
                        Spec::num('employees', 'Employees', 'count(distinct payrolls.employee_id)'),
                        Spec::num('payslips', 'Payslips', 'count(*)'),
                        Spec::money('gross', 'Gross', "sum{$gross}"),
                        Spec::money('deductions', 'Deductions', "sum{$deductions}"),
                        Spec::money('net', 'Net paid', 'sum(payrolls.net_salary)'),
                        Spec::money('average_net', 'Average net', 'round(avg(payrolls.net_salary), 2)'),
                    ],
                    'filters' => [
                        Spec::numberFilter('year', 'Year', 'payrolls.year'),
                    ],
                    'summary' => [
                        Spec::summary('departments', 'Departments', 'count(distinct departments.id)'),
                        Spec::summary('net', 'Net paid', 'sum(payrolls.net_salary)', 'money'),
                    ],
                    'order' => [Spec::order('net', 'desc')],
                    'chart' => Spec::chart('bar', 'department', 'net'),
                ],

                'monthly-payroll-trend' => [
                    'label' => 'Monthly Payroll Trend',
                    'description' => 'How the payroll bill moved month by month.',
                    'base' => 'payrolls',
                    'group_by' => ['payrolls.year', 'payrolls.month'],
                    'columns' => [
                        Spec::col('period', 'Period', "concat(payrolls.month, '/', payrolls.year)", ['primary' => true]),
                        Spec::num('year', 'Year', 'payrolls.year'),
                        Spec::num('month', 'Month', 'payrolls.month'),
                        Spec::num('employees', 'Employees', 'count(distinct payrolls.employee_id)'),
                        Spec::money('gross', 'Gross', "sum{$gross}"),
                        Spec::money('net', 'Net paid', 'sum(payrolls.net_salary)'),
                    ],
                    'filters' => [
                        Spec::numberFilter('year', 'Year', 'payrolls.year'),
                    ],
                    'summary' => [
                        Spec::summary('months', 'Months', 'count(*)'),
                        Spec::summary('net', 'Net paid', 'sum(payrolls.net_salary)', 'money'),
                    ],
                    'order' => [Spec::order('year', 'desc'), Spec::order('month', 'desc')],
                    'chart' => Spec::chart('line', 'period', 'net'),
                ],

                'statutory-deductions' => [
                    'label' => 'Statutory Deductions',
                    'description' => 'PF, PT, ESIC and TDS - the statutory share of every pay.',
                    'base' => 'payrolls',
                    'joins' => [$employee],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::num('month', 'Month', 'payrolls.month'),
                        Spec::num('year', 'Year', 'payrolls.year'),
                        Spec::money('gross', 'Gross', $gross),
                        Spec::money('pf', 'PF', 'payrolls.pf'),
                        Spec::money('pt', 'Professional tax', 'payrolls.pt'),
                        Spec::money('esic', 'ESIC', 'payrolls.esic'),
                        Spec::money('tds', 'TDS', 'payrolls.tds'),
                        Spec::money('statutory_total', 'Statutory total', '(payrolls.pf + payrolls.pt + payrolls.esic + payrolls.tds)'),
                    ],
                    'filters' => [
                        Spec::numberFilter('year', 'Year', 'payrolls.year'),
                    ],
                    'summary' => [
                        Spec::summary('pf', 'PF total', 'sum(payrolls.pf)', 'money'),
                        Spec::summary('tds', 'TDS total', 'sum(payrolls.tds)', 'money'),
                        Spec::summary('statutory', 'Statutory total', 'sum(payrolls.pf + payrolls.pt + payrolls.esic + payrolls.tds)', 'money'),
                    ],
                    'order' => [Spec::order('year', 'desc'), Spec::order('month', 'desc')],
                ],

                'attendance-linked-pay' => [
                    'label' => 'Attendance-Linked Pay',
                    'description' => 'How present days and unpaid leave moved each pay.',
                    'base' => 'payrolls',
                    'joins' => [$employee],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::num('month', 'Month', 'payrolls.month'),
                        Spec::num('year', 'Year', 'payrolls.year'),
                        Spec::num('working_days', 'Working days', 'payrolls.working_days'),
                        Spec::num('present_days', 'Present', 'payrolls.present_days'),
                        Spec::num('absent_days', 'Absent', 'payrolls.absent_days'),
                        Spec::num('paid_leaves', 'Paid leaves', 'payrolls.paid_leaves'),
                        Spec::num('unpaid_leaves', 'Unpaid leaves', 'payrolls.unpaid_leaves'),
                        Spec::money('loss_of_pay', 'Loss of pay', 'payrolls.loss_of_pay'),
                        Spec::money('net_salary', 'Net paid', 'payrolls.net_salary'),
                    ],
                    'filters' => [
                        Spec::numberFilter('year', 'Year', 'payrolls.year'),
                        Spec::sourceFilter('employee_id', 'Employee', 'employees', 'id'),
                    ],
                    'summary' => [
                        Spec::summary('records', 'Payslips', 'count(*)'),
                        Spec::summary('absent', 'Absent days', 'sum(payrolls.absent_days)'),
                        Spec::summary('unpaid_leaves', 'Unpaid leave days', 'sum(payrolls.unpaid_leaves)'),
                    ],
                    'order' => [Spec::order('year', 'desc'), Spec::order('month', 'desc')],
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function salaryStructures(): array
    {
        $employee = Spec::join('employees', 'salary_structures.employee_id', 'employees.id');
        $department = Spec::join('departments', 'employees.department_id', 'departments.id');

        $gross = '(salary_structures.basic_salary + salary_structures.hra + salary_structures.conveyance + salary_structures.medical_allowance + salary_structures.special_allowance + salary_structures.site_allowance + salary_structures.travel_allowance + salary_structures.food_allowance + salary_structures.other_earnings)';

        return [
            'label' => 'Salary Structures',
            'category' => 'Payroll',
            'permission' => 'salary-structures.view',
            'reports' => [

                'master' => [
                    'label' => 'Salary Structure Master',
                    'description' => 'The agreed structure behind every salary.',
                    'base' => 'salary_structures',
                    'joins' => [$employee, $department],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::col('department', 'Department', 'departments.name'),
                        Spec::money('basic', 'Basic', 'salary_structures.basic_salary'),
                        Spec::money('hra', 'HRA', 'salary_structures.hra'),
                        Spec::money('gross', 'Gross', $gross),
                        Spec::date('effective_from', 'From', 'salary_structures.effective_from'),
                        Spec::date('effective_to', 'To', 'salary_structures.effective_to'),
                        Spec::badge('status', 'Status', 'salary_structures.status'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('status', 'Status', 'salary_structures', 'status'),
                        Spec::textFilter('q', 'Search', ['employees.full_name', 'employees.emp_id']),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Structures', 'count(*)'),
                        Spec::summary('gross', 'Monthly gross', "sum{$gross}", 'money'),
                    ],
                    'order' => [Spec::order('name')],
                ],

                'component-breakdown' => [
                    'label' => 'Salary Component Breakdown',
                    'description' => 'Every allowance and deduction in the structure.',
                    'base' => 'salary_structures',
                    'joins' => [$employee],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::money('basic', 'Basic', 'salary_structures.basic_salary'),
                        Spec::money('hra', 'HRA', 'salary_structures.hra'),
                        Spec::money('conveyance', 'Conveyance', 'salary_structures.conveyance'),
                        Spec::money('medical', 'Medical', 'salary_structures.medical_allowance'),
                        Spec::money('special', 'Special', 'salary_structures.special_allowance'),
                        Spec::money('site', 'Site', 'salary_structures.site_allowance'),
                        Spec::money('travel', 'Travel', 'salary_structures.travel_allowance'),
                        Spec::money('food', 'Food', 'salary_structures.food_allowance'),
                        Spec::money('other', 'Other earnings', 'salary_structures.other_earnings'),
                        Spec::money('pf', 'PF deduction', 'salary_structures.pf_deduction'),
                        Spec::money('esic', 'ESIC deduction', 'salary_structures.esic_deduction'),
                        Spec::money('pt', 'Professional tax', 'salary_structures.professional_tax'),
                        Spec::money('tds', 'TDS', 'salary_structures.tds'),
                        Spec::money('other_deductions', 'Other deductions', 'salary_structures.other_deductions'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['employees.full_name', 'employees.emp_id']),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Structures', 'count(*)'),
                        Spec::summary('pf', 'PF total', 'sum(salary_structures.pf_deduction)', 'money'),
                        Spec::summary('tds', 'TDS total', 'sum(salary_structures.tds)', 'money'),
                    ],
                    'order' => [Spec::order('name')],
                ],

                'gross-by-department' => [
                    'label' => 'Gross Salary by Department',
                    'description' => 'The committed monthly salary cost per department.',
                    'base' => 'salary_structures',
                    'joins' => [$employee, $department],
                    'group_by' => ['departments.name'],
                    'columns' => [
                        Spec::col('department', 'Department', 'departments.name', ['primary' => true]),
                        Spec::num('employees', 'Employees', 'count(distinct salary_structures.employee_id)'),
                        Spec::money('gross', 'Monthly gross', "sum{$gross}"),
                        Spec::money('average_gross', 'Average gross', "round(avg{$gross}, 2)"),
                    ],
                    'summary' => [
                        Spec::summary('departments', 'Departments', 'count(distinct departments.id)'),
                        Spec::summary('gross', 'Monthly gross', "sum{$gross}", 'money'),
                    ],
                    'order' => [Spec::order('gross', 'desc')],
                    'chart' => Spec::chart('bar', 'department', 'gross'),
                ],

                'salary-range' => [
                    'label' => 'Salary Range Analysis',
                    'description' => 'The spread of salaries across the organisation.',
                    'base' => 'salary_structures',
                    'joins' => [$employee, $department],
                    'group_by' => ['departments.name'],
                    'columns' => [
                        Spec::col('department', 'Department', 'departments.name', ['primary' => true]),
                        Spec::num('employees', 'Employees', 'count(*)'),
                        Spec::money('minimum', 'Lowest', "min{$gross}"),
                        Spec::money('maximum', 'Highest', "max{$gross}"),
                        Spec::money('average', 'Average', "round(avg{$gross}, 2)"),
                        Spec::money('total', 'Total', "sum{$gross}"),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Structures', 'count(*)'),
                        Spec::summary('average', 'Average gross', "round(avg{$gross}, 2)", 'money'),
                    ],
                    'order' => [Spec::order('average', 'desc')],
                ],

                'increment-history' => [
                    'label' => 'Increment History',
                    'description' => 'Every revision of a salary structure, and when it took effect.',
                    'base' => 'salary_structures',
                    'joins' => [$employee],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::money('basic', 'Basic', 'salary_structures.basic_salary'),
                        Spec::money('gross', 'Gross', $gross),
                        Spec::date('effective_from', 'Effective from', 'salary_structures.effective_from'),
                        Spec::date('effective_to', 'Effective to', 'salary_structures.effective_to'),
                        Spec::col('auto_statutory', 'Auto statutory', 'salary_structures.auto_statutory'),
                        Spec::badge('status', 'Status', 'salary_structures.status'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'Effective from', 'salary_structures.effective_from', '>='),
                        Spec::dateFilter('to', 'Effective to', 'salary_structures.effective_to', '<='),
                    ],
                    'summary' => [
                        Spec::summary('records', 'Revisions', 'count(*)'),
                        Spec::summary('gross', 'Gross', "sum{$gross}", 'money'),
                    ],
                    'order' => [Spec::order('effective_from', 'desc')],
                ],

                'missing-structures' => [
                    'label' => 'Employees Without a Structure',
                    'description' => 'People with no salary structure on file - the gap list.',
                    'base' => 'employees',
                    'joins' => [
                        Spec::join('departments', 'employees.department_id', 'departments.id'),
                        Spec::join('salary_structures', 'salary_structures.employee_id', 'employees.id'),
                    ],
                    'constraints' => [
                        ['column' => 'salary_structures.id', 'operator' => '=', 'value' => null],
                    ],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::col('department', 'Department', 'departments.name'),
                        Spec::col('employment_type', 'Type', 'employees.employment_type'),
                        Spec::date('joining_date', 'Joined', 'employees.joining_date'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['employees.full_name', 'employees.emp_id']),
                    ],
                    'summary' => [Spec::summary('total', 'Without structure', 'count(*)')],
                    'order' => [Spec::order('name')],
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function payslips(): array
    {
        $payroll = Spec::join('payrolls', 'payslips.payroll_id', 'payrolls.id');
        $employee = Spec::join('employees', 'payrolls.employee_id', 'employees.id');
        $payslip = Spec::join('payslips', 'payslips.payroll_id', 'payrolls.id');

        return [
            'label' => 'Payslips',
            'category' => 'Payroll',
            'permission' => 'payslips.view',
            'reports' => [

                'register' => [
                    'label' => 'Payslip Register',
                    'description' => 'Every payslip that has been generated.',
                    'base' => 'payslips',
                    'joins' => [$payroll, $employee],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::num('month', 'Month', 'payrolls.month'),
                        Spec::num('year', 'Year', 'payrolls.year'),
                        Spec::money('net_salary', 'Net paid', 'payrolls.net_salary'),
                        Spec::datetime('generated_at', 'Generated', 'payslips.generated_at'),
                        Spec::col('file_path', 'File', 'payslips.file_path'),
                    ],
                    'filters' => [
                        Spec::numberFilter('year', 'Year', 'payrolls.year'),
                        Spec::textFilter('q', 'Search', ['employees.full_name', 'employees.emp_id']),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Payslips', 'count(*)'),
                        Spec::summary('net', 'Net paid', 'sum(payrolls.net_salary)', 'money'),
                    ],
                    'order' => [Spec::order('generated_at', 'desc')],
                ],

                'generation-status' => [
                    'label' => 'Payslip Generation Status',
                    'description' => 'Who has a payslip on file and who is still missing one.',
                    'base' => 'payrolls',
                    'joins' => [$employee, $payslip],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::num('month', 'Month', 'payrolls.month'),
                        Spec::num('year', 'Year', 'payrolls.year'),
                        Spec::money('net_salary', 'Net paid', 'payrolls.net_salary'),
                        Spec::col('generated', 'Payslip on file', "case when payslips.id is null then 'No' else 'Yes' end"),
                        Spec::datetime('generated_at', 'Generated', 'payslips.generated_at'),
                    ],
                    'filters' => [
                        Spec::numberFilter('year', 'Year', 'payrolls.year'),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Payslips due', 'count(*)'),
                        Spec::summary('generated', 'Generated', 'count(distinct payslips.id)'),
                    ],
                    'order' => [Spec::order('generated_at', 'desc')],
                ],

                'payslip-value-by-employee' => [
                    'label' => 'Payslip Value by Employee',
                    'description' => 'The total value of the payslips issued to each person.',
                    'base' => 'payslips',
                    'joins' => [$payroll, $employee],
                    'group_by' => ['employees.emp_id', 'employees.full_name'],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::num('payslips', 'Payslips', 'count(*)'),
                        Spec::money('net', 'Net paid', 'sum(payrolls.net_salary)'),
                        Spec::money('average', 'Average', 'round(avg(payrolls.net_salary), 2)'),
                        Spec::datetime('latest', 'Latest issued', 'max(payslips.generated_at)'),
                    ],
                    'filters' => [
                        Spec::numberFilter('year', 'Year', 'payrolls.year'),
                    ],
                    'summary' => [
                        Spec::summary('employees', 'Employees', 'count(distinct payrolls.employee_id)'),
                        Spec::summary('net', 'Net paid', 'sum(payrolls.net_salary)', 'money'),
                    ],
                    'order' => [Spec::order('net', 'desc')],
                    'chart' => Spec::chart('bar', 'name', 'net'),
                ],

                'payslip-by-month' => [
                    'label' => 'Payslips by Month',
                    'description' => 'How many payslips were issued and for how much, each month.',
                    'base' => 'payslips',
                    'joins' => [$payroll],
                    'group_by' => ['payrolls.year', 'payrolls.month'],
                    'columns' => [
                        Spec::col('period', 'Period', "concat(payrolls.month, '/', payrolls.year)", ['primary' => true]),
                        Spec::num('payslips', 'Payslips', 'count(*)'),
                        Spec::money('net', 'Net paid', 'sum(payrolls.net_salary)'),
                        Spec::money('average', 'Average', 'round(avg(payrolls.net_salary), 2)'),
                    ],
                    'summary' => [
                        Spec::summary('months', 'Months', 'count(*)'),
                        Spec::summary('net', 'Net paid', 'sum(payrolls.net_salary)', 'money'),
                    ],
                    'order' => [Spec::order('period', 'desc')],
                    'chart' => Spec::chart('bar', 'period', 'net'),
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function payrollPeriods(): array
    {
        return [
            'label' => 'Payroll Periods',
            'category' => 'Payroll',
            'permission' => 'payroll-period.view',
            'reports' => [

                'master' => [
                    'label' => 'Payroll Period Master',
                    'description' => 'The monthly periods payroll is booked against.',
                    'base' => 'payroll_periods',
                    'columns' => [
                        Spec::col('period', 'Period', "concat(payroll_periods.month, '/', payroll_periods.year)", ['primary' => true]),
                        Spec::num('month', 'Month', 'payroll_periods.month'),
                        Spec::num('year', 'Year', 'payroll_periods.year'),
                        Spec::date('start_date', 'Starts', 'payroll_periods.start_date'),
                        Spec::date('end_date', 'Ends', 'payroll_periods.end_date'),
                        Spec::badge('status', 'Status', 'payroll_periods.status'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('status', 'Status', 'payroll_periods', 'status'),
                        Spec::numberFilter('year', 'Year', 'payroll_periods.year'),
                    ],
                    'summary' => [Spec::summary('total', 'Periods', 'count(*)')],
                    'order' => [Spec::order('year', 'desc'), Spec::order('month', 'desc')],
                ],

                'period-cost' => [
                    'label' => 'Cost per Payroll Period',
                    'description' => 'What each period cost, and how many people it covered.',
                    'base' => 'payroll_periods',
                    'joins' => [Spec::join('payrolls', 'payrolls.payroll_period_id', 'payroll_periods.id')],
                    'group_by' => ['payroll_periods.month', 'payroll_periods.year'],
                    'columns' => [
                        Spec::col('period', 'Period', "concat(payroll_periods.month, '/', payroll_periods.year)", ['primary' => true]),
                        Spec::num('employees', 'Employees', 'count(distinct payrolls.employee_id)'),
                        Spec::num('payslips', 'Payslips', 'count(payrolls.id)'),
                        Spec::money('gross', 'Gross', 'sum(payrolls.basic_salary + payrolls.hra + payrolls.other_allowance + payrolls.overtime_pay + payrolls.bonuses)'),
                        Spec::money('net', 'Net paid', 'sum(payrolls.net_salary)'),
                    ],
                    'summary' => [
                        Spec::summary('periods', 'Periods', 'count(*)'),
                        Spec::summary('net', 'Net paid', 'sum(payrolls.net_salary)', 'money'),
                    ],
                    'order' => [Spec::order('net', 'desc')],
                    'chart' => Spec::chart('bar', 'period', 'net'),
                ],

                'period-duration' => [
                    'label' => 'Payroll Period Duration',
                    'description' => 'How long each payroll period ran, and how it was closed.',
                    'base' => 'payroll_periods',
                    'columns' => [
                        Spec::col('period', 'Period', "concat(payroll_periods.month, '/', payroll_periods.year)", ['primary' => true]),
                        Spec::date('start_date', 'Starts', 'payroll_periods.start_date'),
                        Spec::date('end_date', 'Ends', 'payroll_periods.end_date'),
                        Spec::num('days', 'Days', 'datediff(payroll_periods.end_date, payroll_periods.start_date)'),
                        Spec::badge('status', 'Status', 'payroll_periods.status'),
                    ],
                    'filters' => [
                        Spec::numberFilter('year', 'Year', 'payroll_periods.year'),
                    ],
                    'summary' => [
                        Spec::summary('periods', 'Periods', 'count(*)'),
                        Spec::summary('average_days', 'Average days', 'round(avg(datediff(payroll_periods.end_date, payroll_periods.start_date)), 1)'),
                    ],
                    'order' => [Spec::order('year', 'desc'), Spec::order('month', 'desc')],
                ],

                'period-processing-status' => [
                    'label' => 'Period Processing Status',
                    'description' => 'Which periods are still open and which are done.',
                    'base' => 'payroll_periods',
                    'group_by' => ['payroll_periods.status'],
                    'columns' => [
                        Spec::badge('status', 'Status', 'payroll_periods.status', ['primary' => true]),
                        Spec::num('periods', 'Periods', 'count(*)'),
                        Spec::num('year', 'Years covered', 'count(distinct payroll_periods.year)'),
                    ],
                    'summary' => [Spec::summary('total', 'Periods', 'count(*)')],
                    'order' => [Spec::order('periods', 'desc')],
                    'chart' => Spec::chart('pie', 'status', 'periods'),
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function processPayroll(): array
    {
        $employee = Spec::join('employees', 'payrolls.employee_id', 'employees.id');
        $period = Spec::join('payroll_periods', 'payrolls.payroll_period_id', 'payroll_periods.id');

        return [
            'label' => 'Process Payroll',
            'category' => 'Payroll',
            'permission' => 'process-payroll.view',
            'reports' => [

                'processing-status' => [
                    'label' => 'Payroll Processing Status',
                    'description' => 'Where each payslip has got to in the run.',
                    'base' => 'payrolls',
                    'joins' => [$employee, $period],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::col('period', 'Period', "concat(payroll_periods.month, '/', payroll_periods.year)"),
                        Spec::badge('status', 'Status', 'payrolls.status'),
                        Spec::money('net_salary', 'Net paid', 'payrolls.net_salary'),
                        Spec::datetime('updated_at', 'Last touched', 'payrolls.updated_at'),
                    ],
                    'filters' => [
                        Spec::multiFilter('status', 'Status', 'payrolls.status'),
                        Spec::numberFilter('year', 'Year', 'payrolls.year'),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Payslips', 'count(*)'),
                        Spec::summary('approved', 'Approved', "sum(case when payrolls.status = 'Approved' then 1 else 0 end)"),
                    ],
                    'order' => [Spec::order('updated_at', 'desc')],
                ],

                'period-readiness' => [
                    'label' => 'Period Readiness',
                    'description' => 'How complete each period is - the payroll run checklist.',
                    'base' => 'payrolls',
                    'joins' => [$period],
                    'group_by' => ['payroll_periods.month', 'payroll_periods.year'],
                    'columns' => [
                        Spec::col('period', 'Period', "concat(payroll_periods.month, '/', payroll_periods.year)", ['primary' => true]),
                        Spec::num('employees', 'Employees', 'count(distinct payrolls.employee_id)'),
                        Spec::num('processed', 'Processed', 'count(payrolls.id)'),
                        Spec::num('approved', 'Approved', "sum(case when payrolls.status = 'Approved' then 1 else 0 end)"),
                        Spec::money('net', 'Net payable', 'sum(payrolls.net_salary)'),
                    ],
                    'summary' => [
                        Spec::summary('periods', 'Periods', 'count(*)'),
                        Spec::summary('net', 'Net payable', 'sum(payrolls.net_salary)', 'money'),
                    ],
                    'order' => [Spec::order('net', 'desc')],
                    'chart' => Spec::chart('bar', 'period', 'net'),
                ],

                'unprocessed-employees' => [
                    'label' => 'Employees Without Processed Pay',
                    'description' => 'The people missing from the payroll run entirely.',
                    'base' => 'employees',
                    'joins' => [
                        Spec::join('departments', 'employees.department_id', 'departments.id'),
                        Spec::join('payrolls', 'payrolls.employee_id', 'employees.id'),
                    ],
                    'constraints' => [
                        ['column' => 'payrolls.id', 'operator' => '=', 'value' => null],
                    ],
                    'columns' => [
                        Spec::col('emp_id', 'Emp ID', 'employees.emp_id'),
                        Spec::col('name', 'Employee', 'employees.full_name', ['primary' => true]),
                        Spec::col('department', 'Department', 'departments.name'),
                        Spec::col('employment_type', 'Type', 'employees.employment_type'),
                        Spec::date('joining_date', 'Joined', 'employees.joining_date'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['employees.full_name', 'employees.emp_id']),
                    ],
                    'summary' => [Spec::summary('total', 'Employees', 'count(*)')],
                    'order' => [Spec::order('name')],
                ],

                'payroll-cost-summary' => [
                    'label' => 'Payroll Cost Summary',
                    'description' => 'The one-page cost summary for the whole payroll run.',
                    'base' => 'payrolls',
                    'joins' => [$period],
                    'group_by' => ['payroll_periods.month', 'payroll_periods.year'],
                    'columns' => [
                        Spec::col('period', 'Period', "concat(payroll_periods.month, '/', payroll_periods.year)", ['primary' => true]),
                        Spec::num('headcount', 'Headcount', 'count(distinct payrolls.employee_id)'),
                        Spec::money('basic', 'Basic', 'sum(payrolls.basic_salary)'),
                        Spec::money('allowances', 'Allowances', 'sum(payrolls.hra + payrolls.conveyance + payrolls.medical_allowance + payrolls.special_allowance + payrolls.other_allowance)'),
                        Spec::money('statutory', 'Statutory', 'sum(payrolls.pf + payrolls.pt + payrolls.esic + payrolls.tds)'),
                        Spec::money('net', 'Net payable', 'sum(payrolls.net_salary)'),
                    ],
                    'filters' => [
                        Spec::numberFilter('year', 'Year', 'payrolls.year'),
                    ],
                    'summary' => [
                        Spec::summary('net', 'Net payable', 'sum(payrolls.net_salary)', 'money'),
                    ],
                    'order' => [Spec::order('period', 'desc')],
                    'chart' => Spec::chart('bar', 'period', 'net'),
                ],
            ],
        ];
    }
}
