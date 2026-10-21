import React, { useState } from 'react';
import { Download } from 'lucide-react';
import { financeReportService } from '../../../services/accountingService';
import { VOUCHER_TYPES } from '../../../config/vouchers';
import { formatMoney } from '../../../utils/finance';
import {
  INPUT, Panel, Field, ErrorBanner, Empty, RunButton, RefreshButton, BTN_SECONDARY,
  SummaryTiles, TableHead, useReport, formatDate,
} from './shared';

/*
 * Receipt Breakdown — the warehouse ledger, by product.
 *
 * The receipt register answers "what was booked, when"; this answers what the
 * storekeeper and the accountant both actually ask: how much of each product
 * came in and went out in a period, what it was worth, and how much of the
 * period's billing is still outstanding. Everything is derived from active
 * receipt lines, so a reversed receipt never inflates a total.
 */

const qty = (value) => {
  const number = Number(value);
  if (!Number.isFinite(number)) return '0';
  return String(Number(number.toFixed(3)));
};

/** Escape one CSV cell: wrap in quotes, double any embedded quotes. */
const csvCell = (value) => `"${String(value ?? '').replace(/"/g, '""')}"`;

const downloadCsv = (filename, header, rows) => {
  const csv = [header, ...rows].map((row) => row.map(csvCell).join(',')).join('\r\n');
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = filename;
  link.click();
  URL.revokeObjectURL(url);
};

const STATUS_STYLES = {
  pending: 'bg-amber-50 text-amber-700',
  partial: 'bg-blue-50 text-blue-700',
  complete: 'bg-green-50 text-green-700',
};

