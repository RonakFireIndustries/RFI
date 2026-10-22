import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import {
  ArrowLeft, ChevronDown, ChevronUp, Download, FileSpreadsheet, FileText,
  Loader2, RefreshCw, Inbox, BarChart3,
} from 'lucide-react';
import api from '../../services/api';
import { useAuthStore } from '../../store/authStore';
import { can } from '../../utils/permissions';
import { badgeTone, formatCell, formatSummaryValue } from './format';

/*
 * One module report, rendered generically.
 *
 * The backend describes every report the same way - a column list, a filter
 * schema, a summary and the rows - so this page can draw any of them without
 * knowing which one it is. Adding a report on the server therefore needs no
 * work here at all: the page fetches its own description along with its data.
 */

const INPUT = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-orange-500 focus:ring-orange-500';
const LABEL = 'block text-xs font-medium text-gray-600 mb-1';

/** The filters a report offers, with their current values. */
const FilterBar = ({ filters, values, onChange, onReset, loading, onRun, onExport, canExport, exporting }) => {
  if (filters.length === 0 && !canExport) return null;

  return (
    <div className="rounded-lg bg-white p-4 shadow">
      <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        {filters.map((filter) => {
          const value = values[filter.key] ?? filter.default ?? '';
          const options = Object.entries(filter.options ?? {});

          return (
            <div key={filter.key}>
              <label className={LABEL} htmlFor={`filter-${filter.key}`}>
                {filter.label}
              </label>

              {filter.type === 'select' ? (
                <select
                  id={`filter-${filter.key}`}
                  className={INPUT}
                  value={value}
                  onChange={(event) => onChange(filter.key, event.target.value)}
                >
                  <option value="">All {filter.label.toLowerCase()}</option>
                  {options.map(([optionValue, optionLabel]) => (
                    <option key={optionValue} value={optionValue}>{optionLabel}</option>
                  ))}
                </select>
              ) : filter.type === 'multi' ? (
                // A multi filter is a comma separated list on the wire, so it
                // is rendered as a set of toggles rather than as a native
                // multiple-select (which is awkward to use on a tablet).
                <div className="max-h-32 overflow-y-auto rounded-lg border border-gray-300 bg-white p-2">
                  {options.length === 0 ? (
                    <p className="text-xs text-gray-400">No values to choose from.</p>
                  ) : (
                    options.map(([optionValue, optionLabel]) => {
                      const selected = String(value).split(',').map((v) => v.trim()).filter(Boolean);
                      const isOn = selected.includes(optionValue);

                      return (
                        <label key={optionValue} className="flex items-center gap-2 py-0.5 text-sm text-gray-700">
                          <input
                            type="checkbox"
                            className="rounded border-gray-300 text-orange-500 focus:ring-orange-500"
                            checked={isOn}
                            onChange={() => {
                              const next = isOn
                                ? selected.filter((item) => item !== optionValue)
                                : [...selected, optionValue];
                              onChange(filter.key, next.join(','));
                            }}
                          />
                          <span className="truncate">{optionLabel}</span>
                        </label>
                      );
                    })
                  )}
                </div>
              ) : (
                <input
                  id={`filter-${filter.key}`}
                  type={filter.type === 'date' ? 'date' : filter.type === 'number' ? 'number' : 'text'}
                  className={INPUT}
                  value={value}
                  placeholder={filter.type === 'text' ? 'Search…' : ''}
                  onChange={(event) => onChange(filter.key, event.target.value)}
                  onKeyDown={(event) => { if (event.key === 'Enter') onRun(); }}
                />
              )}
            </div>
          );
        })}
      </div>

      <div className="mt-3 flex flex-wrap items-center gap-2 border-t border-gray-100 pt-3">
        <button
          type="button"
          onClick={onRun}
          disabled={loading}
          className="inline-flex items-center rounded-lg bg-orange-500 px-4 py-2 text-sm text-white hover:bg-orange-600 disabled:opacity-50"
        >
          {loading ? <Loader2 className="w-4 h-4 mr-2 animate-spin" /> : <BarChart3 className="w-4 h-4 mr-2" />}
          {loading ? 'Running…' : 'Run report'}
        </button>

        <button
          type="button"
          onClick={onReset}
          className="inline-flex items-center rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
        >
          <RefreshCw className="w-4 h-4 mr-2" /> Reset
        </button>

        {canExport && (
          <>
            <button
              type="button"
              onClick={() => onExport('csv')}
              disabled={exporting}
              className="inline-flex items-center rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50"
            >
              <FileSpreadsheet className="w-4 h-4 mr-2" /> CSV
            </button>
            <button
              type="button"
              onClick={() => onExport('pdf')}
              disabled={exporting}
              className="inline-flex items-center rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50"
            >
              <FileText className="w-4 h-4 mr-2" /> PDF
            </button>
          </>
        )}
      </div>
    </div>
  );
};

