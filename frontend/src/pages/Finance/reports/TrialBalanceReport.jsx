import React, { useState } from 'react';
import { financeReportService } from '../../../services/accountingService';
import { formatMoney } from '../../../utils/finance';
import {
  INPUT, Panel, Field, ErrorBanner, Empty, RunButton, RefreshButton,
  Amount, SummaryTiles, TableHead, useReport,
} from './shared';

/** Trial Balance: every closing balance on its natural side, must tie. */
const TrialBalanceReport = () => {
  const [filters, setFilters] = useState({ to: '' });

  const { data, loading, error, reload } = useReport(
    () => financeReportService.trialBalance(filters),
    [filters.to]
  );

  const isBalanced = data?.is_balanced;

  return (
    <div className="space-y-4">
      <ErrorBanner error={error} />

      <Panel
        title="Trial Balance"
        subtitle={data ? `Closing balances as at ${data.as_of} · ${data.account_count} accounts` : 'Closing balances as at a date.'}
        right={
          <div className="flex flex-wrap items-end gap-3">
            <Field label="As at">
              <input
                type="date"
                value={filters.to}
                onChange={(e) => setFilters({ ...filters, to: e.target.value })}
                className={INPUT}
              />
            </Field>
            <RunButton onClick={reload} loading={loading} />
            <RefreshButton onClick={reload} />
          </div>
        }
      >
        {loading && !data && <Empty message="Loading trial balance…" />}

        {data && (
          <>
            <SummaryTiles
              tiles={[
                { label: 'Total debits', value: formatMoney(data.total_debit), tone: 'bg-blue-50 text-blue-900' },
                { label: 'Total credits', value: formatMoney(data.total_credit), tone: 'bg-amber-50 text-amber-900' },
                { label: 'Difference', value: formatMoney(Math.abs(data.difference)), tone: data.is_balanced ? 'bg-green-50 text-green-900' : 'bg-red-50 text-red-900' },
                { label: 'Status', value: data.is_balanced ? 'Balanced ✓' : 'Not balanced', tone: data.is_balanced ? 'bg-green-100 text-green-900' : 'bg-red-100 text-red-900' },
              ]}
            />

            {!isBalanced && (
              <div className="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                The books do not tie: debits exceed credits (or vice versa) by {formatMoney(Math.abs(data.difference))}.
                Check the opening balances and any vouchers posted outside the balanced set.
              </div>
            )}

            <div className="overflow-x-auto mt-4 -mx-5 px-5">
              <table className="min-w-full divide-y divide-gray-200 text-sm">
                <TableHead
                  columns={[
                    { label: 'Account' },
                    { label: 'Group' },
                    { label: 'Nature' },
                    { label: 'Debit', align: 'right' },
                    { label: 'Credit', align: 'right' },
                  ]}
                />
                <tbody className="divide-y divide-gray-100">
                  {data.rows.map((row) => (
                    <tr key={row.ledger_id}>
                      <td className="px-3 py-2 font-medium text-gray-800 whitespace-nowrap">{row.name}</td>
                      <td className="px-3 py-2 text-gray-600">{row.group || '—'}</td>
                      <td className="px-3 py-2">
                        <span className={`text-[11px] font-semibold uppercase px-2 py-0.5 rounded-full ${
                          row.nature === 'Assets' ? 'bg-blue-100 text-blue-700'
                            : row.nature === 'Liabilities' ? 'bg-amber-100 text-amber-700'
                              : row.nature === 'Income' ? 'bg-green-100 text-green-700'
                                : 'bg-red-100 text-red-700'
                        }`}>
                          {row.nature || '—'}
                        </span>
                      </td>
                      <Amount value={row.debit} />
                      <Amount value={row.credit} />
                    </tr>
                  ))}
                  {data.rows.length === 0 && (
                    <tr>
                      <td colSpan={5} className="px-4 py-8 text-center text-gray-500">No balances to show.</td>
                    </tr>
                  )}
                </tbody>
                {data.rows.length > 0 && (
                  <tfoot className="bg-gray-50 font-semibold text-gray-900">
                    <tr>
                      <td className="px-3 py-3" colSpan={3}>Totals</td>
                      <Amount value={data.total_debit} bold />
                      <Amount value={data.total_credit} bold />
                    </tr>
                  </tfoot>
                )}
              </table>
            </div>
          </>
        )}
      </Panel>
    </div>
  );
};

export default TrialBalanceReport;
