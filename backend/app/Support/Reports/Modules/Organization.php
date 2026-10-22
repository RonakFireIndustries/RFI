<?php

namespace App\Support\Reports\Modules;

use App\Support\Reports\Spec;

/**
 * Organisation reports.
 *
 * The shape of the company itself: which departments exist, what the people
 * in them are called, where the work happens, and the calendar the whole
 * organisation works to.
 */
class Organization
{
    public static function modules(): array
    {
        return [
            'departments' => self::departments(),
            'designations' => self::designations(),
            'sites' => self::sites(),
            'holidays' => self::holidays(),
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function departments(): array
    {
        return [
            'label' => 'Departments',
            'category' => 'Organization',
            'permission' => 'departments.view',
            'reports' => [

                'master' => [
                    'label' => 'Department Master',
                    'description' => 'Every department, and what it is for.',
                    'base' => 'departments',
                    'columns' => [
                        Spec::col('name', 'Department', 'departments.name', ['primary' => true]),
                        Spec::col('description', 'Description', 'departments.description'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['departments.name', 'departments.description']),
                    ],
                    'summary' => [Spec::summary('total', 'Departments', 'count(*)')],
                    'order' => [Spec::order('name')],
                ],

                'headcount' => [
                    'label' => 'Headcount by Department',
                    'description' => 'How many people each department carries.',
                    'base' => 'departments',
                    'joins' => [Spec::join('employees', 'employees.department_id', 'departments.id')],
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
                        Spec::summary('departments', 'Departments', 'count(distinct departments.id)'),
                        Spec::summary('headcount', 'Employees', 'count(distinct employees.id)'),
                    ],
                    'order' => [Spec::order('headcount', 'desc')],
                    'chart' => Spec::chart('bar', 'department', 'headcount'),
                ],

                'headcount-by-type' => [
                    'label' => 'Headcount by Department and Type',
                    'description' => 'The contract mix inside each department.',
                    'base' => 'employees',
                    'joins' => [Spec::join('departments', 'employees.department_id', 'departments.id')],
                    'group_by' => ['departments.name', 'employees.employment_type'],
                    'columns' => [
                        Spec::col('department', 'Department', 'departments.name', ['primary' => true]),
                        Spec::col('employment_type', 'Type', 'employees.employment_type'),
                        Spec::num('headcount', 'Employees', 'count(*)'),
                    ],
                    'summary' => [
                        Spec::summary('headcount', 'Employees', 'count(*)'),
                    ],
                    'order' => [Spec::order('department'), Spec::order('headcount', 'desc')],
                ],

                'department-spend' => [
                    'label' => 'Payroll Cost by Department',
                    'description' => 'What each department costs to staff.',
                    'base' => 'employees',
                    'joins' => [
                        Spec::join('departments', 'employees.department_id', 'departments.id'),
                        Spec::join('payrolls', 'payrolls.employee_id', 'employees.id'),
                    ],
                    'group_by' => ['departments.name'],
                    'columns' => [
                        Spec::col('department', 'Department', 'departments.name', ['primary' => true]),
                        Spec::num('employees', 'Employees', 'count(distinct employees.id)'),
                        Spec::num('payslips', 'Payslips', 'count(payrolls.id)'),
                        Spec::money('net', 'Net paid', 'sum(payrolls.net_salary)'),
                        Spec::money('average_net', 'Average net', 'round(avg(payrolls.net_salary), 2)'),
                    ],
                    'filters' => [
                        Spec::numberFilter('year', 'Year', 'payrolls.year'),
                    ],
                    'summary' => [
                        Spec::summary('net', 'Net paid', 'sum(payrolls.net_salary)', 'money'),
                    ],
                    'order' => [Spec::order('net', 'desc')],
                    'chart' => Spec::chart('bar', 'department', 'net'),
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function designations(): array
    {
        return [
            'label' => 'Designations',
            'category' => 'Organization',
            'permission' => 'designations.view',
            'reports' => [

                'master' => [
                    'label' => 'Designation Master',
                    'description' => 'Every job title the company uses, and the department it sits in.',
                    'base' => 'designations',
                    'joins' => [Spec::join('departments', 'designations.department_id', 'departments.id')],
                    'columns' => [
                        Spec::col('name', 'Designation', 'designations.name', ['primary' => true]),
                        Spec::col('department', 'Department', 'departments.name'),
                        Spec::col('description', 'Description', 'designations.description'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['designations.name', 'designations.description']),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Designations', 'count(*)'),
                        Spec::summary('departments', 'Departments', 'count(distinct departments.id)'),
                    ],
                    'order' => [Spec::order('name')],
                ],

                'headcount' => [
                    'label' => 'Headcount by Designation',
                    'description' => 'How many people hold each title.',
                    'base' => 'designations',
                    'joins' => [Spec::join('employees', 'employees.designation_id', 'designations.id')],
                    'group_by' => ['designations.name'],
                    'columns' => [
                        Spec::col('designation', 'Designation', 'designations.name', ['primary' => true]),
                        Spec::num('headcount', 'Employees', 'count(distinct employees.id)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'Joined from', 'employees.joining_date', '>='),
                        Spec::dateFilter('to', 'Joined to', 'employees.joining_date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('designations', 'Designations', 'count(distinct designations.id)'),
                        Spec::summary('headcount', 'Employees', 'count(distinct employees.id)'),
                    ],
                    'order' => [Spec::order('headcount', 'desc')],
                    'chart' => Spec::chart('bar', 'designation', 'headcount'),
                ],

                'designation-by-department' => [
                    'label' => 'Designations by Department',
                    'description' => 'The job titles each department actually uses.',
                    'base' => 'designations',
                    'joins' => [Spec::join('departments', 'designations.department_id', 'departments.id')],
                    'group_by' => ['departments.name'],
                    'columns' => [
                        Spec::col('department', 'Department', 'departments.name', ['primary' => true]),
                        Spec::num('designations', 'Designations', 'count(distinct designations.id)'),
                    ],
                    'summary' => [
                        Spec::summary('departments', 'Departments', 'count(distinct departments.id)'),
                    ],
                    'order' => [Spec::order('designations', 'desc')],
                    'chart' => Spec::chart('bar', 'department', 'designations'),
                ],

                'designation-cost' => [
                    'label' => 'Average Pay by Designation',
                    'description' => 'What each title is paid on average.',
                    'base' => 'payrolls',
                    'joins' => [
                        Spec::join('employees', 'payrolls.employee_id', 'employees.id'),
                        Spec::join('designations', 'employees.designation_id', 'designations.id'),
                    ],
                    'group_by' => ['designations.name'],
                    'columns' => [
                        Spec::col('designation', 'Designation', 'designations.name', ['primary' => true]),
                        Spec::num('employees', 'Employees', 'count(distinct payrolls.employee_id)'),
                        Spec::money('average_net', 'Average net', 'round(avg(payrolls.net_salary), 2)'),
                        Spec::money('highest', 'Highest', 'max(payrolls.net_salary)'),
                        Spec::money('lowest', 'Lowest', 'min(payrolls.net_salary)'),
                    ],
                    'filters' => [
                        Spec::numberFilter('year', 'Year', 'payrolls.year'),
                    ],
                    'summary' => [
                        Spec::summary('designations', 'Designations', 'count(distinct designations.id)'),
                        Spec::summary('average_net', 'Average net', 'round(avg(payrolls.net_salary), 2)', 'money'),
                    ],
                    'order' => [Spec::order('average_net', 'desc')],
                    'chart' => Spec::chart('bar', 'designation', 'average_net'),
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function sites(): array
    {
        $manager = Spec::join('employees', 'sites.site_manager_id', 'manager.id', 'left', 'manager');

        return [
            'label' => 'Sites',
            'category' => 'Organization',
            'permission' => 'sites.view',
            'reports' => [

                'master' => [
                    'label' => 'Site Master',
                    'description' => 'Every site the company works at.',
                    'base' => 'sites',
                    'columns' => [
                        Spec::col('name', 'Site', 'sites.name', ['primary' => true]),
                        Spec::col('code', 'Code', 'sites.code'),
                        Spec::col('client_details', 'Client', 'sites.client_details'),
                        Spec::col('society_name', 'Society', 'sites.society_name'),
                        Spec::col('building_type', 'Building type', 'sites.building_type'),
                        Spec::col('city', 'City', 'sites.city'),
                        Spec::col('state', 'State', 'sites.state'),
                        Spec::badge('status', 'Status', 'sites.status'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('status', 'Status', 'sites', 'status'),
                        Spec::textFilter('q', 'Search', ['sites.name', 'sites.code', 'sites.city', 'sites.client_details']),
                    ],
                    'summary' => [
                        Spec::summary('total', 'Sites', 'count(*)'),
                    ],
                    'order' => [Spec::order('name')],
                ],

                'contact-directory' => [
                    'label' => 'Site Contact Directory',
                    'description' => 'Who to call at each site.',
                    'base' => 'sites',
                    'columns' => [
                        Spec::col('site', 'Site', 'sites.name', ['primary' => true]),
                        Spec::col('contact_person', 'Contact', 'sites.contact_person'),
                        Spec::col('phone', 'Phone', 'sites.phone'),
                        Spec::col('email', 'Email', 'sites.email'),
                        Spec::col('address', 'Address', 'sites.address'),
                        Spec::col('city', 'City', 'sites.city'),
                        Spec::col('pincode', 'Pincode', 'sites.pincode'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['sites.name', 'sites.contact_person', 'sites.phone']),
                    ],
                    'summary' => [Spec::summary('total', 'Sites', 'count(*)')],
                    'order' => [Spec::order('site')],
                ],

                'site-manager' => [
                    'label' => 'Site Managers',
                    'description' => 'The site and the person who runs it.',
                    'base' => 'sites',
                    'joins' => [$manager],
                    'columns' => [
                        Spec::col('site', 'Site', 'sites.name', ['primary' => true]),
                        Spec::col('manager', 'Site manager', 'manager.full_name'),
                        Spec::col('emp_id', 'Emp ID', 'manager.emp_id'),
                        Spec::col('contact_person', 'Contact', 'sites.contact_person'),
                        Spec::col('phone', 'Phone', 'sites.phone'),
                        Spec::badge('status', 'Status', 'sites.status'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['sites.name', 'manager.full_name']),
                    ],
                    'summary' => [
                        Spec::summary('sites', 'Sites', 'count(*)'),
                        Spec::summary('managed', 'With a manager', 'sum(case when sites.site_manager_id is not null then 1 else 0 end)'),
                    ],
                    'order' => [Spec::order('site')],
                ],

                'site-profile' => [
                    'label' => 'Site Building Profile',
                    'description' => 'The physical shape of each site - floors, flats, lifts.',
                    'base' => 'sites',
                    'columns' => [
                        Spec::col('name', 'Site', 'sites.name', ['primary' => true]),
                        Spec::num('floors', 'Floors', 'sites.no_of_floors'),
                        Spec::num('basements', 'Basements', 'sites.basement_levels'),
                        Spec::num('wings', 'Wings', 'sites.no_of_wings'),
                        Spec::num('flats', 'Flats', 'sites.no_of_flats'),
                        Spec::num('lifts', 'Lifts', 'sites.no_of_lifts'),
                        Spec::num('staircases', 'Staircases', 'sites.no_of_staircase'),
                        Spec::num('exits', 'Exits', 'sites.no_of_exits_entry'),
                        Spec::num('refuge_floors', 'Refuge floors', 'sites.refuge_floors'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['sites.name']),
                    ],
                    'summary' => [
                        Spec::summary('sites', 'Sites', 'count(*)'),
                        Spec::summary('flats', 'Total flats', 'sum(sites.no_of_flats)'),
                        Spec::summary('floors', 'Total floors', 'sum(sites.no_of_floors)'),
                    ],
                    'order' => [Spec::order('name')],
                ],

                'fire-noc-status' => [
                    'label' => 'Fire NOC & Safety Status',
                    'description' => 'The compliance picture across every site.',
                    'base' => 'sites',
                    'columns' => [
                        Spec::col('name', 'Site', 'sites.name', ['primary' => true]),
                        Spec::col('fire_noc_status', 'Fire NOC', 'sites.fire_noc_status'),
                        Spec::col('fire_safety_available', 'Fire safety', 'sites.fire_safety_available'),
                        Spec::col('fire_safety_type', 'Safety type', 'sites.fire_safety_type'),
                        Spec::col('equipment_condition', 'Equipment condition', 'sites.overall_equipment_condition'),
                        Spec::date('last_serviced', 'Last serviced', 'sites.last_serviced_date'),
                        Spec::col('last_amc_company', 'AMC company', 'sites.last_amc_company'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('fire_noc_status', 'Fire NOC', 'sites', 'fire_noc_status'),
                    ],
                    'summary' => [
                        Spec::summary('sites', 'Sites', 'count(*)'),
                        Spec::summary('serviced', 'With a service date', 'sum(case when sites.last_serviced_date is not null then 1 else 0 end)'),
                    ],
                    'order' => [Spec::order('name')],
                ],

                'site-activity' => [
                    'label' => 'Site Activity',
                    'description' => 'How busy each site has been - attendance, reports and staff.',
                    'base' => 'attendances',
                    'joins' => [Spec::join('sites', 'attendances.site_id', 'sites.id')],
                    'group_by' => ['sites.name'],
                    'columns' => [
                        Spec::col('site', 'Site', 'sites.name', ['primary' => true]),
                        Spec::num('checkins', 'Check-ins', 'count(*)'),
                        Spec::num('people', 'People', 'count(distinct attendances.employee_id)'),
                        Spec::num('hours', 'Hours worked', 'sum(attendances.working_hours)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'attendances.date', '>='),
                        Spec::dateFilter('to', 'To', 'attendances.date', '<='),
                    ],
                    'summary' => [
                        Spec::summary('sites', 'Sites', 'count(distinct sites.id)'),
                        Spec::summary('hours', 'Hours worked', 'sum(attendances.working_hours)'),
                    ],
                    'order' => [Spec::order('checkins', 'desc')],
                    'chart' => Spec::chart('bar', 'site', 'checkins'),
                ],

                'sites-by-city' => [
                    'label' => 'Sites by City',
                    'description' => 'Where the company has a presence.',
                    'base' => 'sites',
                    'group_by' => ['sites.city'],
                    'columns' => [
                        Spec::col('city', 'City', 'sites.city', ['primary' => true]),
                        Spec::num('sites', 'Sites', 'count(*)'),
                        Spec::num('flats', 'Flats', 'sum(sites.no_of_flats)'),
                        Spec::num('buildings', 'Floors', 'sum(sites.no_of_floors)'),
                    ],
                    'summary' => [
                        Spec::summary('cities', 'Cities', 'count(distinct sites.city)'),
                        Spec::summary('sites', 'Sites', 'count(*)'),
                    ],
                    'order' => [Spec::order('sites', 'desc')],
                    'chart' => Spec::chart('bar', 'city', 'sites'),
                ],

                'sites-by-status' => [
                    'label' => 'Sites by Status',
                    'description' => 'Which sites are live, and which are not.',
                    'base' => 'sites',
                    'group_by' => ['sites.status'],
                    'columns' => [
                        Spec::badge('status', 'Status', 'sites.status', ['primary' => true]),
                        Spec::num('sites', 'Sites', 'count(*)'),
                        Spec::num('flats', 'Flats', 'sum(sites.no_of_flats)'),
                    ],
                    'summary' => [Spec::summary('total', 'Sites', 'count(*)')],
                    'order' => [Spec::order('sites', 'desc')],
                    'chart' => Spec::chart('pie', 'status', 'sites'),
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function holidays(): array
    {
        return [
            'label' => 'Holiday Calendar',
            'category' => 'Organization',
            'permission' => 'employees.view',
            'reports' => [

                'master' => [
                    'label' => 'Holiday Calendar',
                    'description' => 'Every holiday on the company calendar.',
                    'base' => 'holidays',
                    'columns' => [
                        Spec::col('name', 'Holiday', 'holidays.name', ['primary' => true]),
                        Spec::date('date', 'Date', 'holidays.date'),
                        Spec::col('type', 'Type', 'holidays.type'),
                        Spec::badge('status', 'Status', 'holidays.status'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'holidays.date', '>='),
                        Spec::dateFilter('to', 'To', 'holidays.date', '<='),
                        Spec::sourceFilter('type', 'Type', 'holidays', 'type'),
                        Spec::textFilter('q', 'Search', ['holidays.name']),
                    ],
                    'summary' => [Spec::summary('total', 'Holidays', 'count(*)')],
                    'order' => [Spec::order('date')],
                ],

                'by-type' => [
                    'label' => 'Holidays by Type',
                    'description' => 'How the calendar breaks down - national, restricted, optional.',
                    'base' => 'holidays',
                    'group_by' => ['holidays.type'],
                    'columns' => [
                        Spec::col('type', 'Type', 'holidays.type', ['primary' => true]),
                        Spec::num('holidays', 'Holidays', 'count(*)'),
                        Spec::date('first', 'First', 'min(holidays.date)'),
                        Spec::date('last', 'Last', 'max(holidays.date)'),
                    ],
                    'summary' => [
                        Spec::summary('types', 'Types', 'count(distinct holidays.type)'),
                        Spec::summary('holidays', 'Holidays', 'count(*)'),
                    ],
                    'order' => [Spec::order('holidays', 'desc')],
                    'chart' => Spec::chart('pie', 'type', 'holidays'),
                ],

                'by-year' => [
                    'label' => 'Holidays by Year',
                    'description' => 'The shape of each calendar year.',
                    'base' => 'holidays',
                    'group_by' => ['year(holidays.date)'],
                    'columns' => [
                        Spec::num('year', 'Year', 'year(holidays.date)', ['primary' => true]),
                        Spec::num('holidays', 'Holidays', 'count(*)'),
                        Spec::num('restricted', 'Restricted', "sum(case when holidays.type = 'Restricted Holiday' then 1 else 0 end)"),
                        Spec::num('optional', 'Optional', "sum(case when holidays.type = 'Optional Holiday' then 1 else 0 end)"),
                    ],
                    'summary' => [
                        Spec::summary('years', 'Years', 'count(distinct year(holidays.date))'),
                        Spec::summary('holidays', 'Holidays', 'count(*)'),
                    ],
                    'order' => [Spec::order('year', 'desc')],
                    'chart' => Spec::chart('bar', 'year', 'holidays'),
                ],
            ],
        ];
    }
}
