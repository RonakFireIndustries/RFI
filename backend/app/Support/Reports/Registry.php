<?php

namespace App\Support\Reports;

/**
 * The report registry.
 *
 * Every report in the application is declared here, as data. The registry is
 * assembled from one class per module group so that no single file becomes
 * unreadable, and so that a module can be reasoned about on its own.
 *
 * The shape of a module:
 *
 *   'employees' => [
 *       'label'       => 'Employees',          // shown in the catalogue
 *       'category'    => 'Human Resources',    // the sidebar group it sits under
 *       'permission'  => 'employees.view',     // the read permission it needs
 *       'reports'     => [
 *           'directory' => [ ...one report definition... ],
 *       ],
 *   ],
 *
 * A report definition names a base table, optional joins and constraints, the
 * columns to select, the filters to offer, and (for grouped reports) the
 * GROUP BY. App\Services\Reports\ModuleReportService turns it into SQL.
 *
 * Anything in config/module_reports.php is merged over the top of this, so an
 * installation can add or adjust a report without touching PHP.
 */
class Registry
{
    /**
     * The module groups that make up the registry.
     *
     * The order here does not decide the catalogue order - the service sorts
     * by category - but keeping the list alphabetical makes it easy to find
     * where a new group belongs.
     *
     * @return array<string, array<string, array>>
     */
    protected static function groups(): array
    {
        return [
            'finance' => Modules\Finance::modules(),
            'human-resources' => Modules\HumanResources::modules(),
            'inventory' => Modules\Inventory::modules(),
            'organization' => Modules\Organization::modules(),
            'payroll' => Modules\Payroll::modules(),
            'sales-purchases' => Modules\SalesPurchases::modules(),
            'sales-survey' => Modules\SalesSurvey::modules(),
            'support' => Modules\Support::modules(),
            'system' => Modules\System::modules(),
        ];
    }

    /**
     * The whole registry: module key => module definition.
     *
     * @return array<string, array<string, array>>
     */
    public static function all(): array
    {
        $registry = [];

        foreach (static::groups() as $group) {
            foreach ($group as $moduleKey => $definition) {
                $registry[$moduleKey] = $definition;
            }
        }

        return $registry;
    }

    /**
     * One module definition, or null when the key is unknown.
     *
     * @return array<string, array>|null
     */
    public static function module(string $key): ?array
    {
        return static::all()[$key] ?? null;
    }

    /**
     * How many reports the registry declares, across every module.
     */
    public static function count(): int
    {
        $count = 0;

        foreach (static::all() as $module) {
            $count += count($module['reports'] ?? []);
        }

        return $count;
    }
}
