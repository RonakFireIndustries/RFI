import React, { useState } from 'react';
import { financeReportService } from '../../../services/accountingService';
import { formatMoney } from '../../../utils/finance';
import {
  INPUT, Panel, Field, ErrorBanner, Empty, RunButton, RefreshButton,
  Amount, SummaryTiles, TableHead, GroupedAccounts, useReport,
} from './shared';

/** Balance Sheet as at a date: assets vs liabilities, with profit carried in. */
const BalanceSheetReport = () => {
  const [filters, setFilters] = useState({ to: '' });

  const { data, loading, error, reload } = useReport(
    () => financeReportService.balanceSheet(filters),
    [filters.to]
  );

  const isBalanced = data?.is_balanced;

  return (
    <div className="space-y-4">
      <ErrorBanner error={error} />

      <Panel
        title="Balance Sheet"
        subtitle={data ? `As at ${data.as_of}` : 'Assets and liabilities as at a date.'}
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
        {loading && !data && <Empty message="Loading balance sheet…" />}

        {data && (
          <>
            <SummaryTiles
              tiles={[
                { label: 'Total assets', value: formatMoney(data.assets_total), tone: 'bg-blue-50 text-blue-900' },
                { label: 'Total liabilities', value: formatMoney(data.liabilities_total), tone: 'bg-amber-50 text-amber-900' },
                {
                  label: data.profit >= 0 ? 'Profit' : 'Loss',
                  value: formatMoney(Math.abs(data.profit)),
                  tone: data.profit >= 0 ? 'bg-green-50 text-green-900' : 'bg-red-50 text-red-900',
                },
                {
                  label: 'Status',
                  value: isBalanced ? 'Balanced' : `Off by ${formatMoney(Math.abs(data.difference))}`,
                  tone: isBalanced ? 'bg-green-100 text-green-900' : 'bg-red-100 text-red-900',
                },
              ]}
            />

            {!isBalanced && (
              <div className="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                This sheet does not balance. The difference of {formatMoney(Math.abs(data.difference))} usually means
                the opening balances carried into the year don&apos;t tie. A Trial Balance will show where.
              </div>
            )}

            <div className="grid lg:grid-cols-2 gap-6 mt-5">
              {/* Assets */}
              <div className="min-w-0">
                <div className="rounded-t-lg px-4 py-2 font-semibold bg-blue-100 text-blue-900">
                  Assets
                  <span className="ml-2 font-normal text-xs opacity-80">{formatMoney(data.assets_total)}</span>
                </div>
                <table className="min-w-full divide-y divide-gray-200 text-sm border border-t-0 rounded-b-lg">
                  <TableHead columns={[{ label: 'Group' }, { label: 'Amount', align: 'right' }]} />
                  <GroupedAccounts groups={data.assets} />
                  {data.assets.length === 0 && (
                    <tbody>
                      <tr><td colSpan={2} className="px-4 py-6 text-center text-gray-400 text-sm">No asset balances.</td></tr>
                    </tbody>
                  )}
                  <tfoot className="bg-gray-50 font-semibold text-gray-900">
                    <tr>
                      <td className="px-3 py-3">Total assets</td>
                      <Amount value={data.assets_total} bold />
                    </tr>
                  </tfoot>
                </table>
              </div>

              {/* Liabilities + profit */}
              <div className="min-w-0">
                <div className="rounded-t-lg px-4 py-2 font-semibold bg-amber-100 text-amber-900">
                  Liabilities
                  <span className="ml-2 font-normal text-xs opacity-80">{formatMoney(data.liabilities_total)}</span>
                </div>
                <table className="min-w-full divide-y divide-gray-200 text-sm border border-t-0">
                  <TableHead columns={[{ label: 'Group' }, { label: 'Amount', align: 'right' }]} />
                  <GroupedAccounts groups={data.liabilities} />
                  {data.liabilities.length === 0 && (
                    <tbody>
                      <tr><td colSpan={2} className="px-4 py-6 text-center text-gray-400 text-sm">No liability balances.</td></tr>
                    </tbody>
                  )}
                  <tbody className="divide-y divide-gray-100">
                    <tr className={data.profit >= 0 ? 'bg-green-50' : 'bg-red-50'}>
                      <td className="px-3 py-2 font-medium text-gray-700">{data.profit_line}</td>
                      <Amount value={Math.abs(data.profit)} bold />
                    </tr>
                  </tbody>
                  <tfoot className="bg-gray-50 font-semibold text-gray-900">
                    <tr>
                      <td className="px-3 py-3">Total liabilities + {data.profit >= 0 ? 'profit' : 'loss'}</td>
                      <Amount value={data.liabilities_grand_total} bold />
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>

            <div className={`mt-5 rounded-lg px-4 py-3 flex items-center justify-between ${isBalanced ? 'bg-green-100 text-green-900' : 'bg-red-100 text-red-900'}`}>
              <span className="font-semibold">
                {isBalanced ? 'Assets = Liabilities + Profit ✓' : `Unbalanced by ${formatMoney(Math.abs(data.difference))}`}
              </span>
              <span className="text-sm opacity-80">
                {formatMoney(data.assets_total)} = {formatMoney(data.liabilities_grand_total)}
              </span>
            </div>
          </>
        )}
      </Panel>
    </div>
  );
};

export default BalanceSheetReport;
