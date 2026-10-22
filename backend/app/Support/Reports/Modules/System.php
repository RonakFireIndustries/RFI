<?php

namespace App\Support\Reports\Modules;

use App\Support\Reports\Spec;

/**
 * System reports.
 *
 * The machinery behind the application: the report library itself, who is
 * allowed to see what, and the audit trail that records what happened.
 */
class System
{
    public static function modules(): array
    {
        return [
            'reports' => self::reports(),
            'access-control' => self::accessControl(),
            'system' => self::system(),
        ];
    }

    /* ------------------------------------------------------------------ */
    /* The report library                                                  */
    /* ------------------------------------------------------------------ */

    private static function reports(): array
    {
        return [
            'label' => 'Report Library',
            'category' => 'System',
            'permission' => 'reports.view',
            'reports' => [

                'library' => [
                    'label' => 'Report Library',
                    'description' => 'Every report the application knows about.',
                    'base' => 'reports',
                    'joins' => [
                        Spec::join('report_categories', 'reports.category_id', 'report_categories.id', 'left', 'category'),
                        Spec::join('users', 'reports.created_by', 'users.id', 'left', 'author'),
                    ],
                    'columns' => [
                        Spec::col('name', 'Report', 'reports.name', ['primary' => true]),
                        Spec::col('category', 'Category', 'category.name'),
                        Spec::col('route', 'Route', 'reports.route'),
                        Spec::col('api_endpoint', 'Endpoint', 'reports.api_endpoint'),
                        Spec::badge('status', 'Status', 'reports.status'),
                        Spec::datetime('last_generated', 'Last generated', 'reports.last_generated_at'),
                        Spec::col('author', 'Created by', 'author.name'),
                    ],
                    'filters' => [
                        Spec::sourceMultiFilter('status', 'Status', 'reports', 'status', 'reports.status'),
                        Spec::sourceFilter('category_id', 'Category', 'report_categories', 'id', 'reports.category_id'),
                        Spec::textFilter('q', 'Search', ['reports.name', 'reports.slug', 'reports.description']),
                    ],
                    'summary' => [
                        Spec::summary('reports', 'Reports', 'count(*)'),
                        Spec::summary('categories', 'Categories', 'count(distinct report_categories.id)'),
                    ],
                    'order' => [Spec::order('name')],
                ],

                'by-category' => [
                    'label' => 'Reports by Category',
                    'description' => 'How the report library is organised.',
                    'base' => 'reports',
                    'joins' => [Spec::join('report_categories', 'reports.category_id', 'report_categories.id', 'left', 'category')],
                    'group_by' => ['category.id', 'category.name'],
                    'columns' => [
                        Spec::col('category', 'Category', 'category.name', ['primary' => true]),
                        Spec::num('reports', 'Reports', 'count(*)'),
                    ],
                    'summary' => [
                        Spec::summary('categories', 'Categories', 'count(distinct category.id)'),
                        Spec::summary('reports', 'Reports', 'count(*)'),
                    ],
                    'order' => [Spec::order('reports', 'desc')],
                    'chart' => Spec::chart('bar', 'category', 'reports'),
                ],

                'by-status' => [
                    'label' => 'Reports by Status',
                    'description' => 'Which reports are switched on.',
                    'base' => 'reports',
                    'group_by' => ['reports.status'],
                    'columns' => [
                        Spec::badge('status', 'Status', 'reports.status', ['primary' => true]),
                        Spec::num('reports', 'Reports', 'count(*)'),
                    ],
                    'summary' => [Spec::summary('reports', 'Reports', 'count(*)')],
                    'order' => [Spec::order('reports', 'desc')],
                    'chart' => Spec::chart('pie', 'status', 'reports'),
                ],

                'generations' => [
                    'label' => 'Report Generations',
                    'description' => 'Every file the application has produced from a report.',
                    'base' => 'report_generations',
                    'joins' => [
                        Spec::join('reports', 'report_generations.report_id', 'reports.id', 'left', 'report'),
                        Spec::join('users', 'report_generations.generated_by', 'users.id', 'left', 'generator'),
                    ],
                    'columns' => [
                        Spec::datetime('generated_at', 'Generated on', 'report_generations.generated_at', ['primary' => true]),
                        Spec::col('report', 'Report', 'report.name'),
                        Spec::col('file_name', 'File', 'report_generations.file_name'),
                        Spec::col('file_type', 'Type', 'report_generations.file_type'),
                        Spec::num('file_size', 'Size (KB)', 'round(report_generations.file_size / 1024, 2)'),
                        Spec::badge('status', 'Status', 'report_generations.status'),
                        Spec::col('generated_by', 'Generated by', 'generator.name'),
                        Spec::col('error', 'Error', 'report_generations.error_message'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'report_generations.generated_at', '>='),
                        Spec::dateFilter('to', 'To', 'report_generations.generated_at', '<='),
                        Spec::sourceMultiFilter('status', 'Status', 'report_generations', 'status', 'report_generations.status'),
                        Spec::textFilter('q', 'Search', ['report.name', 'report_generations.file_name']),
                    ],
                    'summary' => [
                        Spec::summary('generations', 'Generations', 'count(*)'),
                        Spec::summary('failed', 'Failed', "sum(case when ifnull(report_generations.status, '') = 'failed' then 1 else 0 end)"),
                    ],
                    'order' => [Spec::order('generated_at', 'desc')],
                ],

                'generation-status' => [
                    'label' => 'Generations by Status',
                    'description' => 'How the report runs are going.',
                    'base' => 'report_generations',
                    'group_by' => ['report_generations.status'],
                    'columns' => [
                        Spec::badge('status', 'Status', 'report_generations.status', ['primary' => true]),
                        Spec::num('generations', 'Generations', 'count(*)'),
                    ],
                    'summary' => [Spec::summary('generations', 'Generations', 'count(*)')],
                    'order' => [Spec::order('generations', 'desc')],
                    'chart' => Spec::chart('pie', 'status', 'generations'),
                ],

                'usage' => [
                    'label' => 'Most Used Reports',
                    'description' => 'Which reports the company actually asks for.',
                    'base' => 'report_generations',
                    'joins' => [Spec::join('reports', 'report_generations.report_id', 'reports.id', 'left', 'report')],
                    'group_by' => ['report.id', 'report.name'],
                    'columns' => [
                        Spec::col('report', 'Report', 'report.name', ['primary' => true]),
                        Spec::num('generations', 'Generations', 'count(*)'),
                        Spec::datetime('last_generated', 'Last generated', 'max(report_generations.generated_at)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'report_generations.generated_at', '>='),
                        Spec::dateFilter('to', 'To', 'report_generations.generated_at', '<='),
                    ],
                    'summary' => [
                        Spec::summary('reports', 'Reports', 'count(distinct report.id)'),
                        Spec::summary('generations', 'Generations', 'count(*)'),
                    ],
                    'order' => [Spec::order('generations', 'desc')],
                    'chart' => Spec::chart('bar', 'report', 'generations'),
                ],

                'schedules' => [
                    'label' => 'Report Schedules',
                    'description' => 'The reports that run on their own.',
                    'base' => 'report_schedules',
                    'joins' => [
                        Spec::join('reports', 'report_schedules.report_id', 'reports.id', 'left', 'report'),
                        Spec::join('users', 'report_schedules.created_by', 'users.id', 'left', 'owner'),
                    ],
                    'columns' => [
                        Spec::col('name', 'Schedule', 'report_schedules.name', ['primary' => true]),
                        Spec::col('report', 'Report', 'report.name'),
                        Spec::col('frequency', 'Frequency', 'report_schedules.frequency'),
                        Spec::col('format', 'Format', 'report_schedules.format'),
                        Spec::datetime('next_run', 'Next run', 'report_schedules.next_run_at'),
                        Spec::datetime('last_run', 'Last run', 'report_schedules.last_run_at'),
                        Spec::badge('status', 'Status', 'report_schedules.status'),
                        Spec::col('owner', 'Owner', 'owner.name'),
                    ],
                    'filters' => [
                        Spec::sourceMultiFilter('status', 'Status', 'report_schedules', 'status', 'report_schedules.status'),
                        Spec::textFilter('q', 'Search', ['report_schedules.name', 'report.name']),
                    ],
                    'summary' => [
                        Spec::summary('schedules', 'Schedules', 'count(*)'),
                    ],
                    'order' => [Spec::order('next_run')],
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Access control                                                      */
    /* ------------------------------------------------------------------ */

    private static function accessControl(): array
    {
        $userType = 'App\Models\User';

        return [
            'label' => 'Access Control',
            'category' => 'System',
            // Who may see what. Restricted to the Access Control screen's own
            // permission: the register lists every account and its grants.
            'permission' => 'access-control.manage',
            'reports' => [

                'users' => [
                    'label' => 'User Register',
                    'description' => 'Every account, and the roles it carries.',
                    'base' => 'users',
                    'joins' => [
                        Spec::join('model_has_roles', 'model_has_roles.model_id', 'users.id', 'left', 'user_role'),
                        Spec::join('roles', 'user_role.role_id', 'roles.id', 'left', 'role'),
                    ],
                    'constraints' => [
                        ['column' => 'model_has_roles.model_type', 'operator' => '=', 'value' => $userType],
                    ],
                    'group_by' => ['users.id', 'users.name', 'users.email'],
                    'columns' => [
                        Spec::col('name', 'User', 'users.name', ['primary' => true]),
                        Spec::col('email', 'Email', 'users.email'),
                        Spec::col('dashboard', 'Dashboard', 'users.dashboard_type'),
                        Spec::col('roles', 'Roles', "coalesce(group_concat(role.name separator ', '), '')"),
                        Spec::badge('super_admin', 'Super admin', 'users.is_super_admin'),
                        Spec::datetime('created', 'Created on', 'users.created_at'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('dashboard_type', 'Dashboard', 'users', 'dashboard_type', 'users.dashboard_type'),
                        Spec::textFilter('q', 'Search', ['users.name', 'users.email']),
                    ],
                    'summary' => [
                        Spec::summary('users', 'Users', 'count(distinct users.id)'),
                        Spec::summary('admins', 'Super admins', 'sum(case when users.is_super_admin = 1 then 1 else 0 end)'),
                    ],
                    'order' => [Spec::order('name')],
                ],

                'users-by-role' => [
                    'label' => 'Users by Role',
                    'description' => 'How many people sit in each role.',
                    'base' => 'users',
                    'joins' => [
                        Spec::join('model_has_roles', 'model_has_roles.model_id', 'users.id', 'left', 'user_role'),
                        Spec::join('roles', 'user_role.role_id', 'roles.id', 'left', 'role'),
                    ],
                    'constraints' => [
                        ['column' => 'model_has_roles.model_type', 'operator' => '=', 'value' => $userType],
                    ],
                    'group_by' => ['role.id', 'role.name'],
                    'columns' => [
                        Spec::col('role', 'Role', 'role.name', ['primary' => true]),
                        Spec::num('users', 'Users', 'count(distinct users.id)'),
                    ],
                    'summary' => [
                        Spec::summary('roles', 'Roles', 'count(distinct role.id)'),
                        Spec::summary('users', 'Users', 'count(distinct users.id)'),
                    ],
                    'order' => [Spec::order('users', 'desc')],
                    'chart' => Spec::chart('bar', 'role', 'users'),
                ],

                'users-by-dashboard' => [
                    'label' => 'Users by Dashboard',
                    'description' => 'Which dashboard each person lands on.',
                    'base' => 'users',
                    'group_by' => ['users.dashboard_type'],
                    'columns' => [
                        Spec::col('dashboard', 'Dashboard', 'users.dashboard_type', ['primary' => true]),
                        Spec::num('users', 'Users', 'count(*)'),
                        Spec::num('admins', 'Super admins', 'sum(case when users.is_super_admin = 1 then 1 else 0 end)'),
                    ],
                    'summary' => [
                        Spec::summary('dashboards', 'Dashboards', 'count(distinct users.dashboard_type)'),
                        Spec::summary('users', 'Users', 'count(*)'),
                    ],
                    'order' => [Spec::order('users', 'desc')],
                    'chart' => Spec::chart('pie', 'dashboard', 'users'),
                ],

                'admins' => [
                    'label' => 'Super Administrators',
                    'description' => 'The accounts that bypass every permission check.',
                    'base' => 'users',
                    'joins' => [
                        Spec::join('model_has_roles', 'model_has_roles.model_id', 'users.id', 'left', 'user_role'),
                        Spec::join('roles', 'user_role.role_id', 'roles.id', 'left', 'role'),
                    ],
                    'constraints' => [
                        ['column' => 'users.is_super_admin', 'operator' => '=', 'value' => 1],
                    ],
                    'columns' => [
                        Spec::col('name', 'User', 'users.name', ['primary' => true]),
                        Spec::col('email', 'Email', 'users.email'),
                        Spec::col('roles', 'Roles', "coalesce(group_concat(role.name separator ', '), '')"),
                        Spec::datetime('created', 'Created on', 'users.created_at'),
                    ],
                    'summary' => [Spec::summary('admins', 'Super admins', 'count(distinct users.id)')],
                    'order' => [Spec::order('name')],
                ],

                'roles' => [
                    'label' => 'Role Register',
                    'description' => 'Every role, and how much it can reach.',
                    'base' => 'roles',
                    'joins' => [
                        Spec::join('role_has_permissions', 'role_has_permissions.role_id', 'roles.id', 'left', 'rp'),
                    ],
                    'group_by' => ['roles.id', 'roles.name'],
                    'columns' => [
                        Spec::col('name', 'Role', 'roles.name', ['primary' => true]),
                        Spec::col('guard', 'Guard', 'roles.guard_name'),
                        Spec::num('permissions', 'Permissions', 'count(distinct role_has_permissions.permission_id)'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['roles.name']),
                    ],
                    'summary' => [
                        Spec::summary('roles', 'Roles', 'count(distinct roles.id)'),
                        Spec::summary('permissions', 'Permission grants', 'count(role_has_permissions.permission_id)'),
                    ],
                    'order' => [Spec::order('name')],
                    'chart' => Spec::chart('bar', 'name', 'permissions'),
                ],

                'role-permissions' => [
                    'label' => 'Permissions by Role',
                    'description' => 'How much each role is allowed to do.',
                    'base' => 'role_has_permissions',
                    'joins' => [
                        Spec::join('roles', 'role_has_permissions.role_id', 'roles.id', 'left', 'role'),
                        Spec::join('permissions', 'role_has_permissions.permission_id', 'permissions.id', 'left', 'permission'),
                    ],
                    'group_by' => ['role.id', 'role.name'],
                    'columns' => [
                        Spec::col('role', 'Role', 'role.name', ['primary' => true]),
                        Spec::num('permissions', 'Permissions', 'count(distinct permissions.id)'),
                        Spec::num('modules', 'Modules', "count(distinct substring_index(permissions.name, '.', 1))"),
                    ],
                    'summary' => [
                        Spec::summary('roles', 'Roles', 'count(distinct roles.id)'),
                        Spec::summary('permissions', 'Grants', 'count(*)'),
                    ],
                    'order' => [Spec::order('permissions', 'desc')],
                    'chart' => Spec::chart('bar', 'role', 'permissions'),
                ],

                'permissions-by-module' => [
                    'label' => 'Permissions by Module',
                    'description' => 'The shape of the permission catalogue.',
                    'base' => 'permissions',
                    'group_by' => ["substring_index(permissions.name, '.', 1)"],
                    'columns' => [
                        Spec::col('module', 'Module', "substring_index(permissions.name, '.', 1)", ['primary' => true]),
                        Spec::num('permissions', 'Permissions', 'count(*)'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['permissions.name']),
                    ],
                    'summary' => [
                        Spec::summary('modules', 'Modules', "count(distinct substring_index(permissions.name, '.', 1))"),
                        Spec::summary('permissions', 'Permissions', 'count(*)'),
                    ],
                    'order' => [Spec::order('permissions', 'desc')],
                    'chart' => Spec::chart('bar', 'module', 'permissions'),
                ],

                'direct-grants' => [
                    'label' => 'Direct Permission Grants',
                    'description' => 'The permissions handed to a person outside their role.',
                    'base' => 'model_has_permissions',
                    'joins' => [
                        Spec::join('users', 'model_has_permissions.model_id', 'users.id', 'left', 'user'),
                        Spec::join('permissions', 'model_has_permissions.permission_id', 'permissions.id', 'left', 'permission'),
                    ],
                    'constraints' => [
                        ['column' => 'model_has_permissions.model_type', 'operator' => '=', 'value' => $userType],
                    ],
                    'columns' => [
                        Spec::col('user', 'User', 'users.name', ['primary' => true]),
                        Spec::col('email', 'Email', 'users.email'),
                        Spec::col('permission', 'Permission', 'permissions.name'),
                        Spec::col('guard', 'Guard', 'permissions.guard_name'),
                    ],
                    'filters' => [
                        Spec::textFilter('q', 'Search', ['users.name', 'users.email', 'permissions.name']),
                    ],
                    'summary' => [
                        Spec::summary('grants', 'Grants', 'count(*)'),
                        Spec::summary('users', 'Users', 'count(distinct users.id)'),
                    ],
                    'order' => [Spec::order('user')],
                ],

                'role-templates' => [
                    'label' => 'Role Templates',
                    'description' => 'The saved role blueprints the company starts from.',
                    'base' => 'role_templates',
                    'columns' => [
                        Spec::col('name', 'Template', 'role_templates.name', ['primary' => true]),
                        Spec::col('category', 'Category', 'role_templates.category'),
                        Spec::col('description', 'Description', 'role_templates.description'),
                        Spec::badge('active', 'Active', 'role_templates.is_active'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('category', 'Category', 'role_templates', 'category', 'role_templates.category'),
                        Spec::textFilter('q', 'Search', ['role_templates.name', 'role_templates.description']),
                    ],
                    'summary' => [
                        Spec::summary('templates', 'Templates', 'count(*)'),
                        Spec::summary('active', 'Active', 'sum(case when role_templates.is_active = 1 then 1 else 0 end)'),
                    ],
                    'order' => [Spec::order('name')],
                ],
            ],
        ];
    }

    /* ------------------------------------------------------------------ */
    /* System activity                                                     */
    /* ------------------------------------------------------------------ */

    private static function system(): array
    {
        return [
            'label' => 'System',
            'category' => 'System',
            // The audit trail and the installation settings. Gated on the same
            // permission the Access Control screen uses: these rows are for an
            // administrator to read, and nobody else.
            'permission' => 'access-control.manage',
            'reports' => [

                'activity-log' => [
                    'label' => 'Activity Log',
                    'description' => 'Everything that has been changed, and by whom.',
                    'base' => 'activity_logs',
                    'joins' => [Spec::join('users', 'activity_logs.user_id', 'users.id', 'left', 'user')],
                    'columns' => [
                        Spec::datetime('happened_at', 'When', 'activity_logs.created_at', ['primary' => true]),
                        Spec::col('user', 'Who', 'user.name'),
                        Spec::col('action', 'Action', 'activity_logs.action'),
                        Spec::col('entity', 'Entity', 'activity_logs.loggable_type'),
                        Spec::num('entity_id', 'Record', 'activity_logs.loggable_id'),
                        Spec::col('description', 'Description', 'activity_logs.description'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'activity_logs.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'activity_logs.created_at', '<='),
                        Spec::sourceFilter('action', 'Action', 'activity_logs', 'action', 'activity_logs.action'),
                        Spec::textFilter('q', 'Search', ['activity_logs.description', 'users.name']),
                    ],
                    'summary' => [Spec::summary('entries', 'Entries', 'count(*)')],
                    'order' => [Spec::order('happened_at', 'desc')],
                ],

                'activity-by-user' => [
                    'label' => 'Activity by User',
                    'description' => 'Who is changing the most.',
                    'base' => 'activity_logs',
                    'joins' => [Spec::join('users', 'activity_logs.user_id', 'users.id', 'left', 'user')],
                    'group_by' => ['user.id', 'user.name'],
                    'columns' => [
                        Spec::col('user', 'User', 'user.name', ['primary' => true]),
                        Spec::num('entries', 'Changes', 'count(*)'),
                        Spec::num('creates', 'Created', "sum(case when activity_logs.action = 'created' then 1 else 0 end)"),
                        Spec::num('updates', 'Updated', "sum(case when activity_logs.action = 'updated' then 1 else 0 end)"),
                        Spec::num('deletes', 'Deleted', "sum(case when activity_logs.action = 'deleted' then 1 else 0 end)"),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'activity_logs.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'activity_logs.created_at', '<='),
                    ],
                    'summary' => [
                        Spec::summary('users', 'Users', 'count(distinct user.id)'),
                        Spec::summary('entries', 'Changes', 'count(*)'),
                    ],
                    'order' => [Spec::order('entries', 'desc')],
                    'chart' => Spec::chart('bar', 'user', 'entries'),
                ],

                'activity-by-action' => [
                    'label' => 'Activity by Action',
                    'description' => 'What kind of change is being made.',
                    'base' => 'activity_logs',
                    'group_by' => ['activity_logs.action'],
                    'columns' => [
                        Spec::col('action', 'Action', 'activity_logs.action', ['primary' => true]),
                        Spec::num('entries', 'Changes', 'count(*)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'activity_logs.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'activity_logs.created_at', '<='),
                    ],
                    'summary' => [Spec::summary('entries', 'Changes', 'count(*)')],
                    'order' => [Spec::order('entries', 'desc')],
                    'chart' => Spec::chart('bar', 'action', 'entries'),
                ],

                'activity-by-entity' => [
                    'label' => 'Activity by Record Type',
                    'description' => 'Which part of the application is being changed.',
                    'base' => 'activity_logs',
                    'group_by' => ['activity_logs.loggable_type'],
                    'columns' => [
                        Spec::col('entity', 'Record type', 'activity_logs.loggable_type', ['primary' => true]),
                        Spec::num('entries', 'Changes', 'count(*)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'activity_logs.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'activity_logs.created_at', '<='),
                    ],
                    'summary' => [Spec::summary('entries', 'Changes', 'count(*)')],
                    'order' => [Spec::order('entries', 'desc')],
                    'chart' => Spec::chart('bar', 'entity', 'entries'),
                ],

                'system-exceptions' => [
                    'label' => 'System Exceptions',
                    'description' => 'The business rules that are allowed to be overridden.',
                    'base' => 'system_exceptions',
                    'columns' => [
                        Spec::col('name', 'Exception', 'system_exceptions.name', ['primary' => true]),
                        Spec::col('exception_key', 'Key', 'system_exceptions.key'),
                        Spec::col('category', 'Category', 'system_exceptions.category'),
                        Spec::col('description', 'Description', 'system_exceptions.description'),
                        Spec::badge('enabled', 'Enabled', 'system_exceptions.is_enabled'),
                        Spec::badge('approval', 'Needs approval', 'system_exceptions.requires_approval'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('category', 'Category', 'system_exceptions', 'category', 'system_exceptions.category'),
                        Spec::textFilter('q', 'Search', ['system_exceptions.name', 'system_exceptions.key']),
                    ],
                    'summary' => [
                        Spec::summary('exceptions', 'Exceptions', 'count(*)'),
                        Spec::summary('enabled', 'Enabled', 'sum(case when system_exceptions.is_enabled = 1 then 1 else 0 end)'),
                    ],
                    'order' => [Spec::order('name')],
                ],

                'tasks' => [
                    'label' => 'Task Register',
                    'description' => 'The work that has been assigned and not yet done.',
                    'base' => 'tasks',
                    'joins' => [Spec::join('employees', 'tasks.employee_id', 'employees.id', 'left', 'employee')],
                    'columns' => [
                        Spec::col('title', 'Task', 'tasks.title', ['primary' => true]),
                        Spec::col('employee', 'Assigned to', 'employee.full_name'),
                        Spec::badge('status', 'Status', 'tasks.status'),
                        Spec::date('due_date', 'Due on', 'tasks.due_date'),
                        Spec::col('description', 'Description', 'tasks.description'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'Due from', 'tasks.due_date', '>='),
                        Spec::dateFilter('to', 'Due to', 'tasks.due_date', '<='),
                        Spec::sourceMultiFilter('status', 'Status', 'tasks', 'status', 'tasks.status'),
                        Spec::textFilter('q', 'Search', ['tasks.title', 'tasks.description', 'employee.full_name']),
                    ],
                    'summary' => [Spec::summary('tasks', 'Tasks', 'count(*)')],
                    'order' => [Spec::order('due_date')],
                ],

                'tasks-by-status' => [
                    'label' => 'Tasks by Status',
                    'description' => 'How the task list is draining.',
                    'base' => 'tasks',
                    'group_by' => ['tasks.status'],
                    'columns' => [
                        Spec::badge('status', 'Status', 'tasks.status', ['primary' => true]),
                        Spec::num('tasks', 'Tasks', 'count(*)'),
                    ],
                    'summary' => [Spec::summary('tasks', 'Tasks', 'count(*)')],
                    'order' => [Spec::order('tasks', 'desc')],
                    'chart' => Spec::chart('pie', 'status', 'tasks'),
                ],

                'notifications' => [
                    'label' => 'Notification Register',
                    'description' => 'Every notification the system has pushed out.',
                    'base' => 'notifications',
                    'joins' => [Spec::join('users', 'notifications.user_id', 'users.id', 'left', 'user')],
                    'columns' => [
                        Spec::datetime('sent_at', 'Sent on', 'notifications.created_at', ['primary' => true]),
                        Spec::col('user', 'User', 'user.name'),
                        Spec::col('title', 'Title', 'notifications.title'),
                        Spec::col('type', 'Type', 'notifications.type'),
                        Spec::col('module', 'Module', 'notifications.module'),
                        Spec::badge('read', 'Read', 'notifications.is_read'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'notifications.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'notifications.created_at', '<='),
                        Spec::sourceFilter('module', 'Module', 'notifications', 'module', 'notifications.module'),
                        Spec::textFilter('q', 'Search', ['notifications.title', 'notifications.message']),
                    ],
                    'summary' => [
                        Spec::summary('notifications', 'Notifications', 'count(*)'),
                        Spec::summary('unread', 'Unread', 'sum(case when notifications.is_read = 0 then 1 else 0 end)'),
                    ],
                    'order' => [Spec::order('sent_at', 'desc')],
                ],

                'chat-channels' => [
                    'label' => 'Chat Channels',
                    'description' => 'The conversations the company runs.',
                    'base' => 'chat_channels',
                    'joins' => [
                        Spec::join('sites', 'chat_channels.site_id', 'sites.id', 'left', 'site'),
                        Spec::join('users', 'chat_channels.created_by', 'users.id', 'left', 'owner'),
                    ],
                    'columns' => [
                        Spec::col('name', 'Channel', 'chat_channels.name', ['primary' => true]),
                        Spec::col('type', 'Type', 'chat_channels.type'),
                        Spec::col('site', 'Site', 'site.name'),
                        Spec::badge('status', 'Status', 'chat_channels.status'),
                        Spec::col('owner', 'Created by', 'owner.name'),
                        Spec::col('description', 'Description', 'chat_channels.description'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('type', 'Type', 'chat_channels', 'type', 'chat_channels.type'),
                        Spec::textFilter('q', 'Search', ['chat_channels.name', 'chat_channels.description']),
                    ],
                    'summary' => [Spec::summary('channels', 'Channels', 'count(*)')],
                    'order' => [Spec::order('name')],
                ],

                'chat-messages' => [
                    'label' => 'Chat Messages',
                    'description' => 'What is being said, and where.',
                    'base' => 'chat_messages',
                    'joins' => [
                        Spec::join('chat_channels', 'chat_messages.chat_channel_id', 'chat_channels.id', 'left', 'channel'),
                        Spec::join('users', 'chat_messages.user_id', 'users.id', 'left', 'user'),
                    ],
                    'columns' => [
                        Spec::datetime('sent_at', 'Sent on', 'chat_messages.created_at', ['primary' => true]),
                        Spec::col('channel', 'Channel', 'channel.name'),
                        Spec::col('user', 'User', 'user.name'),
                        Spec::col('type', 'Type', 'chat_messages.type'),
                        Spec::col('message', 'Message', 'chat_messages.message'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'From', 'chat_messages.created_at', '>='),
                        Spec::dateFilter('to', 'To', 'chat_messages.created_at', '<='),
                        Spec::textFilter('q', 'Search', ['chat_messages.message', 'channel.name', 'user.name']),
                    ],
                    'summary' => [Spec::summary('messages', 'Messages', 'count(*)')],
                    'order' => [Spec::order('sent_at', 'desc')],
                ],

                'dashboard-widgets' => [
                    'label' => 'Dashboard Widgets',
                    'description' => 'What the dashboards are built from.',
                    'base' => 'dashboard_widgets',
                    'columns' => [
                        Spec::col('name', 'Widget', 'dashboard_widgets.name', ['primary' => true]),
                        Spec::col('widget_key', 'Key', 'dashboard_widgets.widget_key'),
                        Spec::col('type', 'Type', 'dashboard_widgets.type'),
                        Spec::col('chart_type', 'Chart', 'dashboard_widgets.chart_type'),
                        Spec::col('permission', 'Permission', 'dashboard_widgets.permission'),
                        Spec::num('sort_order', 'Order', 'dashboard_widgets.order'),
                        Spec::badge('active', 'Active', 'dashboard_widgets.is_active'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('type', 'Type', 'dashboard_widgets', 'type', 'dashboard_widgets.type'),
                        Spec::textFilter('q', 'Search', ['dashboard_widgets.name', 'dashboard_widgets.widget_key']),
                    ],
                    'summary' => [
                        Spec::summary('widgets', 'Widgets', 'count(*)'),
                        Spec::summary('active', 'Active', 'sum(case when dashboard_widgets.is_active = 1 then 1 else 0 end)'),
                    ],
                    'order' => [Spec::order('sort_order')],
                ],

                'audit-settings' => [
                    'label' => 'Audit Settings',
                    'description' => 'How much is being written down, and for how long.',
                    'base' => 'audit_settings',
                    'columns' => [
                        Spec::col('entity', 'Entity', 'audit_settings.configurable_type', ['primary' => true]),
                        Spec::num('entity_id', 'Record', 'audit_settings.configurable_id'),
                        Spec::col('verbosity', 'Verbosity', 'audit_settings.logging_verbosity'),
                        Spec::num('retention_days', 'Retention (days)', 'audit_settings.retention_days'),
                        Spec::badge('reason_required', 'Reason required', 'audit_settings.reason_required'),
                        Spec::badge('change_tracking', 'Change tracking', 'audit_settings.change_tracking_enabled'),
                        Spec::badge('snapshots', 'Snapshots', 'audit_settings.snapshot_creation_enabled'),
                    ],
                    'filters' => [
                        Spec::sourceFilter('verbosity', 'Verbosity', 'audit_settings', 'logging_verbosity', 'audit_settings.logging_verbosity'),
                    ],
                    'summary' => [
                        Spec::summary('settings', 'Settings', 'count(*)'),
                    ],
                    'order' => [Spec::order('entity')],
                ],

                'company-settings' => [
                    'label' => 'Company Profile',
                    'description' => 'The details the company bills and banks under.',
                    'base' => 'company_settings',
                    'columns' => [
                        Spec::col('company_name', 'Company', 'company_settings.company_name', ['primary' => true]),
                        Spec::col('gst_number', 'GST number', 'company_settings.gst_number'),
                        Spec::col('vat_number', 'VAT number', 'company_settings.vat_number'),
                        Spec::col('tax_registration', 'Tax registration', 'company_settings.tax_registration_number'),
                        Spec::col('contact_email', 'Email', 'company_settings.contact_email'),
                        Spec::col('contact_phone', 'Phone', 'company_settings.contact_phone'),
                        Spec::col('website', 'Website', 'company_settings.website'),
                        Spec::col('bank_name', 'Bank', 'company_settings.bank_name'),
                        Spec::col('bank_account_name', 'Account name', 'company_settings.bank_account_name'),
                        Spec::col('bank_account_number', 'Account number', 'company_settings.bank_account_number'),
                        Spec::col('bank_ifsc', 'IFSC', 'company_settings.bank_ifsc_code'),
                        Spec::col('signatory', 'Signatory', 'company_settings.signatory_name'),
                    ],
                    'summary' => [Spec::summary('rows', 'Records', 'count(*)')],
                ],
            ],
        ];
    }
}
