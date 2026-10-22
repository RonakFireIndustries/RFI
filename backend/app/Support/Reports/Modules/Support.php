<?php

namespace App\Support\Reports\Modules;

use App\Support\Reports\Spec;

/**
 * Support reports.
 *
 * The issues the field teams and the office raise against the application
 * itself - what broke, how urgent it is, and how long it took to put right.
 */
class Support
{
    public static function modules(): array
    {
        return [
            'issue-reports' => self::issueReports(),
        ];
    }

    /* ------------------------------------------------------------------ */

    private static function issueReports(): array
    {
        $statuses = [
            'open' => 'Open',
            'in_progress' => 'In progress',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
        ];

        $priorities = [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'critical' => 'Critical',
        ];

        $categories = [
            'bug' => 'Bug',
            'ui' => 'User interface',
            'performance' => 'Performance',
            'data' => 'Data',
            'access' => 'Access',
            'other' => 'Other',
        ];

        return [
            'label' => 'Issue Reports',
            'category' => 'Support',
            'permission' => 'issue-reports.view',
            'reports' => [

                'master' => [
                    'label' => 'Issue Register',
                    'description' => 'Every issue that has been raised.',
                    'base' => 'issue_reports',
                    'joins' => [
                        Spec::join('users', 'issue_reports.user_id', 'users.id', 'left', 'reporter'),
                        Spec::join('users', 'issue_reports.resolved_by', 'users.id', 'left', 'resolver'),
                    ],
                    'columns' => [
                        Spec::col('title', 'Issue', 'issue_reports.title', ['primary' => true]),
                        Spec::datetime('created', 'Raised on', 'issue_reports.created_at'),
                        Spec::col('reporter', 'Raised by', 'reporter.name'),
                        Spec::col('category', 'Category', 'issue_reports.category'),
                        Spec::badge('priority', 'Priority', 'issue_reports.priority'),
                        Spec::badge('status', 'Status', 'issue_reports.status'),
                        Spec::col('resolver', 'Resolved by', 'resolver.name'),
                        Spec::datetime('resolved_at', 'Resolved on', 'issue_reports.resolved_at'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'Raised from', 'issue_reports.created_at', '>='),
                        Spec::dateFilter('to', 'Raised to', 'issue_reports.created_at', '<='),
                        Spec::multiFilter('status', 'Status', 'issue_reports.status', $statuses),
                        Spec::multiFilter('priority', 'Priority', 'issue_reports.priority', $priorities),
                        Spec::multiFilter('category', 'Category', 'issue_reports.category', $categories),
                        Spec::textFilter('q', 'Search', ['issue_reports.title', 'issue_reports.description']),
                    ],
                    'summary' => [
                        Spec::summary('issues', 'Issues', 'count(*)'),
                        Spec::summary('open', 'Still open', "sum(case when ifnull(issue_reports.status, '') <> 'resolved' and ifnull(issue_reports.status, '') <> 'closed' then 1 else 0 end)"),
                        Spec::summary('resolved', 'Resolved', "sum(case when issue_reports.status = 'resolved' or issue_reports.status = 'closed' then 1 else 0 end)"),
                    ],
                    'order' => [Spec::order('created', 'desc')],
                ],

                'open-issues' => [
                    'label' => 'Open Issues',
                    'description' => 'The issues still waiting to be put right.',
                    'base' => 'issue_reports',
                    'joins' => [Spec::join('users', 'issue_reports.user_id', 'users.id', 'left', 'reporter')],
                    'columns' => [
                        Spec::col('title', 'Issue', 'issue_reports.title', ['primary' => true]),
                        Spec::datetime('created', 'Raised on', 'issue_reports.created_at'),
                        Spec::col('reporter', 'Raised by', 'reporter.name'),
                        Spec::badge('priority', 'Priority', 'issue_reports.priority'),
                        Spec::badge('status', 'Status', 'issue_reports.status'),
                        Spec::col('page_url', 'Page', 'issue_reports.page_url'),
                    ],
                    'filters' => [
                        Spec::multiFilter('status', 'Status', 'issue_reports.status', $statuses, ['default' => 'open']),
                        Spec::multiFilter('priority', 'Priority', 'issue_reports.priority', $priorities),
                        Spec::multiFilter('category', 'Category', 'issue_reports.category', $categories),
                    ],
                    'summary' => [
                        Spec::summary('issues', 'Issues', 'count(*)'),
                    ],
                    'order' => [Spec::order('created', 'desc')],
                ],

                'by-status' => [
                    'label' => 'Issues by Status',
                    'description' => 'Where the backlog sits.',
                    'base' => 'issue_reports',
                    'group_by' => ['issue_reports.status'],
                    'columns' => [
                        Spec::badge('status', 'Status', 'issue_reports.status', ['primary' => true]),
                        Spec::num('issues', 'Issues', 'count(*)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'Raised from', 'issue_reports.created_at', '>='),
                        Spec::dateFilter('to', 'Raised to', 'issue_reports.created_at', '<='),
                    ],
                    'summary' => [
                        Spec::summary('statuses', 'Statuses', 'count(distinct issue_reports.status)'),
                        Spec::summary('issues', 'Issues', 'count(*)'),
                    ],
                    'order' => [Spec::order('issues', 'desc')],
                    'chart' => Spec::chart('pie', 'status', 'issues'),
                ],

                'by-priority' => [
                    'label' => 'Issues by Priority',
                    'description' => 'What the team is being pulled towards.',
                    'base' => 'issue_reports',
                    'group_by' => ['issue_reports.priority'],
                    'columns' => [
                        Spec::badge('priority', 'Priority', 'issue_reports.priority', ['primary' => true]),
                        Spec::num('issues', 'Issues', 'count(*)'),
                        Spec::num('open', 'Still open', "sum(case when ifnull(issue_reports.status, '') <> 'resolved' and ifnull(issue_reports.status, '') <> 'closed' then 1 else 0 end)"),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'Raised from', 'issue_reports.created_at', '>='),
                        Spec::dateFilter('to', 'Raised to', 'issue_reports.created_at', '<='),
                    ],
                    'summary' => [
                        Spec::summary('priorities', 'Priorities', 'count(distinct issue_reports.priority)'),
                        Spec::summary('issues', 'Issues', 'count(*)'),
                    ],
                    'order' => [Spec::order('issues', 'desc')],
                    'chart' => Spec::chart('bar', 'priority', 'issues'),
                ],

                'by-category' => [
                    'label' => 'Issues by Category',
                    'description' => 'Which part of the application is being complained about.',
                    'base' => 'issue_reports',
                    'group_by' => ['issue_reports.category'],
                    'columns' => [
                        Spec::col('category', 'Category', 'issue_reports.category', ['primary' => true]),
                        Spec::num('issues', 'Issues', 'count(*)'),
                        Spec::num('resolved', 'Resolved', "sum(case when issue_reports.status = 'resolved' or issue_reports.status = 'closed' then 1 else 0 end)"),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'Raised from', 'issue_reports.created_at', '>='),
                        Spec::dateFilter('to', 'Raised to', 'issue_reports.created_at', '<='),
                    ],
                    'summary' => [
                        Spec::summary('categories', 'Categories', 'count(distinct issue_reports.category)'),
                        Spec::summary('issues', 'Issues', 'count(*)'),
                    ],
                    'order' => [Spec::order('issues', 'desc')],
                    'chart' => Spec::chart('bar', 'category', 'issues'),
                ],

                'by-reporter' => [
                    'label' => 'Issues by Reporter',
                    'description' => 'Who is raising the most issues.',
                    'base' => 'issue_reports',
                    'joins' => [Spec::join('users', 'issue_reports.user_id', 'users.id', 'left', 'reporter')],
                    'group_by' => ['reporter.id', 'reporter.name'],
                    'columns' => [
                        Spec::col('reporter', 'Reporter', 'reporter.name', ['primary' => true]),
                        Spec::num('issues', 'Issues', 'count(*)'),
                        Spec::num('open', 'Still open', "sum(case when ifnull(issue_reports.status, '') <> 'resolved' and ifnull(issue_reports.status, '') <> 'closed' then 1 else 0 end)"),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'Raised from', 'issue_reports.created_at', '>='),
                        Spec::dateFilter('to', 'Raised to', 'issue_reports.created_at', '<='),
                    ],
                    'summary' => [
                        Spec::summary('reporters', 'Reporters', 'count(distinct reporter.id)'),
                        Spec::summary('issues', 'Issues', 'count(*)'),
                    ],
                    'order' => [Spec::order('issues', 'desc')],
                    'chart' => Spec::chart('bar', 'reporter', 'issues'),
                ],

                'by-resolver' => [
                    'label' => 'Issues by Resolver',
                    'description' => 'Who is putting the issues right.',
                    'base' => 'issue_reports',
                    'joins' => [Spec::join('users', 'issue_reports.resolved_by', 'users.id', 'left', 'resolver')],
                    'group_by' => ['resolver.id', 'resolver.name'],
                    'columns' => [
                        Spec::col('resolver', 'Resolver', 'resolver.name', ['primary' => true]),
                        Spec::num('resolved', 'Resolved', 'count(*)'),
                        Spec::num('average_hours', 'Average hours', 'round(avg(timestampdiff(hour, issue_reports.created_at, issue_reports.resolved_at)), 2)'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'Resolved from', 'issue_reports.resolved_at', '>='),
                        Spec::dateFilter('to', 'Resolved to', 'issue_reports.resolved_at', '<='),
                    ],
                    'summary' => [
                        Spec::summary('resolvers', 'Resolvers', 'count(distinct resolver.id)'),
                        Spec::summary('resolved', 'Resolved', 'count(*)'),
                    ],
                    'order' => [Spec::order('resolved', 'desc')],
                    'chart' => Spec::chart('bar', 'resolver', 'resolved'),
                ],

                'resolution-time' => [
                    'label' => 'Resolution Time by Category',
                    'description' => 'How long each kind of issue takes to close.',
                    'base' => 'issue_reports',
                    'group_by' => ['issue_reports.category'],
                    'columns' => [
                        Spec::col('category', 'Category', 'issue_reports.category', ['primary' => true]),
                        Spec::num('resolved', 'Resolved', 'count(issue_reports.resolved_at)'),
                        Spec::num('average_hours', 'Average hours', 'round(avg(timestampdiff(hour, issue_reports.created_at, issue_reports.resolved_at)), 2)'),
                        Spec::num('longest_hours', 'Longest hours', 'max(timestampdiff(hour, issue_reports.created_at, issue_reports.resolved_at))'),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'Raised from', 'issue_reports.created_at', '>='),
                        Spec::dateFilter('to', 'Raised to', 'issue_reports.created_at', '<='),
                    ],
                    'summary' => [
                        Spec::summary('categories', 'Categories', 'count(distinct issue_reports.category)'),
                        Spec::summary('resolved', 'Resolved', 'count(issue_reports.resolved_at)'),
                    ],
                    'order' => [Spec::order('average_hours', 'desc')],
                    'chart' => Spec::chart('bar', 'category', 'average_hours'),
                ],

