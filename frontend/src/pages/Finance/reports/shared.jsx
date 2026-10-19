import React, { useCallback, useEffect, useState } from 'react';
import { RefreshCw, PlayCircle, Inbox } from 'lucide-react';
import { formatMoney } from '../../../utils/finance';

/*
 * Shared building blocks for the financial report pack (slice 5).
 *
 * Every report is a read-only fetch: `useReport` owns the loading/error state,
 * and the presentational pieces below keep the seven report bodies consistent
 * with each other instead of re-declaring the same Tailwind classes.
 */

/* -------------------------------- styling -------------------------------- */

export const INPUT = 'border border-gray-300 rounded-lg shadow-sm px-3 py-2 text-sm focus:ring-orange-500 focus:border-orange-500 bg-white';
export const LABEL = 'block text-xs font-medium text-gray-600 mb-1';
export const BTN_PRIMARY = 'inline-flex items-center bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm disabled:opacity-50';
export const BTN_SECONDARY = 'inline-flex items-center border border-gray-300 text-gray-700 hover:bg-gray-50 px-3 py-2 rounded-lg text-sm disabled:opacity-50';

export const firstError = (err) => {
  const errors = err?.response?.data?.data?.errors || err?.response?.data?.errors;
  if (errors) return Object.values(errors)[0]?.[0];
  return err?.response?.data?.data?.message || err?.response?.data?.message || 'Something went wrong.';
};

/**
 * Re-exported so the report bodies keep importing from one place; the
 * implementation (and the timezone note) lives in utils/date.
 */
export { formatDate } from '../../../utils/date';

/* --------------------------------- hooks --------------------------------- */

/**
 * Run `loader` whenever `deps` change (or when reloaded) and expose the
 * result. Every report uses this so the seven screens behave identically.
 */
export const useReport = (loader, deps = []) => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [tick, setTick] = useState(0);

  useEffect(() => {
    let active = true;
    setLoading(true);
    setError(null);

    (async () => {
      try {
        const result = await loader();
        if (active) {
          setData(result);
          setError(null);
        }
      } catch (err) {
        if (active) {
          setData(null);
          setError(firstError(err));
        }
      } finally {
        if (active) setLoading(false);
      }
    })();

    return () => { active = false; };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [...deps, tick]);

  return { data, loading, error, reload: useCallback(() => setTick((t) => t + 1), []) };
};

/* ------------------------------ primitives ------------------------------- */

export const Panel = ({ title, subtitle, right, children, className = '' }) => (
  <div className={`bg-white rounded-lg shadow ${className}`}>
    {(title || right) && (
      <div className="flex items-start justify-between gap-3 px-5 pt-4 pb-3">
        <div>
          {title && <h2 className="font-semibold text-gray-900">{title}</h2>}
          {subtitle && <p className="text-xs text-gray-500 mt-0.5">{subtitle}</p>}
        </div>
        {right}
      </div>
    )}
    <div className="px-5 pb-5">{children}</div>
  </div>
);

export const Field = ({ label, children }) => (
  <div>
    <label className={LABEL}>{label}</label>
    {children}
  </div>
);

export const ErrorBanner = ({ error }) =>
  error ? (
    <div className="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{error}</div>
  ) : null;

export const Empty = ({ message = 'Nothing to show for this period.' }) => (
  <div className="px-4 py-10 text-center text-gray-500">
    <Inbox className="w-6 h-6 mx-auto mb-2 text-gray-300" />
    <p className="text-sm">{message}</p>
  </div>
);

export const RunButton = ({ onClick, loading, children = 'Run report' }) => (
  <button type="button" onClick={onClick} disabled={loading} className={BTN_PRIMARY}>
    <PlayCircle className="w-4 h-4 mr-2" />
    {loading ? 'Running…' : children}
  </button>
);

export const RefreshButton = ({ onClick }) => (
  <button type="button" onClick={onClick} className={BTN_SECONDARY} title="Refresh">
    <RefreshCw className="w-4 h-4 mr-2" /> Refresh
  </button>
);

/** A formatted amount, rendered as a right-aligned table cell. */
export const Amount = ({ value, bold = false, className = '' }) => (
  <td className={`px-3 py-2 whitespace-nowrap ${bold ? 'font-semibold text-gray-900' : 'text-gray-800'} text-right ${className}`}>
    {value === null || value === undefined ? '—' : formatMoney(value)}
  </td>
);

/** Label cell used by the grouped P&L / Balance Sheet tables. */
export const Cell = ({ children, className = '' }) => (
  <td className={`px-3 py-2 ${className}`}>{children}</td>
);

/**
 * Render the `[{group, total, accounts:[{name, amount}]}]` shape the P&L and
 * Balance Sheet both return — one block per account group with a subtotal.
 */
export const GroupedAccounts = ({ groups = [] }) => {
  if (groups.length === 0) return null;

  return (
    <tbody className="divide-y divide-gray-100">
      {groups.map((group) => (
        <React.Fragment key={group.group}>
          <tr className="bg-gray-50">
            <Cell className="font-medium text-gray-700">
              {group.group}
              {/* P&L only: marks the groups that make up gross profit. Balance
                  Sheet groups carry no `direct` key, so nothing renders there. */}
              {group.direct !== undefined && (
                <span className={`ml-2 text-[10px] font-semibold uppercase px-1.5 py-0.5 rounded ${
                  group.direct ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500'
                }`}>
                  {group.direct ? 'Direct' : 'Indirect'}
                </span>
              )}
            </Cell>
            <Amount value={group.total} bold />
          </tr>
          {group.accounts.map((account) => (
            <tr key={`${group.group}-${account.ledger_id}`}>
              <Cell className="pl-8 text-sm text-gray-600">{account.name}</Cell>
              <Amount value={account.amount} />
            </tr>
          ))}
        </React.Fragment>
      ))}
    </tbody>
  );
};

/** A run of headline figures (income, totals, profit …). */
export const SummaryTiles = ({ tiles }) => (
  <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
    {tiles.map((tile) => (
      <div key={tile.label} className={`rounded-lg p-3 ${tile.tone || 'bg-gray-50 text-gray-900'}`}>
        <div className="text-[11px] uppercase tracking-wide opacity-70">{tile.label}</div>
        <div className="text-lg font-semibold mt-1">{tile.value}</div>
      </div>
    ))}
  </div>
);

export const TableHead = ({ columns }) => (
  <thead className="bg-gray-50">
    <tr>
      {columns.map((column) => (
        <th
          key={column.label}
          className={`px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider ${column.align === 'right' ? 'text-right' : 'text-left'}`}
        >
          {column.label}
        </th>
      ))}
    </tr>
  </thead>
);
