<?php

namespace App\Support\Reports;

/**
 * Terse builders for the report registry.
 *
 * A report definition is a plain array. Writing it out longhand means
 * repeating 'key', 'label', 'expression' and 'format' for every column, which
 * is both noisy and easy to get wrong. These helpers keep the registry
 * readable: a column is one line, and the format is implied by the helper
 * that was used.
 */
class Spec
{
    /* -------------------------------- columns -------------------------------- */

    /** A plain text column. */
    public static function col(string $key, string $label, string $expression, array $extra = []): array
    {
        return array_merge([
            'key' => $key,
            'label' => $label,
            'expression' => $expression,
            'format' => 'text',
        ], $extra);
    }

    /** A right-aligned count or quantity. */
    public static function num(string $key, string $label, string $expression, array $extra = []): array
    {
        return static::col($key, $label, $expression, array_merge(['format' => 'number'], $extra));
    }

    /** A currency column. */
    public static function money(string $key, string $label, string $expression, array $extra = []): array
    {
        return static::col($key, $label, $expression, array_merge(['format' => 'money'], $extra));
    }

    /** A percentage column. */
    public static function percent(string $key, string $label, string $expression, array $extra = []): array
    {
        return static::col($key, $label, $expression, array_merge(['format' => 'percent'], $extra));
    }

    /** A date column, rendered DD/MM/YYYY. */
    public static function date(string $key, string $label, string $expression, array $extra = []): array
    {
        return static::col($key, $label, $expression, array_merge(['format' => 'date'], $extra));
    }

    /** A timestamp column, rendered DD/MM/YYYY with the time. */
    public static function datetime(string $key, string $label, string $expression, array $extra = []): array
    {
        return static::col($key, $label, $expression, array_merge(['format' => 'datetime'], $extra));
    }

    /** A status column, rendered as a coloured chip. */
    public static function badge(string $key, string $label, string $expression, array $extra = []): array
    {
        return static::col($key, $label, $expression, array_merge(['format' => 'badge'], $extra));
    }

    /* --------------------------------- joins --------------------------------- */

    /** A left join - the common case, so the master rows are never lost. */
    public static function join(string $table, string $first, string $second, string $type = 'left', ?string $alias = null): array
    {
        $join = [
            'type' => $type,
            'table' => $table,
            'first' => $first,
            'operator' => '=',
            'second' => $second,
        ];

        if ($alias !== null) {
            $join['alias'] = $alias;
        }

        return $join;
    }

    /* -------------------------------- filters -------------------------------- */

    /** A date range bound, e.g. 'from' with '>=' or 'to' with '<='. */
    public static function dateFilter(string $key, string $label, string $column, string $operator = '>='): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'type' => 'date',
            'column' => $column,
            'operator' => $operator,
        ];
    }

    /** A dropdown with a fixed list of options. */
    public static function selectFilter(string $key, string $label, string $column, array $options = [], array $extra = []): array
    {
        return array_merge([
            'key' => $key,
            'label' => $label,
            'type' => 'select',
            'column' => $column,
            'options' => $options,
        ], $extra);
    }

    /**
     * A dropdown whose options are the distinct values already in a column.
     *
     * Better than a fixed list for statuses: the filter always offers exactly
     * what the data holds, so it can never offer a value that returns nothing.
     *
     * The optional `$filterColumn` qualifies the column in the WHERE clause
     * when the report joins another table that has a column of the same name
     * - the options still come from `$table`.`$column`.
     */
    public static function sourceFilter(string $key, string $label, string $table, string $column, ?string $filterColumn = null): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'type' => 'select',
            'column' => $filterColumn ?? $column,
            'options_source' => ['table' => $table, 'column' => $column],
        ];
    }

    /**
     * A multi-select dropdown whose options are the distinct values in a
     * column. The engine reads the value as a comma separated list.
     */
    public static function sourceMultiFilter(string $key, string $label, string $table, string $column, ?string $filterColumn = null): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'type' => 'multi',
            'column' => $filterColumn ?? $column,
            'options_source' => ['table' => $table, 'column' => $column],
        ];
    }

    /**
     * A dropdown that accepts more than one value at a time.
     *
     * The engine reads the value as a comma separated list, so a report can
     * say "show me every pending and submitted record" in one filter rather
     * than making the user run the report twice.
     */
    public static function multiFilter(string $key, string $label, string $column, array $options = [], array $extra = []): array
    {
        return array_merge([
            'key' => $key,
            'label' => $label,
            'type' => 'multi',
            'column' => $column,
            'options' => $options,
        ], $extra);
    }

    /** A free text search across one or more columns. */
    public static function textFilter(string $key, string $label, array $columns): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'type' => 'text',
            'columns' => $columns,
        ];
    }

    /** An exact-match filter on a numeric column. */
    public static function numberFilter(string $key, string $label, string $column, array $extra = []): array
    {
        return array_merge([
            'key' => $key,
            'label' => $label,
            'type' => 'number',
            'column' => $column,
        ], $extra);
    }

    /* -------------------------------- summary -------------------------------- */

    /** A headline figure for the report. */
    public static function summary(string $key, string $label, string $expression, string $format = 'number'): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'expression' => $expression,
            'format' => $format,
        ];
    }

    /* --------------------------------- order --------------------------------- */

    /** A sort instruction. The column is the output key, not a table column. */
    public static function order(string $column, string $direction = 'asc'): array
    {
        return ['column' => $column, 'direction' => $direction];
    }

    /* --------------------------------- chart --------------------------------- */

    /**
     * A chart drawn from the report rows.
     *
     * `label` and `value` are output keys, so a bar chart is just a grouped
     * report with a name column and a number column.
     */
    public static function chart(string $type, string $label, string $value, int $limit = 12): array
    {
        return [
            'type' => $type,
            'label' => $label,
            'value' => $value,
            'limit' => $limit,
        ];
    }
}
