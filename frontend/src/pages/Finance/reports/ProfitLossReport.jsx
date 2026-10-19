import React, { useState } from 'react';
import { financeReportService } from '../../../services/accountingService';
import { formatMoney } from '../../../utils/finance';
import {
  INPUT, Panel, Field, ErrorBanner, Empty, RunButton, RefreshButton,
  Amount, SummaryTiles, TableHead, GroupedAccounts, useReport,
} from './shared';

const ProfitLossSection = ({ title, groups, total, tone }) => (
  <div className="min-w-0">
    <div className={`rounded-t-lg px-4 py-2 font-semibold ${tone}`}>
      {title}
      <span className="ml-2 font-normal text-xs opacity-80">{formatMoney(total)}</span>
    </div>
    <table className="min-w-full divide-y divide-gray-200 text-sm border border-t-0 rounded-b-lg">
      <TableHead columns={[{ label: 'Account group' }, { label: 'Amount', align: 'right' }]} />
      <GroupedAccounts groups={groups} />
      {groups.length === 0 && (
        <tbody>
          <tr>
            <td colSpan={2} className="px-4 py-6 text-center text-gray-400 text-sm">Nothing in this period.</td>
          </tr>
        </tbody>
      )}
      <tfoot className="bg-gray-50 font-semibold text-gray-900">
        <tr>
          <td className="px-3 py-3">Total</td>
          <Amount value={total} bold />
        </tr>
      </tfoot>
    </table>
  </div>
);

/** Profit & Loss for a period: income vs expenses, gross then net. */
const ProfitLossReport = () => {
  const [filters, setFilters] = useState({ from: '', to: '' });

  const { data, loading, error, reload } = useReport(
    () => financeReportService.profitLoss(filters),
    [filters.from, filters.to]
  );

  return (
    <div className="space-y-4">
      <ErrorBanner error={error} />

      <Panel
        title="Profit & Loss"
        subtitle={
          data
            ? `${data.period.from || 'Start of books'} → ${data.period.to}`
            : 'Income and expenses for the selected period.'
        }
        right={
          <div className="flex flex-wrap items-end gap-3">
            <Field label="From">
              <input
                type="date"
                value={filters.from}
                onChange={(e) => setFilters({ ...filters, from: e.target.value })}
                className={INPUT}
              />
            </Field>
            <Field label="To">
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
        {loading && !data && <Empty message="Loading profit & loss…" />}

        {data && (
          <>
            <SummaryTiles
              tiles={[
                { label: 'Gross profit', value: formatMoney(data.gross_profit), tone: data.gross_profit >= 0 ? 'bg-blue-50 text-blue-900' : 'bg-red-50 text-red-900' },
                { label: 'Total income', value: formatMoney(data.total_income), tone: 'bg-green-50 text-green-900' },
                { label: 'Total expenses', value: formatMoney(data.total_expenses), tone: 'bg-amber-50 text-amber-900' },
                {
                  label: data.is_loss ? 'Net loss' : 'Net profit',
                  value: formatMoney(Math.abs(data.net_profit)),
                  tone: data.is_loss ? 'bg-red-100 text-red-900' : 'bg-green-100 text-green-900',
                },
              ]}
            />

            <div className="grid lg:grid-cols-2 gap-6 mt-5">
              <ProfitLossSection
                title="Income"
                groups={data.income}
                total={data.total_income}
                tone="bg-green-100 text-green-900"
              />
              <ProfitLossSection
                title="Expenses"
                groups={data.expenses}
                total={data.total_expenses}
                tone="bg-amber-100 text-amber-900"
              />
            </div>

            <div className={`mt-5 rounded-lg px-4 py-3 flex items-center justify-between ${data.is_loss ? 'bg-red-100 text-red-900' : 'bg-green-100 text-green-900'}`}>
              <span className="font-semibold">{data.is_loss ? 'Net loss for the period' : 'Net profit for the period'}</span>
              <span className="text-lg font-semibold">{formatMoney(Math.abs(data.net_profit))}</span>
            </div>
          </>
        )}
      </Panel>
    </div>
  );
};

export default ProfitLossReport;