const ReceiptBreakdownReport = () => {
  const [filters, setFilters] = useState({ from: '', to: '', voucher_type: '' });

  const { data, loading, error, reload } = useReport(
    () => financeReportService.receiptBreakdown(filters),
    [filters.from, filters.to, filters.voucher_type]
  );

  const exportProducts = () => {
    if (!data) return;
    downloadCsv(
      `receipt-breakdown-${data.period.to || 'all'}.csv`,
      ['Product', 'SKU', 'Received qty', 'Received value', 'Delivered qty', 'Delivered value', 'Receipts'],
      data.products.map((row) => [
        row.product_name, row.sku || '',
        qty(row.inbound_quantity), row.inbound_value,
        qty(row.outbound_quantity), row.outbound_value, row.receipt_count,
      ])
    );
  };

  const periodLabel = data
    ? `${data.period.from ? formatDate(data.period.from) : 'start'} → ${formatDate(data.period.to)}`
    : 'Movements for a period.';

  return (
    <div className="space-y-4">
      <ErrorBanner error={error} />

      <Panel
        title="Receipt Breakdown"
        subtitle={data ? `${periodLabel} · ${data.totals.receipts} receipts` : 'Movements for a period.'}
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
                <option value="">Goods in &amp; out</option>
                <option value="purchase">Purchases (received)</option>
                <option value="sales">Sales (delivered)</option>
              </select>
            </Field>
            <RunButton onClick={reload} loading={loading} />
            <RefreshButton onClick={reload} />
            <button type="button" onClick={exportProducts} disabled={!data || data.products.length === 0} className={BTN_SECONDARY}>
              <Download className="w-4 h-4 mr-2" /> CSV
            </button>
          </div>
        }
      >
        {loading && !data && <Empty message="Loading receipt breakdown…" />}

        {data && (
          <>
            <SummaryTiles
              tiles={[
                { label: 'Receipts', value: data.totals.receipts },
                { label: 'Received', value: `${qty(data.totals.inbound_quantity)} · ${formatMoney(data.totals.inbound_value)}`, tone: 'bg-green-50 text-green-900' },
                { label: 'Delivered', value: `${qty(data.totals.outbound_quantity)} · ${formatMoney(data.totals.outbound_value)}`, tone: 'bg-blue-50 text-blue-900' },
                { label: 'Net in hand', value: qty(data.totals.net_quantity), tone: 'bg-gray-50 text-gray-900' },
              ]}
            />

            <h3 className="mt-5 mb-2 text-sm font-semibold text-gray-800">By product</h3>
            <div className="overflow-x-auto -mx-5 px-5">
              <table className="min-w-full divide-y divide-gray-200 text-sm">
                <TableHead
                  columns={[
                    { label: 'Product' },
                    { label: 'SKU' },
                    { label: 'Received', align: 'right' },
                    { label: 'Delivered', align: 'right' },
                    { label: 'Receipts', align: 'right' },
                  ]}
                />
                <tbody className="divide-y divide-gray-100">
                  {data.products.map((row) => (
                    <tr key={row.product_id ?? 'unknown'}>
                      <td className="px-3 py-2 font-medium text-gray-800">{row.product_name}</td>
                      <td className="px-3 py-2 text-gray-500">{row.sku || '—'}</td>
                      <td className="px-3 py-2 text-right">
                        {Number(row.inbound_quantity) > 0 ? (
                          <div>
                            <div className="text-gray-900">{qty(row.inbound_quantity)}</div>
                            <div className="text-xs text-gray-500">{formatMoney(row.inbound_value)}</div>
                          </div>
                        ) : <span className="text-gray-400">—</span>}
                      </td>
                      <td className="px-3 py-2 text-right">
                        {Number(row.outbound_quantity) > 0 ? (
                          <div>
                            <div className="text-gray-900">{qty(row.outbound_quantity)}</div>
                            <div className="text-xs text-gray-500">{formatMoney(row.outbound_value)}</div>
                          </div>
                        ) : <span className="text-gray-400">—</span>}
                      </td>
                      <td className="px-3 py-2 text-right text-gray-500">{row.receipt_count}</td>
                    </tr>
                  ))}
                  {data.products.length === 0 && (
                    <tr>
                      <td colSpan={5} className="px-4 py-8 text-center text-gray-500">
                        No goods were received or delivered in this period.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>

            {data.parties.length > 0 && (
              <>
                <h3 className="mt-6 mb-2 text-sm font-semibold text-gray-800">By party</h3>
                <div className="overflow-x-auto -mx-5 px-5">
                  <table className="min-w-full divide-y divide-gray-200 text-sm">
                    <TableHead
                      columns={[
                        { label: 'Party' },
                        { label: 'Received', align: 'right' },
                        { label: 'Delivered', align: 'right' },
                        { label: 'Receipts', align: 'right' },
                      ]}
                    />
                    <tbody className="divide-y divide-gray-100">
                      {data.parties.map((row) => (
                        <tr key={row.party_ledger_id}>
                          <td className="px-3 py-2 text-gray-800">{row.party_name}</td>
                          <td className="px-3 py-2 text-right text-gray-700">{Number(row.inbound_quantity) > 0 ? qty(row.inbound_quantity) : <span className="text-gray-400">—</span>}</td>
                          <td className="px-3 py-2 text-right text-gray-700">{Number(row.outbound_quantity) > 0 ? qty(row.outbound_quantity) : <span className="text-gray-400">—</span>}</td>
                          <td className="px-3 py-2 text-right text-gray-500">{row.receipt_count}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </>
            )}

            <h3 className="mt-6 mb-2 text-sm font-semibold text-gray-800">Still outstanding</h3>
            <p className="mb-2 text-xs text-gray-500">
              Billed in the period but not yet fully received (or delivered).
            </p>
            <div className="overflow-x-auto -mx-5 px-5">
              <table className="min-w-full divide-y divide-gray-200 text-sm">
                <TableHead
                  columns={[
                    { label: 'Document' },
                    { label: 'Date' },
                    { label: 'Party' },
                    { label: 'Ordered', align: 'right' },
                    { label: 'Moved', align: 'right' },
                    { label: 'Pending', align: 'right' },
                    { label: 'Status' },
                  ]}
                />
                <tbody className="divide-y divide-gray-100">
                  {data.outstanding.map((row) => (
                    <tr key={row.voucher_id}>
                      <td className="px-3 py-2">
                        <div className="font-medium text-gray-800">{row.voucher_no}</div>
                        <div className="text-[11px] uppercase text-gray-400">{row.voucher_type}</div>
                      </td>
                      <td className="px-3 py-2 text-gray-600 whitespace-nowrap">{formatDate(row.voucher_date)}</td>
                      <td className="px-3 py-2 text-gray-700">{row.party_name || '—'}</td>
                      <td className="px-3 py-2 text-right text-gray-700">{qty(row.ordered_quantity)}</td>
                      <td className="px-3 py-2 text-right text-gray-700">{qty(row.moved_quantity)}</td>
                      <td className="px-3 py-2 text-right font-medium text-amber-700">{qty(row.pending_quantity)}</td>
                      <td className="px-3 py-2">
                        <span className={`rounded-full px-2 py-0.5 text-[11px] font-semibold capitalize ${STATUS_STYLES[row.status] || 'bg-gray-100 text-gray-700'}`}>
                          {row.status}
                        </span>
                      </td>
                    </tr>
                  ))}
                  {data.outstanding.length === 0 && (
                    <tr>
                      <td colSpan={7} className="px-4 py-8 text-center text-gray-500">
                        Everything billed in this period has been fully received.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </>
        )}
      </Panel>
    </div>
  );
};

export default ReceiptBreakdownReport;
