import React, { useState } from 'react';
import { financeReportService } from '../../../services/accountingService';
import { VOUCHER_TYPES } from '../../../config/vouchers';
import { formatMoney } from '../../../utils/finance';
import {
  INPUT, Panel, Field, ErrorBanner, Empty, RunButton, RefreshButton,
  Amount, SummaryTiles, TableHead, useReport, formatDate,
} from './shared';

/**
 * Day Book — every posted line in a period, grouped visually by voucher.
 * This is the "what actually got posted" register.
 */
const DayBookReport = () => {
  const [filters, setFilters] = useState({ from: '', to: '', voucher_type: '' });

  const { data, loading, error, reload } = useReport(
    () => financeReportService.dayBook(filters),
    [filters.from, filters.to, filters.voucher_type]
  );

  return (
    <div className="space-y-4">
      <ErrorBanner error={error} />

      <Panel
        title="Day Book"
        subtitle="All posted vouchers and their lines for the selected period."
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
            <Field label="Voucher type">
              <select
                value={filters.voucher_type}
                onChange={(e) => setFilters({ ...filters, voucher_type: e.target.value })}
                className={INPUT}
              >
                <option value="">All types</option>
                {Object.entries(VOUCHER_TYPES).map(([key, config]) => (
                  <option key={key} value={key}>{config.label}</option>
                ))}
              </select>
            </Field>
            <RunButton onClick={reload} loading={loading} />
            <RefreshButton onClick={reload} />
          </div>
        }
      >
        {loading && !data && <Empty message="Loading day book…" />}

        {data && (
          <>
            <SummaryTiles
              tiles={[
                { label: 'Vouchers', value: data.voucher_count },
                { label: 'Lines', value: data.rows.length },
                { label: 'Total debits', value: formatMoney(data.debit_total), tone: 'bg-blue-50 text-blue-900' },
                { label: 'Total credits', value: formatMoney(data.credit_total), tone: 'bg-amber-50 text-amber-900' },
              ]}
            />

            <div className="overflow-x-auto mt-4 -mx-5 px-5">
              <table className="min-w-full divide-y divide-gray-200 text-sm">
                <TableHead
                  columns={[
                    { label: 'Date' },
                    { label: 'Voucher' },
                    { label: 'Particulars' },
                    { label: 'Account' },
                    { label: 'Debit', align: 'right' },
                    { label: 'Credit', align: 'right' },
                  ]}
                />
                <tbody className="divide-y divide-gray-100">
                  {data.rows.map((row, index) => (
                    <tr key={`${row.voucher_id}-${index}`}>
                      <td className="px-3 py-2 text-gray-600 whitespace-nowrap">{formatDate(row.voucher_date)}</td>
                      <td className="px-3 py-2">
                        <div className="font-medium text-gray-800">{row.voucher_no}</div>
                        <div className="text-[11px] uppercase text-gray-400">{row.voucher_type}</div>
                      </td>
                      <td className="px-3 py-2 text-gray-700 max-w-[22rem] truncate" title={row.narration || ''}>
                        <span className={`mr-1.5 text-[11px] font-semibold ${row.entry_type === 'Dr' ? 'text-blue-600' : 'text-amber-600'}`}>
                          {row.entry_type === 'Dr' ? 'Dr' : 'Cr'}
                        </span>
                        {row.narration || row.party_name || '—'}
                      </td>
                      <td className="px-3 py-2 text-gray-700 whitespace-nowrap">{row.ledger_name}</td>
                      <Amount value={row.debit} />
                      <Amount value={row.credit} />
                    </tr>
                  ))}
                  {data.rows.length === 0 && (
                    <tr>
                      <td colSpan={6} className="px-4 py-8 text-center text-gray-500">
                        No vouchers were posted in this period.
                      </td>
                    </tr>
                  )}
                </tbody>
                {data.rows.length > 0 && (
                  <tfoot className="bg-gray-50 font-semibold text-gray-900">
                    <tr>
                      <td className="px-3 py-3" colSpan={4}>Totals</td>
                      <Amount value={data.debit_total} bold />
                      <Amount value={data.credit_total} bold />
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

export default DayBookReport;
