<?php

namespace App\Services\Reports;

use App\Models\User;
use App\Support\Access;
use App\Support\Reports\Registry;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * The engine behind the module report pack.
 *
 * Every report in the application is described declaratively in
 * App\Support\Reports\Registry: a base table, optional joins, a column list, a
 * filter schema and (for grouped reports) a GROUP BY. This service turns that
 * description into SQL, runs it and hands back a shape the generic frontend
 * renderer can draw without knowing anything about the module.
 *
 * Why a registry instead of one controller per report: the application has
 * around sixty modules and every one of them needs registers, summaries and
 * breakdowns. Writing them by hand produced four hundred lines of near
 * identical controllers, and every new report needed a service, a route, a
 * controller method and a bespoke Blade view. A single declarative registry
 * means a new report is data, not code.
 *
 * The registry is trusted, static PHP - it is not user input - but the
 * expressions it carries are still screened before they reach the database
 * (see assertSafeExpression) so a typo in a spec can never smuggle a second
 * statement into a report.
 *
 * Filters are the only user-controlled part of a report, and they are bound as
 * parameters. The engine also refuses any filter key the report did not
 * declare, so a caller cannot smuggle a column into the WHERE clause.
 */
class ModuleReportService
{
    /** Rows returned when a report does not declare its own limit. */
    public const DEFAULT_LIMIT = 500;

    /** Hard ceiling on rows, whatever the registry asks for. */
    public const MAX_LIMIT = 2000;

    /**
     * The order the module groups appear in the catalogue. Anything not
     * listed sorts after these, alphabetically.
     */
    public const CATEGORY_ORDER = [
        'Human Resources',
        'Payroll',
        'Finance',
        'Organization',
        'Inventory',
        'Sales & Purchases',
        'Sales & Survey',
        'Support',
        'System',
    ];

    /* ------------------------------------------------------------------ */
    /* Catalogue                                                           */
    /* ------------------------------------------------------------------ */

