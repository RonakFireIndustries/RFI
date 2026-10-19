import React, { useState } from 'react';
import { financeReportService } from '../../../services/accountingService';
import { formatMoney } from '../../../utils/finance';
import {
  INPUT, BTN_SECONDARY, Panel, Field, ErrorBanner, Empty, RunButton, RefreshButton,
  Amount, SummaryTiles, TableHead, useReport,
} from './shared';

const SIDES = [
  { key: 'receivable', label: 'Receivables' },
  { key: 'payable', label: 'Payables' },
  { key: 'both', label: 'Both' },
];

const AGE_TONES = {
  Current: 'bg-green-100 text-green-800',
  '1-30 days': 'bg-blue-100 text-blue-800',
  '31-60 days': 'bg-amber-100 text-amber-800',
  '61-90 days': 'bg-orange-100 text-orange-800',
  'Over 90 days': 'bg-red-100 text-red-800',
};

const formatDate = (value) => (value ? new Date(value).toLocaleDateString('en-GB') : '—');

/** Bill-wise outstanding with aging, for receivables, payables or both. */
const OutstandingReport = () => {
  const [filters, setFilters] = useState({ side: 'receivable', as_of: '' });

  const { data, loading, error, reload } = useReport(
    () => financeReportService.outstanding(filters),
    [filters.side, filters.as_of]
  );

  const buckets = ['Current', '1-30 days', '31-60 days', '61-90 days', 'Over 90 days'];

  return (
    <div className="space-y-4">
      <ErrorBanner error={error} />

      <Panel
        title="Outstandings"
        subtitle={data ? `As at ${formatDate(data.as_of)} · bill-wise pending balances` : 'Bill-wise pending balances.'}
        right={
          <div className="flex flex-wrap items-end gap-3">
            <Field label="Side">
              <div className="flex rounded-lg border border-gray-300 overflow-hidden w-fit">
                {SIDES.map((side) => (
                  <button
                    key={side.key}
                    type="button"
                    onClick={() => setFilters({ ...filters, side: side.key })}
                    className={`px-3 py-2 text-sm ${filters.side === side.key ? 'bg-orange-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'}`}
                  >
                    {side.label}
                  </button>
                ))}
              </div>
            </Field>
            <Field label="As at">
              <input
                type="date"
                value={filters.as_of}
                onChange={(e) => setFilters({ ...filters, as_of: e.target.value })}
                className={INPUT}
              />
            </Field>
            <RunButton onClick={reload} loading={loading} />
            <RefreshButton onClick={reload} />
          </div>
        }
      >
        {loading && !data && <Empty message="Loading outstandings…" />}

        {data && (
          <>
            <SummaryTiles
              tiles={[
                { label: 'Receivable', value: formatMoney(data.totals.receivable), tone: 'bg-blue-50 text-blue-900' },
                { label: 'Payable', value: formatMoney(data.totals.payable), tone: 'bg-amber-50 text-amber-900' },
                { label: 'Grand total', value: formatMoney(data.grand_total), tone: 'bg-gray-900 text-white' },
                { label: 'Bills / parties', value: `${data.bill_count} / ${data.party_count}` },
              ]}
            />

            <div className="flex flex-wrap gap-2 mt-4">
              {buckets.map((bucket) => (
                <div key={bucket} className="flex-1 min-w-[7rem] rounded-lg border border-gray-200 px-3 py-2">
                  <div className="text-[11px] uppercase tracking-wide text-gray-500">{bucket}</div>
                  <div className="text-sm font-semibold text-gray-900 mt-0.5">{formatMoney(data.aging[bucket] || 0)}</div>
                </div>
              ))}
            </div>

            <div className="overflow-x-auto mt-4 -mx-5 px-5">
              <table className="min-w-full divide-y divide-gray-200 text-sm">
                <TableHead
                  columns={[
                    { label: 'Party' },
                    { label: 'Bill' },
                    { label: 'Date' },
                    { label: 'Due' },
                    { label: 'Bill total', align: 'right' },
                    { label: 'Settled', align: 'right' },
                    { label: 'Balance', align: 'right' },
                    { label: 'Age', align: 'right' },
                  ]}
                />
                <tbody className="divide-y divide-gray-100">
                  {data.bills.map((bill, index) => (
                    <tr key={`${bill.ledger_id}-${bill.bill_type}-${bill.bill_id ?? index}`}>
                      <td className="px-3 py-2 whitespace-nowrap">
                        <span className="font-medium text-gray-800">{bill.party_name}</span>
                        <span className={`ml-2 text-[10px] font-semibold uppercase ${bill.side === 'receivable' ? 'text-blue-600' : 'text-amber-600'}`}>
                          {bill.side}
                        </span>
                      </td>
                      <td className="px-3 py-2 text-gray-700">
                        <div className="font-medium">{bill.bill_name || (bill.date ? `Bill ${formatDate(bill.date)}` : '—')}</div>
                        <div className="text-[11px] uppercase text-gray-400">{bill.bill_type}</div>
                      </td>
                      <td className="px-3 py-2 text-gray-600 whitespace-nowrap">{formatDate(bill.date)}</td>
                      <td className="px-3 py-2 text-gray-600 whitespace-nowrap">
                        {formatDate(bill.due_date)}
                        {bill.overdue && <span className="ml-1 text-[10px] font-semibold text-red-600">OVERDUE</span>}
                      </td>
                      <Amount value={bill.total} />
                      <Amount value={bill.settled} />
                      <Amount value={bill.balance} bold />
                      <td className="px-3 py-2 text-right whitespace-nowrap">
                        <span className={`text-[11px] font-medium px-2 py-0.5 rounded-full ${AGE_TONES[bill.bucket] || 'bg-gray-100 text-gray-700'}`}>
                          {bill.bucket} · {bill.days}d
                        </span>
                      </td>
                    </tr>
                  ))}
                  {data.bills.length === 0 && (
                    <tr>
                      <td colSpan={8} className="px-4 py-8 text-center text-gray-500">
                        Nothing outstanding for this side.
                      </td>
                    </tr>
                  )}
                </tbody>
                {data.bills.length > 0 && (
                  <tfoot className="bg-gray-50 font-semibold text-gray-900">
                    <tr>
                      <td className="px-3 py-3" colSpan={6}>Total outstanding</td>
                      <Amount value={data.grand_total} bold />
                      <td />
                    </tr>
                  </tfoot>
                )}
              </table>
            </div>

            {data.bills.length > 0 && (
              <div className="flex justify-end mt-3">
                <button type="button" className={BTN_SECONDARY} onClick={() => window.print()}>
                  Print
                </button>
              </div>
            )}
          </>
        )}
      </Panel>
    </div>
  );
};

export default OutstandingReport;
