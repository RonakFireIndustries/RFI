import React, { useEffect, useState } from 'react';
import { financeReportService, ledgerService } from '../../../services/accountingService';
import { formatMoney } from '../../../utils/finance';
import {
  INPUT, Panel, Field, ErrorBanner, Empty, RunButton, RefreshButton,
  Amount, SummaryTiles, TableHead, useReport, formatDate,
} from './shared';

const Balance = ({ value, side }) => (
  <td className="px-3 py-2 text-right whitespace-nowrap text-gray-900 font-medium">
    {formatMoney(Math.abs(Number(value || 0)))}{' '}
    <span className="text-[11px] text-gray-400">{side}</span>
  </td>
);

/** Ledger / Account statement: opening, every line, running and closing balance. */
const LedgerStatementReport = () => {
  const [ledgers, setLedgers] = useState([]);
  const [ledgerId, setLedgerId] = useState(null);
  const [filters, setFilters] = useState({ from: '', to: '' });

  useEffect(() => {
    let active = true;
    ledgerService
      .list()
      .then((list) => {
        if (!active) return;
        setLedgers(list || []);
        if (list?.length) setLedgerId((current) => current ?? list[0].id);
      })
      .catch(() => {});
    return () => { active = false; };
  }, []);

  // Until the ledger list resolves there is nothing to ask for — never fetch
  // `ledger/null`, which would just come back as a 404.
  const { data, loading, error, reload } = useReport(
    () => (ledgerId ? financeReportService.ledgerStatement(ledgerId, filters) : null),
    [ledgerId, filters.from, filters.to]
  );

  const run = () => reload();

  if (ledgers.length === 0) {
    return <Empty message="No ledgers found. Create them under Chart of Accounts first." />;
  }

  return (
    <div className="space-y-4">
      <ErrorBanner error={error} />

      <Panel
        title={data?.ledger?.name || 'Account statement'}
        subtitle={
          data
            ? `${data.ledger.group || '—'} · ${data.period.from || 'Start of books'} → ${data.period.to}`
            : 'Select an account and a period.'
        }
        right={
          <div className="flex flex-wrap items-end gap-3">
            <Field label="Account">
              <select value={ledgerId ?? ''} onChange={(e) => setLedgerId(Number(e.target.value))} className={INPUT}>
                {ledgerId === null && <option value="" disabled>Select an account…</option>}
                {ledgers.map((ledger) => (
                  <option key={ledger.id} value={ledger.id}>{ledger.name}</option>
                ))}
              </select>
            </Field>
            <Field label="From">
              <input type="date" value={filters.from} placeholder="FY start"
                onChange={(e) => setFilters({ ...filters, from: e.target.value })} className={INPUT} />
            </Field>
            <Field label="To">
              <input type="date" value={filters.to} placeholder="Today"
                onChange={(e) => setFilters({ ...filters, to: e.target.value })} className={INPUT} />
            </Field>
            <RunButton onClick={run} loading={loading} />
            <RefreshButton onClick={run} />
          </div>
        }
      >
        {loading && !data && <Empty message="Loading statement…" />}
        {data && (
          <>
            <SummaryTiles
              tiles={[
                { label: 'Opening balance', value: `${formatMoney(Math.abs(data.opening_balance))} ${data.opening_side}` },
                { label: 'Total debits', value: formatMoney(data.debit_total), tone: 'bg-blue-50 text-blue-900' },
                { label: 'Total credits', value: formatMoney(data.credit_total), tone: 'bg-amber-50 text-amber-900' },
                { label: 'Closing balance', value: `${formatMoney(Math.abs(data.closing_balance))} ${data.closing_side}`, tone: 'bg-green-50 text-green-900' },
              ]}
            />

            <div className="overflow-x-auto mt-4 -mx-5 px-5">
              <table className="min-w-full divide-y divide-gray-200 text-sm">
                <TableHead columns={[
                  { label: 'Date' }, { label: 'Voucher' }, { label: 'Particulars' },
                  { label: 'Debit', align: 'right' }, { label: 'Credit', align: 'right' },
                  { label: 'Balance', align: 'right' },
                ]} />
                <tbody className="divide-y divide-gray-100">
                  {data.entries.map((entry) => (
                    <tr key={entry.id}>
                      <td className="px-3 py-2 text-gray-600 whitespace-nowrap">
                        {formatDate(entry.voucher_date)}
                      </td>
                      <td className="px-3 py-2">
                        <div className="font-medium text-gray-800">{entry.voucher_no}</div>
                        <div className="text-[11px] uppercase text-gray-400">{entry.voucher_type}</div>
                      </td>
                      <td className="px-3 py-2 text-gray-700 max-w-[20rem] truncate" title={entry.narration || ''}>
                        {entry.narration || entry.reference_no || '—'}
                      </td>
                      <Amount value={entry.debit} />
                      <Amount value={entry.credit} />
                      <Balance value={entry.balance} side={entry.balance_side} />
                    </tr>
                  ))}
                  {data.entries.length === 0 && (
                    <tr>
                      <td colSpan={6} className="px-4 py-8 text-center text-gray-500">
                        No transactions for this account in the selected period.
                      </td>
                    </tr>
                  )}
                </tbody>
                {data.entries.length > 0 && (
                  <tfoot className="bg-gray-50 font-semibold text-gray-900">
                    <tr>
                      <td className="px-3 py-3" colSpan={3}>Totals</td>
                      <Amount value={data.debit_total} bold />
                      <Amount value={data.credit_total} bold />
                      <Balance value={data.closing_balance} side={data.closing_side} />
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

export default LedgerStatementReport;