    /**
     * Every module report the signed-in user is allowed to open.
     *
     * The catalogue is permission filtered: a report for the warehouse is only
     * listed for someone who can read the warehouse. That way the frontend
     * can render one generic page and simply not mention the modules the
     * user has no business seeing.
     */
    public function catalog(User $user): array
    {
        $modules = [];

        foreach ($this->registry() as $moduleKey => $module) {
            $modulePermission = $module['permission'] ?? null;

            $reports = [];
            foreach ($module['reports'] ?? [] as $reportKey => $report) {
                $permission = $report['permission'] ?? $modulePermission;
                if ($permission && ! Access::can($user, $permission)) {
                    continue;
                }

                $reports[] = [
                    'key' => $reportKey,
                    'module' => $moduleKey,
                    'label' => $report['label'] ?? $this->humanise($reportKey),
                    'description' => $report['description'] ?? null,
                    'filters' => $this->filterSchema($report, resolveOptions: false),
                ];
            }

            if ($reports === []) {
                continue;
            }

            $modules[] = [
                'key' => $moduleKey,
                'label' => $module['label'] ?? $this->humanise($moduleKey),
                'category' => $module['category'] ?? 'General',
                'description' => $module['description'] ?? null,
                'report_count' => count($reports),
                'reports' => $reports,
            ];
        }

        usort($modules, function (array $a, array $b) {
            $orderA = array_search($a['category'], self::CATEGORY_ORDER, true);
            $orderB = array_search($b['category'], self::CATEGORY_ORDER, true);
            $orderA = $orderA === false ? PHP_INT_MAX : $orderA;
            $orderB = $orderB === false ? PHP_INT_MAX : $orderB;

            return [$orderA, $a['label']] <=> [$orderB, $b['label']];
        });

        $total = array_sum(array_column($modules, 'report_count'));

        return [
            'modules' => $modules,
            'module_count' => count($modules),
            'report_count' => $total,
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Running a report                                                    */
    /* ------------------------------------------------------------------ */

    /**
     * The filters one report offers, with the dropdown options filled in.
     *
     * The catalogue deliberately leaves the options out - resolving them
     * there would mean a query per filter per report - so a page that only
     * wants the filter bar for a single report asks for them here.
     *
     * @return array<int, array<string, mixed>>
     */
    public function filterSchemaFor(string $moduleKey, string $reportKey, User $user): array
    {
        $module = $this->registry()[$moduleKey] ?? null;
        if ($module === null) {
            abort(404, 'Unknown report module.');
        }

        $report = $module['reports'][$reportKey] ?? null;
        if ($report === null) {
            abort(404, 'Unknown report.');
        }

        $permission = $report['permission'] ?? $module['permission'] ?? null;
        if ($permission && ! Access::can($user, $permission)) {
            abort(403, 'You do not have permission to view this report.');
        }

        return $this->filterSchema($report, resolveOptions: true);
    }

    /**
     * Run one report and return the generic report payload.
     *
     * @param  string  $moduleKey  the registry module key
     * @param  string  $reportKey  the report key inside that module
     * @param  array   $input      raw query parameters
     */
    public function run(string $moduleKey, string $reportKey, array $input, User $user): array
    {
        $module = $this->registry()[$moduleKey] ?? null;
        if ($module === null) {
            abort(404, 'Unknown report module.');
        }

        $report = $module['reports'][$reportKey] ?? null;
        if ($report === null) {
            abort(404, 'Unknown report.');
        }

        // Every expression in the report is pointed at the right table alias
        // before anything else looks at it, so the rest of the engine never
        // has to know which tables are joined under a name.
        $report = $this->normalise($report);

        $permission = $report['permission'] ?? $module['permission'] ?? null;
        if ($permission && ! Access::can($user, $permission)) {
            abort(403, 'You do not have permission to view this report.');
        }

        $filters = $this->validateFilters($report, $input);
        $sort = $this->resolveSort($report, $input);

        $limit = $this->resolveLimit($report, $input);
        $total = $this->countRows($report, $filters);

        $rows = $this->fetchRows($report, $filters, $sort, $limit);
        $summary = $this->fetchSummary($report, $filters);

        return [
            'report' => [
                'key' => $reportKey,
                'module' => $moduleKey,
                'module_label' => $module['label'] ?? $this->humanise($moduleKey),
                'label' => $report['label'] ?? $this->humanise($reportKey),
                'description' => $report['description'] ?? null,
                'columns' => $this->columnSchema($report),
                'filters' => $this->filterSchema($report, resolveOptions: true),
                'chart' => $report['chart'] ?? null,
            ],
            'filters' => $filters,
            'summary' => $summary,
            'rows' => $rows,
            'meta' => [
                'total' => $total,
                'returned' => count($rows),
                'limit' => $limit,
                'truncated' => $total > count($rows),
            ],
        ];
    }

    /**
     * Run a report and return exactly the rows, for CSV/PDF export.
     *
     * @return array<int, array<string, mixed>>
     */
    public function rows(string $moduleKey, string $reportKey, array $input, User $user): array
    {
        return $this->run($moduleKey, $reportKey, $input, $user)['rows'];
    }

    /* ------------------------------------------------------------------ */
    /* Query building                                                      */
    /* ------------------------------------------------------------------ */

    /**
     * The full registry, with anything in config/module_reports.php merged
     * over the built-in definitions. The config hook lets an installation add
     * its own report without editing PHP.
     */
    public function registry(): array
    {
        $registry = Registry::all();

        $overrides = config('module_reports', []);
        if (is_array($overrides) && $overrides !== []) {
            $registry = array_replace_recursive($registry, $overrides);
        }

        return $registry;
    }

    /**
     * Build the base query: the table, its joins and the filter WHERE clauses.
     */
    protected function baseQuery(array $report, array $filters): Builder
    {
        $base = $report['base'] ?? null;
        if (! $base) {
            throw new \RuntimeException('A report definition must declare a base table.');
        }

        $query = DB::table($this->assertIdentifier($base));

        // Soft-deleted rows are gone as far as the models are concerned, so a
        // report must not see them either. The column is only added when the
        // table actually has one.
        if ($this->hasColumn($base, 'deleted_at')) {
            $query->whereNull($base . '.deleted_at');
        }

        foreach ($report['joins'] ?? [] as $join) {
            $type = strtolower($join['type'] ?? 'left');
            $method = $type === 'inner' ? 'join' : 'leftJoin';

            $table = $this->assertIdentifier($join['table']);
            $alias = isset($join['alias']) ? $this->assertIdentifier($join['alias']) : null;
            $target = $alias ? $table . ' as ' . $alias : $table;

            $query->{$method}(
                $target,
                $this->assertSafeExpression($join['first']),
                $join['operator'] ?? '=',
                $this->assertSafeExpression($join['second'])
            );

            if ($alias && $this->hasColumn($table, 'deleted_at')) {
                $query->whereNull($alias . '.deleted_at');
            }

            // A joined table that soft deletes its rows should hide them too,
            // unless the join is aliased - the alias branch above already
            // covers that case, and applying both would reference a column
            // twice.
            if (! $alias && $table !== $base && $this->hasColumn($table, 'deleted_at')) {
                $query->whereNull($table . '.deleted_at');
            }
        }

        // Fixed conditions the report always applies - a voucher type, a
        // polymorphic location, and so on. The value is bound, never
        // interpolated.
        foreach ($report['constraints'] ?? [] as $constraint) {
            $column = $this->assertSafeExpression($constraint['column']);
            $operator = $this->assertOperator($constraint['operator'] ?? '=');

            if (array_key_exists('value', $constraint) && $constraint['value'] === null) {
                $operator === '!=' ? $query->whereNotNull($column) : $query->whereNull($column);
                continue;
            }

            if ($this->isPlainColumn($column)) {
                $query->where($column, $operator, $constraint['value']);
                continue;
            }

            // A constraint can also be a computed expression - "the amount
            // still owed on this invoice" - which cannot be wrapped in
            // backticks, so it goes into the WHERE clause as it was written,
            // with the value still bound as a parameter.
            $query->whereRaw("{$column} {$operator} ?", [$constraint['value']]);
        }

        foreach ($report['filters'] ?? [] as $index => $definition) {
            $value = $filters[$this->filterKey($index, $definition)] ?? null;
            if ($value === null || $value === '') {
                continue;
            }

            $this->applyFilter($query, $definition, $value);
        }

        return $query;
    }

    /**
     * The alias each joined table is known by inside the report.
     *
     * A table joined as "suppliers as supplier" takes the original name out
     * of scope, so every expression that mentions it has to name the alias
     * instead. Building the map once, and running every expression through
     * it, keeps the registry readable: the report author writes the real
     * table name and the engine points it at the right alias.
     *
     * A table that is joined more than once - a site that is both the
     * source and the destination of a transfer, a user who is both the
     * reporter and the resolver of an issue - is left out of the map: there
     * is no single name to point at, so the report has to name the alias it
     * means. The base table is left out too, because it keeps its own name
     * even when the report joins a second copy of it.
     *
     * @return array<string, string>
     */
    protected function aliasMap(array $report): array
    {
        $base = $report['base'] ?? null;

        $joins = $report['joins'] ?? [];

        $counts = [];
        foreach ($joins as $join) {
            $table = $join['table'] ?? null;
            if ($table) {
                $counts[$table] = ($counts[$table] ?? 0) + 1;
            }
        }

        $map = [];
        foreach ($joins as $join) {
            $table = $join['table'] ?? null;
            $alias = $join['alias'] ?? null;

            if (! $table || ! $alias || $table === $alias || $table === $base) {
                continue;
            }

            if (($counts[$table] ?? 0) !== 1) {
                continue;
            }

            $map[$table] = $alias;
        }

        return $map;
    }

    /**
     * The report definition, ready to be turned into SQL.
     *
     * Two things happen here. Unqualified columns are pointed at the table
     * they belong to, so that a filter on "status" cannot be ambiguous once
     * the report joins a second table with a status of its own. Then every
     * expression is rewritten to name the alias its table was joined under.
     */
    protected function normalise(array $report): array
    {
        $map = $this->aliasMap($report);

        $rewrite = function (?string $expression) use ($map): ?string {
            if ($expression === null) {
                return null;
            }

            foreach ($map as $table => $alias) {
                $expression = $this->aliasColumn($expression, $table, $alias);
            }

            return $expression;
        };

        // Point the filters at their tables first, while the joins are still
        // known by their real names.
        foreach ($report['filters'] ?? [] as $index => $filter) {
            if (isset($filter['column'])) {
                $report['filters'][$index]['column'] = $this->qualify($filter['column'], $report);
            }

            foreach ($filter['columns'] ?? [] as $position => $column) {
                $report['filters'][$index]['columns'][$position] = $this->qualify($column, $report);
            }
        }

        foreach ($report['joins'] ?? [] as $index => $join) {
            $table = $join['table'] ?? null;
            $alias = $join['alias'] ?? null;
            $second = $join['second'] ?? '';

            // The right hand side of a join names the table being joined,
            // which from that point on is known by its alias.
            if ($table && $alias && $table !== $alias) {
                $second = $this->aliasColumn($second, $table, $alias);
            }

            $report['joins'][$index]['first'] = $rewrite($join['first'] ?? '');
            $report['joins'][$index]['second'] = $rewrite($second);
        }

        foreach ($report['columns'] ?? [] as $index => $column) {
            if (isset($column['expression'])) {
                $report['columns'][$index]['expression'] = $rewrite($column['expression']);
            }
        }

        foreach ($report['group_by'] ?? [] as $index => $group) {
            $report['group_by'][$index] = $rewrite($group);
        }

        foreach ($report['constraints'] ?? [] as $index => $constraint) {
            if (isset($constraint['column'])) {
                $report['constraints'][$index]['column'] = $rewrite($constraint['column']);
            }
        }

        foreach ($report['summary'] ?? [] as $index => $definition) {
            if (isset($definition['expression'])) {
                $report['summary'][$index]['expression'] = $rewrite($definition['expression']);
            }
        }

        foreach ($report['filters'] ?? [] as $index => $filter) {
            if (isset($filter['column'])) {
                $report['filters'][$index]['column'] = $rewrite($filter['column']);
            }

            foreach ($filter['columns'] ?? [] as $position => $column) {
                $report['filters'][$index]['columns'][$position] = $rewrite($column);
            }
        }

        foreach ($report['order'] ?? [] as $index => $order) {
            if (isset($order['column'])) {
                $report['order'][$index]['column'] = $rewrite($order['column']);
            }
        }

        return $report;
    }

    /**
     * Point an unqualified column at the table it belongs to.
     *
     * "status" is ambiguous the moment a report joins a second table that
     * has one too, so a column named without a table is taken to mean the
     * report's own table - and failing that, the first joined table that has
     * such a column.
     */
    protected function qualify(string $column, array $report): string
    {
        if (str_contains($column, '.')) {
            return $column;
        }

        $base = $report['base'] ?? null;
        if ($base && $this->hasColumn($base, $column)) {
            return $base . '.' . $column;
        }

        foreach ($report['joins'] ?? [] as $join) {
            $table = $join['table'] ?? null;
            if ($table && $this->hasColumn($table, $column)) {
                return $table . '.' . $column;
            }
        }

        return $column;
    }

    /**
     * The key a filter is filed under.
     *
     * A report may declare its filters as a list, in which case each filter
     * carries its own key, or as a map from key to definition. Both are
     * accepted; the declared key always wins.
     */
    protected function filterKey(int|string $index, array $definition): string
    {
        return (string) ($definition['key'] ?? $index);
    }

    /**
     * Point a qualified column reference at a table's alias.
     *
     * "ledger_accounts.id" becomes "party.id" when ledger_accounts is joined
     * as "party", because the original name leaves scope the moment the
     * alias is introduced.
     */
    protected function aliasColumn(string $expression, string $table, string $alias): string
    {
        // The lookbehind keeps a table name that is only part of a longer
        // name - "users" inside "model_has_users" - from being rewritten.
        $pattern = '/(?<![A-Za-z0-9_.])' . preg_quote($table, '/') . '\./';

        return preg_replace($pattern, $alias . '.', $expression) ?? $expression;
    }

    /**
     * Does a table have a column? The column list is read once per table and
     * kept for the rest of the request.
     */
    protected function hasColumn(string $table, string $column): bool
    {
        static $cache = [];

        if (! isset($cache[$table])) {
            try {
                $cache[$table] = Schema::getColumnListing($table);
            } catch (\Throwable) {
                $cache[$table] = [];
            }
        }

        return in_array($column, $cache[$table], true);
    }

    /**
     * Apply a single declared filter. Every value is bound, never
     * interpolated.
     */
    protected function applyFilter(Builder $query, array $definition, $value): void
    {
        $type = $definition['type'] ?? 'text';
        $column = $definition['column'] ?? null;

        switch ($type) {
            case 'date':
                $query->whereDate($column, $definition['operator'] ?? '>=', $value);
                break;

            case 'select':
                $query->where($column, '=', $value);
                break;

            case 'multi':
                $values = array_values(array_filter(array_map('trim', explode(',', (string) $value)), fn ($item) => $item !== ''));
                if ($values !== []) {
                    $query->whereIn($column, $values);
                }
                break;

            case 'number':
                $query->where($column, '=', $value);
                break;

            case 'text':
            default:
                $columns = $definition['columns'] ?? ($column ? [$column] : []);
                if ($columns === []) {
                    return;
                }

                $needle = '%' . $this->escapeLike((string) $value) . '%';
                $query->where(function ($inner) use ($columns, $needle) {
                    foreach ($columns as $index => $candidate) {
                        $candidate = $this->assertSafeExpression($candidate);
                        $index === 0 ? $inner->where($candidate, 'like', $needle) : $inner->orWhere($candidate, 'like', $needle);
                    }
                });
                break;
        }
    }

    /**
     * Keep the wildcards a user typed from being read as SQL wildcards.
     */
    protected function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    /**
     * Fetch the report rows, applying the column list, GROUP BY, sort and
     * limit.
     */
    protected function fetchRows(array $report, array $filters, array $sort, int $limit): array
    {
        $query = $this->groupedQuery($report, $filters);

        // The registry's own ordering is the default; an explicit sort
        // request overrides it.
        $ordering = $sort !== [] ? $sort : ($report['order'] ?? []);
        foreach ($ordering as $order) {
            $column = $order['column'] ?? null;
            if (! $column) {
                continue;
            }
            $direction = strtolower($order['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
            $query->orderBy($this->assertSafeExpression($column), $direction);
        }

        $query->limit($limit);

        $rows = $query->get()->map(function ($row) use ($report) {
            return $this->castRow((array) $row, $report);
        })->all();

        return $rows;
    }

    /**
     * The report's rows, grouped but not yet ordered or limited.
     *
     * The same query is used for the table, for the row count and - when a
     * report asks for it - for the headline figures, so all three always
     * agree on what a "row" is.
     */
    protected function groupedQuery(array $report, array $filters): Builder
    {
        $query = $this->baseQuery($report, $filters);

        $select = [];
        foreach ($report['columns'] ?? [] as $column) {
            $key = $column['key'] ?? null;
            if (! $key) {
                continue;
            }
            $select[] = $this->assertSafeExpression($column['expression'] ?? $key) . ' as ' . $this->alias($key);
        }

        if ($select === []) {
            $select = ['*'];
        }

        $query->selectRaw(implode(', ', $select));

        foreach ($this->groupBy($report) as $group) {
            $query->groupByRaw($this->assertSafeExpression($group));
        }

        return $query;
    }

    /**
     * The GROUP BY the report runs with.
     *
     * The registry states the grouping it wants, but a grouped report nearly
     * always shows a few descriptive columns as well - the supplier of a
     * purchase order, the status of an order - and MySQL will not accept
     * those in a grouped select unless they are part of the grouping. Every
     * selected expression that is not an aggregate is therefore added to the
     * GROUP BY, which is what the report means by it: one row per group, with
     * those columns carried along.
     *
     * @return array<int, string>
     */
    protected function groupBy(array $report): array
    {
        $groups = [];

        foreach ($report['group_by'] ?? [] as $group) {
            $groups[] = $group;
        }

        foreach ($report['columns'] ?? [] as $column) {
            $expression = $column['expression'] ?? $column['key'] ?? null;

            if (! $expression || $this->isAggregate($expression)) {
                continue;
            }

            if (in_array($expression, $groups, true)) {
                continue;
            }

            $groups[] = $expression;
        }

        return $groups;
    }

    /**
     * Does the expression contain an aggregate function?
     *
     * An expression that aggregates cannot be a grouping key, so it is left
     * out of the GROUP BY - MySQL allows an aggregate to look at columns that
     * are not themselves grouped.
     */
    protected function isAggregate(string $expression): bool
    {
        $functions = 'sum|count|avg|min|max|group_concat|std|stddev|variance|var_pop|var_samp|bit_or|bit_and|bit_xor|json_arrayagg|json_objectagg';

        return (bool) preg_match('/\b(' . $functions . ')\s*\(/i', $expression);
    }

    /**
     * Compute the headline figures for a report.
     *
     * The summary runs against the same filtered rows as the table, so a
     * filtered report never mixes an unfiltered total into its headline.
     */
    protected function fetchSummary(array $report, array $filters): array
    {
        $definitions = $report['summary'] ?? [];
        if ($definitions === []) {
            return [];
        }

        $query = $this->baseQuery($report, $filters);

        // A report can ask for its headline figures to be worked out over its
        // own grouping - "how many products are short" - which needs the
        // grouped rows to be a table in their own right.
        if ($report['summary_grouped'] ?? false) {
            $query = DB::query()->fromSub($this->groupedQuery($report, $filters), 'grouped');
        }

        $select = [];
        foreach ($definitions as $definition) {
            $key = $definition['key'] ?? null;
            if (! $key) {
                continue;
            }
            $expression = $this->assertSafeExpression($definition['expression'] ?? 'count(*)');
            $select[] = $expression . ' as ' . $this->alias($key);
        }

        $row = $query->selectRaw(implode(', ', $select))->first();

        $summary = [];
        foreach ($definitions as $definition) {
            $key = $definition['key'] ?? null;
            if (! $key) {
                continue;
            }

            $summary[] = [
                'key' => $key,
                'label' => $definition['label'] ?? $this->humanise($key),
                'value' => $this->castValue($row->{$key} ?? null, $definition['format'] ?? 'number'),
                'format' => $definition['format'] ?? 'number',
            ];
        }

        return $summary;
    }

    /**
     * Count the rows the report matches, ignoring the limit.
     *
     * A grouped report counts groups, not source rows, so "5 departments"
     * reads as 5 rather than as the headcount behind them.
     */
    protected function countRows(array $report, array $filters): int
    {
        $query = $this->baseQuery($report, $filters);

        $groups = $this->groupBy($report);

        if ($groups !== []) {
            foreach ($groups as $group) {
                $query->groupByRaw($this->assertSafeExpression($group));
            }

            $query->selectRaw('1 as ' . $this->alias('grouped_row'));

            return (int) DB::query()->fromSub($query, 'grouped')->count();
        }

        return (int) $query->count();
    }

    /* ------------------------------------------------------------------ */
    /* Validation                                                          */
    /* ------------------------------------------------------------------ */

    /**
     * Keep only the filters the report declared, and check each value.
     *
     * Undeclared keys are dropped rather than rejected: the frontend sends a
     * single filter object and a report that does not use one of the keys
     * should simply ignore it.
     */
    protected function validateFilters(array $report, array $input): array
    {
        $clean = [];

        foreach ($report['filters'] ?? [] as $index => $definition) {
            $key = $this->filterKey($index, $definition);
            $value = $input[$key] ?? null;

            // A filter may carry a default so a report can open on the slice
            // that matters (the pending approvals, say) without hiding the
            // other values from the user.
            if (($value === null || $value === '') && array_key_exists('default', $definition)) {
                $value = $definition['default'];
            }

            if ($value === null || $value === '' || (is_array($value) && $value === [])) {
                continue;
            }

            if (is_array($value)) {
                throw ValidationException::withMessages([$key => 'The ' . $definition['label'] . ' filter takes a single value.']);
            }

            $value = is_string($value) ? trim($value) : $value;

            switch ($definition['type'] ?? 'text') {
                case 'date':
                    if (! $this->isDate($value)) {
                        throw ValidationException::withMessages([$key => 'The ' . $definition['label'] . ' filter must be a date.']);
                    }
                    $clean[$key] = substr((string) $value, 0, 10);
                    break;

                case 'number':
                    if (! is_numeric($value)) {
                        throw ValidationException::withMessages([$key => 'The ' . $definition['label'] . ' filter must be a number.']);
                    }
                    $clean[$key] = $value + 0;
                    break;

                case 'select':
                    $options = $this->filterOptions($definition);
                    if ($options !== [] && ! array_key_exists((string) $value, $options)) {
                        throw ValidationException::withMessages([$key => 'The ' . $definition['label'] . ' filter has an unknown value.']);
                    }
                    $clean[$key] = (string) $value;
                    break;

                case 'multi':
                    $options = $this->filterOptions($definition);
                    $values = array_values(array_filter(array_map('trim', explode(',', (string) $value)), fn ($item) => $item !== ''));
                    if ($values === []) {
                        break;
                    }
                    if ($options !== []) {
                        foreach ($values as $item) {
                            if (! array_key_exists($item, $options)) {
                                throw ValidationException::withMessages([$key => 'The ' . $definition['label'] . ' filter has an unknown value.']);
                            }
                        }
                    }
                    $clean[$key] = implode(',', $values);
                    break;

                case 'text':
                default:
                    $clean[$key] = mb_substr((string) $value, 0, 120);
                    break;
            }
        }

        return $clean;
    }

    /**
     * The sort the caller asked for, provided the column is one the report
     * actually exposes.
     */
    protected function resolveSort(array $report, array $input): array
    {
        $column = $input['sort'] ?? null;
        if (! $column) {
            return [];
        }

        $known = array_column($report['columns'] ?? [], 'key');
        if (! in_array($column, $known, true)) {
            return [];
        }

        $direction = strtolower((string) ($input['direction'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';

        return [['column' => $column, 'direction' => $direction]];
    }

    /**
     * How many rows to return. The registry may lower the ceiling but never
     * raise it past MAX_LIMIT.
     */
    protected function resolveLimit(array $report, array $input): int
    {
        $limit = $report['limit'] ?? self::DEFAULT_LIMIT;

        if (isset($input['limit']) && is_numeric($input['limit'])) {
            $limit = (int) $input['limit'];
        }

        $limit = max(1, min((int) $limit, self::MAX_LIMIT));

        return $limit;
    }

    /* ------------------------------------------------------------------ */
    /* Presentation                                                        */
    /* ------------------------------------------------------------------ */

    /**
     * The column list, ready for the generic table renderer.
     */
    public function columnSchema(array $report): array
    {
        $columns = [];

        foreach ($report['columns'] ?? [] as $column) {
            $key = $column['key'] ?? null;
            if (! $key) {
                continue;
            }

            $format = $column['format'] ?? 'text';

            $columns[] = [
                'key' => $key,
                'label' => $column['label'] ?? $this->humanise($key),
                'format' => $format,
                'align' => $column['align'] ?? $this->defaultAlign($format),
                'primary' => (bool) ($column['primary'] ?? false),
            ];
        }

        return $columns;
    }

    /**
     * The filter list, ready for the generic filter bar.
     *
     * Select filters whose options come from the database are only resolved
     * when asked: the catalogue is rendered for every module at once, and
     * resolving them there would mean a query per filter per report.
     */
    public function filterSchema(array $report, bool $resolveOptions = true): array
    {
        $filters = [];

        foreach ($report['filters'] ?? [] as $index => $definition) {
            $type = $definition['type'] ?? 'text';

            $filter = [
                'key' => $this->filterKey($index, $definition),
                'label' => $definition['label'] ?? $this->humanise($index),
                'type' => $type,
            ];

            if (array_key_exists('default', $definition)) {
                $filter['default'] = $definition['default'];
            }

            if (in_array($type, ['select', 'multi'], true)) {
                // The catalogue is drawn for every module at once, so a
                // database-backed option list is only resolved when a page
                // actually asks for one report's filters. Either way the
                // catalogue carries the same "options" key, so the frontend
                // never has to know where a list came from.
                $filter['options'] = $resolveOptions
                    ? $this->filterOptions($definition)
                    : $this->normaliseOptions($definition['options'] ?? []);
            }

            $filters[] = $filter;
        }

        return $filters;
    }

    /**
     * The option list for a select filter, either declared or read from the
     * distinct values of a column.
     */
    protected function filterOptions(array $definition): array
    {
        if (! empty($definition['options'])) {
            return $this->normaliseOptions($definition['options']);
        }

        $source = $definition['options_source'] ?? null;
        if (! $source) {
            return [];
        }

        $table = $this->assertIdentifier($source['table']);
        $column = $this->assertSafeExpression($source['column']);

        $values = DB::table($table)
            ->select($column)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->map(fn ($value) => (string) $value)
            ->all();

        $options = [];
        foreach ($values as $value) {
            $options[$value] = $value;
        }

        return $options;
    }

    /**
     * Filter options are always a map of value => label.
     *
     * A registry author may write the options as a plain list - ['Pending',
     * 'Submitted'] - or as a map - ['pending' => 'Pending']. Both have to
     * reach the frontend in one shape, and both have to be checked against
     * the value the user sent, so the list form is folded into a map here.
     *
     * @param  array<int|string, mixed>  $options
     * @return array<string, string>
     */
    protected function normaliseOptions(array $options): array
    {
        $map = [];

        foreach ($options as $key => $value) {
            // ['value' => 'approved', 'label' => 'Approved']
            if (is_array($value)) {
                $optionValue = (string) ($value['value'] ?? $key);
                $map[$optionValue] = (string) ($value['label'] ?? $optionValue);
                continue;
            }

            // A plain list: the value is the label.
            if (is_int($key)) {
                $map[(string) $value] = (string) $value;
                continue;
            }

            $map[(string) $key] = (string) $value;
        }

        return $map;
    }

    /**
     * Cast a raw database row into the shape the renderer expects: numbers as
     * numbers, everything else untouched.
     */
    protected function castRow(array $row, array $report): array
    {
        $formats = [];
        foreach ($report['columns'] ?? [] as $column) {
            if (! empty($column['key'])) {
                $formats[$column['key']] = $column['format'] ?? 'text';
            }
        }

        $out = [];
        foreach ($row as $key => $value) {
            $out[$key] = $this->castValue($value, $formats[$key] ?? 'text');
        }

        return $out;
    }

    /**
     * Cast a single value according to its column format.
     */
    protected function castValue($value, string $format)
    {
        if ($value === null) {
            return null;
        }

        if (in_array($format, ['number', 'money', 'percent'], true)) {
            return is_numeric($value) ? (float) $value : null;
        }

        if ($format === 'boolean') {
            return (bool) $value;
        }

        return $value;
    }

    /* ------------------------------------------------------------------ */
    /* Helpers                                                             */
    /* ------------------------------------------------------------------ */

    /**
     * Right-align the columns that hold numbers, everything else to the left.
     */
    protected function defaultAlign(string $format): string
    {
        return in_array($format, ['number', 'money', 'percent'], true) ? 'right' : 'left';
    }

    /**
     * Screen a SQL fragment that came from the registry.
     *
     * The registry is static PHP, so this is not a security boundary against
     * a determined attacker - it is a guard against a mistyped expression
     * turning a report into something that is not a report.
     */
    protected function assertSafeExpression(string $expression): string
    {
        // Letters, digits, identifiers, and the punctuation a SQL expression
        // legitimately needs: arithmetic, comparison, grouping and quoting.
        // Everything else - a statement separator, a comment marker - is
        // refused.
        if (! preg_match('/^[A-Za-z0-9_.*()\-+,:\/\'"=<>!% ]+$/', $expression)) {
            throw new \RuntimeException("Unsafe SQL expression in report definition: {$expression}");
        }

        if (preg_match('/(--|\/\*|\*\/|;)/', $expression)) {
            throw new \RuntimeException("Unsafe SQL expression in report definition: {$expression}");
        }

        // An odd number of quotes means the expression was written wrong - a
        // string literal that was never closed. Catching it here gives a
        // report author a clear message instead of a SQL syntax error.
        if (substr_count($expression, "'") % 2 !== 0) {
            throw new \RuntimeException("Unbalanced quotes in report expression: {$expression}");
        }

        return $expression;
    }

    /**
     * Screen the operator a constraint compares with.
     */
    protected function assertOperator(string $operator): string
    {
        $operator = strtolower(trim($operator));

        $allowed = ['=', '!=', '<>', '>', '<', '>=', '<=', 'like', 'not like'];

        if (! in_array($operator, $allowed, true)) {
            throw new \RuntimeException("Unsafe operator in report definition: {$operator}");
        }

        return $operator;
    }

    /**
     * Is the expression a plain column, rather than something computed?
     *
     * A plain column can be quoted as a column name; anything else has to go
     * into the query as it was written.
     */
    protected function isPlainColumn(string $expression): bool
    {
        return (bool) preg_match('/^[A-Za-z_][A-Za-z0-9_]*(\.[A-Za-z_][A-Za-z0-9_]*)?$/', $expression);
    }

    /**
     * Quote an output alias for use in a select list.
     *
     * Report keys are ordinary identifiers, but a few of them - "group",
     * "primary", "lines" - are reserved words in MySQL. Backticks keep them
     * usable as column aliases and as ORDER BY targets.
     */
    protected function alias(string $key): string
    {
        return '`' . $this->assertIdentifier($key) . '`';
    }

    /**
     * Screen a table or alias name.
     */
    protected function assertIdentifier(string $identifier): string
    {
        if (! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier)) {
            throw new \RuntimeException("Unsafe table name in report definition: {$identifier}");
        }

        return $identifier;
    }

    /**
     * A loose date check - the engine only needs Y-m-d.
     */
    protected function isDate($value): bool
    {
        if (! is_string($value) || $value === '') {
            return false;
        }

        $date = \DateTime::createFromFormat('Y-m-d', substr($value, 0, 10));
        if ($date && $date->format('Y-m-d') === substr($value, 0, 10)) {
            return true;
        }

        return (bool) strtotime((string) $value);
    }

    /**
     * Turn a snake_case or kebab-case key into a readable label.
     */
    protected function humanise(string $key): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $key));
    }
}
