import React, { useState } from 'react';
import { financeReportService } from '../../../services/accountingService';
import { formatMoney } from '../../../utils/finance';
import {
  INPUT, Panel, Field, ErrorBanner, Empty, RunButton, RefreshButton,
  Amount, SummaryTiles, TableHead, useReport,
} from './shared';

const formatDate = (value) => (value ? new Date(value).toLocaleDateString('en-GB') : '—');

/**
 * Cash / Bank Book: movement of every cash and bank account over a period.
 * Selecting an account reveals its transaction lines underneath.
 */
const CashBankBookReport = () => {
  const [filters, setFilters] = useState({ from: '', to: '' });
  const [ledgerId, setLedgerId] = useState(null);

  const { data, loading, error, reload } = useReport(
    () => financeReportService.cashBankBook({ ...filters, ledger_id: ledgerId || undefined }),
    [filters.from, filters.to, ledgerId]
  );

  return (
    <div className="space-y-4">
      <ErrorBanner error={error} />

      <Panel
        title="Cash / Bank Book"
        subtitle={
          data
            ? `${data.period.from || 'Start of books'} → ${data.period.to} · ${data.accounts.length} accounts`
            : 'Movement of cash and bank accounts.'
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
        {loading && !data && <Empty message="Loading cash / bank book…" />}

        {data && (
          <>
            <SummaryTiles
              tiles={[
                { label: 'Opening', value: formatMoney(data.totals.opening) },
                { label: 'Money in', value: formatMoney(data.totals.debit), tone: 'bg-green-50 text-green-900' },
                { label: 'Money out', value: formatMoney(data.totals.credit), tone: 'bg-amber-50 text-amber-900' },
                { label: 'Closing', value: formatMoney(data.totals.closing), tone: 'bg-gray-900 text-white' },
              ]}
            />

            <div className="overflow-x-auto mt-4 -mx-5 px-5">
              <table className="min-w-full divide-y divide-gray-200 text-sm">
                <TableHead
                  columns={[
                    { label: 'Account' },
                    { label: 'Opening', align: 'right' },
                    { label: 'Debits', align: 'right' },
                    { label: 'Credits', align: 'right' },
                    { label: 'Closing', align: 'right' },
                  ]}
                />
                <tbody className="divide-y divide-gray-100">
                  {data.accounts.map((account) => {
                    const active = ledgerId === account.ledger_id;
                    return (
                      <tr
                        key={account.ledger_id}
                        onClick={() => setLedgerId(active ? null : account.ledger_id)}
                        className={`cursor-pointer ${active ? 'bg-orange-50' : 'hover:bg-gray-50'}`}
                      >
                        <td className="px-3 py-2">
                          <div className="font-medium text-gray-800 flex items-center gap-2">
                            {account.name}
                            <span className={`text-[10px] font-semibold uppercase px-1.5 py-0.5 rounded ${account.is_bank ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600'}`}>
                              {account.is_bank ? 'Bank' : 'Cash'}
                            </span>
                          </div>
                          <div className="text-[11px] text-gray-400">
                            {account.account_number ? `A/C ${account.account_number}` : (account.bank_name || '—')}
                          </div>
                        </td>
                        <Amount value={account.opening} />
                        <Amount value={account.debit} />
                        <Amount value={account.credit} />
                        <Amount value={account.closing} bold />
                      </tr>
                    );
                  })}
                  {data.accounts.length === 0 && (
                    <tr>
                      <td colSpan={5} className="px-4 py-8 text-center text-gray-500">
                        No cash or bank ledgers found.
                      </td>
                    </tr>
                  )}
                </tbody>
                {data.accounts.length > 0 && (
                  <tfoot className="bg-gray-50 font-semibold text-gray-900">
                    <tr>
                      <td className="px-3 py-3">Totals</td>
                      <Amount value={data.totals.opening} bold />
                      <Amount value={data.totals.debit} bold />
                      <Amount value={data.totals.credit} bold />
                      <Amount value={data.totals.closing} bold />
                    </tr>
                  </tfoot>
                )}
              </table>
            </div>

            {data.ledger && (
              <div className="mt-5">
                <div className="flex items-center justify-between mb-2">
                  <h3 className="text-sm font-semibold text-gray-800">
                    Transactions — {data.ledger.name}
                  </h3>
                  <button
                    type="button"
                    className="text-sm text-orange-600 hover:underline"
                    onClick={() => setLedgerId(null)}
                  >
                    Clear selection
                  </button>
                </div>

                <div className="overflow-x-auto rounded-lg border border-gray-200">
                  <table className="min-w-full divide-y divide-gray-200 text-sm">
                    <TableHead
                      columns={[
                        { label: 'Date' },
                        { label: 'Voucher' },
                        { label: 'Particulars' },
                        { label: 'Debit', align: 'right' },
                        { label: 'Credit', align: 'right' },
                        { label: 'Balance', align: 'right' },
                      ]}
                    />
                    <tbody className="divide-y divide-gray-100">
                      {data.entries.map((entry) => (
                        <tr key={entry.id}>
                          <td className="px-3 py-2 text-gray-600 whitespace-nowrap">{formatDate(entry.voucher_date)}</td>
                          <td className="px-3 py-2">
                            <div className="font-medium text-gray-800">{entry.voucher_no}</div>
                            <div className="text-[11px] uppercase text-gray-400">{entry.voucher_type}</div>
                          </td>
                          <td className="px-3 py-2 text-gray-700 max-w-[18rem] truncate">{entry.narration || '—'}</td>
                          <Amount value={entry.debit} />
                          <Amount value={entry.credit} />
                          <td className="px-3 py-2 text-right font-medium text-gray-900 whitespace-nowrap">
                            {formatMoney(Math.abs(entry.balance))}{' '}
                            <span className="text-[11px] text-gray-400">{entry.balance_side}</span>
                          </td>
                        </tr>
                      ))}
                      {data.entries.length === 0 && (
                        <tr>
                          <td colSpan={6} className="px-4 py-6 text-center text-gray-500">No transactions in this period.</td>
                        </tr>
                      )}
                    </tbody>
                  </table>
                </div>
              </div>
            )}
          </>
        )}
      </Panel>
    </div>
  );
};

export default CashBankBookReport;