/** One headline figure. */
const SummaryTile = ({ figure }) => (
  <div className="rounded-lg bg-gray-50 p-3">
    <div className="text-[11px] uppercase tracking-wide text-gray-500">{figure.label}</div>
    <div className="mt-1 text-lg font-semibold text-gray-900">
      {formatSummaryValue(figure.value, figure.format)}
    </div>
  </div>
);

/** The report table, with click-to-sort on the headers. */
const ReportTable = ({ columns, rows, sort, onSort }) => (
  <div className="overflow-x-auto">
    <table className="min-w-full divide-y divide-gray-200">
      <thead className="bg-gray-50">
        <tr>
          {columns.map((column) => {
            const active = sort?.column === column.key;
            return (
              <th
                key={column.key}
                scope="col"
                className={`px-3 py-3 text-xs font-medium uppercase tracking-wider text-gray-500 ${
                  column.align === 'right' ? 'text-right' : 'text-left'
                }`}
              >
                <button
                  type="button"
                  onClick={() => onSort(column.key)}
                  className={`inline-flex items-center gap-1 hover:text-gray-700 ${active ? 'text-orange-600' : ''}`}
                >
                  {column.label}
                  {active && (sort.direction === 'desc'
                    ? <ChevronDown className="w-3 h-3" />
                    : <ChevronUp className="w-3 h-3" />)}
                </button>
              </th>
            );
          })}
        </tr>
      </thead>

      <tbody className="divide-y divide-gray-100 bg-white">
        {rows.map((row, index) => (
          <tr key={index} className="hover:bg-gray-50">
            {columns.map((column) => {
              const value = row[column.key];

              return (
                <td
                  key={column.key}
                  className={`whitespace-nowrap px-3 py-2 text-sm ${
                    column.align === 'right' ? 'text-right tabular-nums' : 'text-left'
                  } ${column.primary ? 'font-medium text-gray-900' : 'text-gray-700'}`}
                >
                  {column.format === 'badge' && value !== null && value !== undefined && value !== '' ? (
                    <span className={`inline-flex items-center rounded-full border px-2 py-0.5 text-xs ${badgeTone(value)}`}>
                      {value}
                    </span>
                  ) : (
                    formatCell(value, column.format)
                  )}
                </td>
              );
            })}
          </tr>
        ))}
      </tbody>
    </table>
  </div>
);