                'monthly-trend' => [
                    'label' => 'Issues Raised Monthly Trend',
                    'description' => 'How the complaint rate moved month by month.',
                    'base' => 'issue_reports',
                    'group_by' => ['year(issue_reports.created_at)', 'month(issue_reports.created_at)'],
                    'columns' => [
                        Spec::col('period', 'Period', "concat(month(issue_reports.created_at), '/', year(issue_reports.created_at))", ['primary' => true]),
                        Spec::num('issues', 'Issues', 'count(*)'),
                        Spec::num('resolved', 'Resolved', "sum(case when issue_reports.status = 'resolved' or issue_reports.status = 'closed' then 1 else 0 end)"),
                    ],
                    'filters' => [
                        Spec::dateFilter('from', 'Raised from', 'issue_reports.created_at', '>='),
                        Spec::dateFilter('to', 'Raised to', 'issue_reports.created_at', '<='),
                    ],
                    'summary' => [
                        Spec::summary('months', 'Months', 'count(*)'),
                        Spec::summary('issues', 'Issues', 'count(*)'),
                    ],
                    'order' => [Spec::order('period', 'desc')],
                    'chart' => Spec::chart('line', 'period', 'issues'),
                ],

                'screenshot-capture' => [
                    'label' => 'Issue Screenshot Capture',
                    'description' => 'How much evidence came with each issue.',
                    'base' => 'issue_reports',
                    'joins' => [
                        Spec::join('users', 'issue_reports.user_id', 'users.id', 'left', 'reporter'),
                        Spec::join('issue_report_screenshots', 'issue_report_screenshots.issue_report_id', 'issue_reports.id', 'left', 'shot'),
                    ],
                    'group_by' => ['issue_reports.id', 'issue_reports.title'],
                    'columns' => [
                        Spec::col('title', 'Issue', 'issue_reports.title', ['primary' => true]),
                        Spec::col('reporter', 'Raised by', 'reporter.name'),
                        Spec::badge('priority', 'Priority', 'issue_reports.priority'),
                        Spec::num('screenshots', 'Screenshots', 'count(distinct shot.id)'),
                    ],
                    'summary' => [
                        Spec::summary('issues', 'Issues', 'count(distinct issue_reports.id)'),
                        Spec::summary('screenshots', 'Screenshots', 'count(distinct shot.id)'),
                    ],
                    'order' => [Spec::order('screenshots', 'desc')],
                ],
            ],
        ];
    }
}