export default function ReportViewPage() {
  const { module, report } = useParams();
  const user = useAuthStore((s) => s.user);
  const permissions = useAuthStore((s) => s.permissions);

  const [payload, setPayload] = useState(null);
  const [values, setValues] = useState({});
  const [sort, setSort] = useState(null);
  const [loading, setLoading] = useState(true);
  const [exporting, setExporting] = useState(false);
  const [error, setError] = useState(null);

  // The filters the page has actually run with. Held apart from `values` so
  // typing in a box does not re-run the report on every keystroke.
  const [applied, setApplied] = useState({});
  const firstLoad = useRef(true);

  const canExport = can({ user, permissions }, 'reports.export');

  const load = useCallback(async (filters, ordering) => {
    setLoading(true);
    setError(null);

    try {
      const params = { ...filters };
      if (ordering) {
        params.sort = ordering.column;
        params.direction = ordering.direction;
      }

      const response = await api.get(`/module-reports/${module}/${report}`, { params });
      setPayload(response.data);

      // The first load seeds the filter boxes with whatever the report chose
      // as its defaults, so the form matches the data on screen.
      if (firstLoad.current) {
        setValues(response.data.filters ?? {});
        firstLoad.current = false;
      }
    } catch (err) {
      setPayload(null);
      setError(err?.response?.data?.message || 'Could not run this report.');
    } finally {
      setLoading(false);
    }
  }, [module, report]);

  useEffect(() => {
    firstLoad.current = true;
    setApplied({});
    setSort(null);
    setValues({});
    load({}, null);
  }, [module, report, load]);

  const onRun = () => {
    setApplied(values);
    load(values, sort);
  };

  const onReset = () => {
    setValues({});
    setApplied({});
    setSort(null);
    load({}, null);
  };

  const onSort = (columnKey) => {
    const next = sort?.column === columnKey && sort.direction === 'asc'
      ? { column: columnKey, direction: 'desc' }
      : { column: columnKey, direction: 'asc' };

    setSort(next);
    load(applied, next);
  };

  const onExport = async (format) => {
    setExporting(true);

    try {
      const params = new URLSearchParams({ ...applied, format });
      if (sort) {
        params.set('sort', sort.column);
        params.set('direction', sort.direction);
      }

      const blob = await api.getBlob(`/module-reports/${module}/${report}/export?${params.toString()}`);

      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `${(payload?.report?.label ?? report).replace(/[^\w]+/g, '-').toLowerCase()}.${format}`;
      document.body.appendChild(link);
      link.click();
      link.remove();
      URL.revokeObjectURL(url);
    } catch (err) {
      setError(err?.response?.data?.message || 'Could not export this report.');
    } finally {
      setExporting(false);
    }
  };

  const columns = payload?.report?.columns ?? [];
  const filters = payload?.report?.filters ?? [];
  const rows = payload?.rows ?? [];
  const meta = payload?.meta;

  const emptyMessage = useMemo(
    () => (filters.length > 0
      ? 'No rows matched the selected filters.'
      : 'This report has no rows to show yet.'),
    [filters.length]
  );

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <Link
            to="/dashboard/reports"
            className="inline-flex items-center text-xs text-gray-500 hover:text-gray-700"
          >
            <ArrowLeft className="w-3.5 h-3.5 mr-1" /> All reports
          </Link>
          <h1 className="mt-1 text-xl font-semibold text-gray-900">
            {payload?.report?.label ?? 'Report'}
          </h1>
          <p className="text-sm text-gray-500">
            {payload?.report?.module_label ?? module}
            {meta ? ` · ${meta.total} row${meta.total === 1 ? '' : 's'}` : ''}
            {meta?.truncated ? ` · showing the first ${meta.returned}` : ''}
          </p>
        </div>

        {rows.length > 0 && canExport && (
          <button
            type="button"
            onClick={() => onExport('csv')}
            disabled={exporting}
            className="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 disabled:opacity-50"
          >
            <Download className="w-4 h-4 mr-2" /> Export
          </button>
        )}
      </div>

      {error && (
        <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          {error}
        </div>
      )}

      {payload?.report?.description && (
        <p className="text-sm text-gray-600">{payload.report.description}</p>
      )}

      <FilterBar
        filters={filters}
        values={values}
        onChange={(key, value) => setValues((prev) => ({ ...prev, [key]: value }))}
        onReset={onReset}
        onRun={onRun}
        loading={loading}
        onExport={onExport}
        canExport={canExport}
        exporting={exporting}
      />

      {payload?.summary?.length > 0 && (
        <div className="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4">
          {payload.summary.map((figure) => <SummaryTile key={figure.key} figure={figure} />)}
        </div>
      )}

      <div className="rounded-lg bg-white shadow">
        {loading && rows.length === 0 ? (
          <div className="flex items-center justify-center py-16 text-gray-500">
            <Loader2 className="w-5 h-5 mr-2 animate-spin" /> Running report…
          </div>
        ) : rows.length === 0 ? (
          <div className="px-5 py-14 text-center text-gray-500">
            <Inbox className="w-6 h-6 mx-auto mb-2 text-gray-300" />
            <p className="text-sm">{emptyMessage}</p>
          </div>
        ) : (
          <ReportTable columns={columns} rows={rows} sort={sort} onSort={onSort} />
        )}
      </div>

      {meta?.truncated && (
        <p className="text-xs text-gray-500">
          Showing {meta.returned} of {meta.total} rows. Narrow the filters, or export the report to
          get the whole set.
        </p>
      )}
    </div>
  );
}
