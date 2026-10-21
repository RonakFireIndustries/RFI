import { useEffect, useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import { ArrowLeftRight, BarChart3, PackageCheck, Search, Truck, Undo2, X } from 'lucide-react';
import { stockReceiptService } from '../../../services/stockReceiptService';
import PermissionGate from '../../../components/Guards/PermissionGate';

/*
 * Goods receipt / delivery register.
 *
 * The purchase and sales vouchers are the bills; this screen is the warehouse
 * side of the story — every instalment that actually moved, in date order,
 * with the site it landed at and who booked it. Reversals stay in the list
 * struck through, because a receipt that was undone is still an audit fact.
 */

const STATUS_STYLES = {
  active: 'bg-green-50 text-green-700 border-green-200',
  reversed: 'bg-gray-100 text-gray-500 border-gray-200',
};

const qty = (value) => {
  const number = Number(value);
  if (!Number.isFinite(number)) return '0';
  return String(Number(number.toFixed(3)));
};

const firstError = (err) => {
  const errors = err?.response?.data?.data?.errors || err?.response?.data?.errors;
  if (errors) return Object.values(errors)[0]?.[0];
  return err?.response?.data?.data?.message
    || err?.response?.data?.message
    || 'Could not load the receipt register.';
};

const today = () => new Date().toISOString().split('T')[0];

const StockReceiptsPage = () => {
  const [rows, setRows] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');
  const [from, setFrom] = useState('');
  const [to, setTo] = useState('');

  const load = () => {
    setLoading(true);
    stockReceiptService.list({
      search: search || undefined,
      status: status || undefined,
      from: from || undefined,
      to: to || undefined,
    })
      .then((data) => { setRows(data || []); setError(null); })
      .catch((err) => setError(firstError(err)))
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [status, from, to]);

  useEffect(() => {
    const timer = setTimeout(load, 300);
    return () => clearTimeout(timer);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [search]);

  const reverse = async (receipt) => {
    const reason = window.prompt(
      `Reverse ${receipt.receipt_no}? The stock movement will be undone and the bill will show as outstanding again.`,
      ''
    );
    if (reason === null) return;
    try {
      await stockReceiptService.remove(receipt.id, reason || 'Reversed from the receipt register');
      load();
    } catch (err) {
      window.alert(firstError(err));
    }
  };

  const totals = useMemo(() => {
    const active = rows.filter((row) => row.status === 'active');
    return {
      count: active.length,
      inbound: active.filter((row) => row.source === 'goods_receipt').length,
      outbound: active.filter((row) => row.source === 'delivery').length,
      quantity: active.reduce((sum, row) => sum + Number(row.quantity_total || 0), 0),
    };
  }, [rows]);

  const hasFilters = Boolean(search || status || from || to);
  const clearFilters = () => { setSearch(''); setStatus(''); setFrom(''); setTo(''); };

  return (
    <div className="mx-auto max-w-7xl space-y-5 p-6 pb-10">
      <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Goods Receipts</h1>
          <p className="mt-1 text-sm text-gray-500">
            Every stock movement booked against a purchase or sales voucher
          </p>
        </div>
        <div className="flex flex-wrap items-center gap-2 text-xs">
          <span className="rounded-full border border-green-200 bg-green-50 px-3 py-1 font-medium text-green-700">
            {totals.inbound} received
          </span>
          <span className="rounded-full border border-blue-200 bg-blue-50 px-3 py-1 font-medium text-blue-700">
            {totals.outbound} delivered
          </span>
          <span className="rounded-full border border-gray-200 bg-gray-50 px-3 py-1 font-medium text-gray-600">
            {qty(totals.quantity)} units moved
          </span>
          <Link
            to="/dashboard/accounting/reports/receipt-breakdown"
            className="inline-flex items-center rounded-lg border border-gray-300 px-3 py-1.5 font-medium text-gray-700 hover:bg-gray-50"
          >
            <BarChart3 className="mr-1.5 h-3.5 w-3.5" /> Breakdown report
          </Link>
        </div>
      </div>

      <div className="flex flex-wrap items-end gap-3 rounded-lg bg-white p-4 shadow">
        <div className="relative min-w-[220px] flex-1">
          <label className="mb-1 block text-xs font-medium text-gray-600">Search</label>
          <Search className="pointer-events-none absolute left-3 top-[30px] h-4 w-4 text-gray-400" />
          <input
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Receipt no., bill no., voucher or party…"
            className="w-full rounded-lg border border-gray-300 py-2 pl-9 pr-3 text-sm focus:border-orange-500 focus:ring-orange-500"
          />
        </div>
        <div>
          <label className="mb-1 block text-xs font-medium text-gray-600">Status</label>
          <select
            value={status}
            onChange={(e) => setStatus(e.target.value)}
            className="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-orange-500 focus:ring-orange-500"
          >
            <option value="">All</option>
            <option value="active">Active</option>
            <option value="reversed">Reversed</option>
          </select>
        </div>
        <div>
          <label className="mb-1 block text-xs font-medium text-gray-600">From</label>
          <input
            type="date"
            value={from}
            max={to || today()}
            onChange={(e) => setFrom(e.target.value)}
            className="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-orange-500 focus:ring-orange-500"
          />
        </div>
        <div>
          <label className="mb-1 block text-xs font-medium text-gray-600">To</label>
          <input
            type="date"
            value={to}
            min={from || undefined}
            onChange={(e) => setTo(e.target.value)}
            className="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-orange-500 focus:ring-orange-500"
          />
        </div>
        {hasFilters && (
          <button
            type="button"
            onClick={clearFilters}
            className="inline-flex items-center rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50"
          >
            <X className="mr-1 h-4 w-4" /> Clear
          </button>
        )}
      </div>

      {error && (
        <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{error}</div>
      )}

      <div className="overflow-x-auto rounded-lg bg-white shadow">
        <table className="min-w-full text-sm">
          <thead>
            <tr className="border-b border-gray-100 text-xs uppercase tracking-wider text-gray-500">
              <th className="px-4 py-3 text-left">Receipt #</th>
              <th className="px-4 py-3 text-left">Date</th>
              <th className="px-4 py-3 text-left">Document</th>
              <th className="px-4 py-3 text-left">Party</th>
              <th className="px-4 py-3 text-left">Location</th>
              <th className="px-4 py-3 text-right">Qty</th>
              <th className="px-4 py-3 text-left">Received by</th>
              <th className="px-4 py-3 text-left">Status</th>
              <th className="px-4 py-3 w-16"></th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100">
            {loading && (
              <tr>
                <td colSpan={9} className="px-4 py-10 text-center text-gray-400">Loading receipts…</td>
              </tr>
            )}
            {!loading && rows.length === 0 && (
              <tr>
                <td colSpan={9} className="px-4 py-10 text-center text-gray-500">
                  <PackageCheck className="mx-auto mb-2 h-8 w-8 text-gray-300" />
                  {hasFilters ? 'No receipts match that filter.' : 'No goods have been received or delivered yet.'}
                </td>
              </tr>
            )}
            {!loading && rows.map((row) => {
              const outbound = row.source === 'delivery';
              const reversed = row.status === 'reversed';
              return (
                <tr key={row.id} className={reversed ? 'text-gray-400' : 'hover:bg-gray-50'}>
                  <td className="px-4 py-3">
                    <span className={`font-medium ${reversed ? 'line-through' : 'text-gray-900'}`}>{row.receipt_no}</span>
                    <div className="text-xs text-gray-400">
                      {outbound ? 'Delivery' : 'Goods receipt'}
                    </div>
                  </td>
                  <td className="px-4 py-3 whitespace-nowrap">{row.receipt_date || '—'}</td>
                  <td className="px-4 py-3">
                    {row.voucher_id ? (
                      <span className="inline-flex items-center gap-1">
                        {outbound ? <Truck className="h-3.5 w-3.5 text-blue-500" /> : <ArrowLeftRight className="h-3.5 w-3.5 text-green-500" />}
                        <span className={reversed ? 'line-through' : 'text-gray-700'}>{row.voucher_no || `#${row.voucher_id}`}</span>
                      </span>
                    ) : '—'}
                    {row.supplier_bill_no && (
                      <div className="text-xs text-gray-400">Bill: {row.supplier_bill_no}</div>
                    )}
                  </td>
                  <td className="px-4 py-3">{row.party_name || '—'}</td>
                  <td className="px-4 py-3">{row.location_name || '—'}</td>
                  <td className="px-4 py-3 text-right font-medium">
                    <span className={reversed ? 'line-through text-gray-400' : outbound ? 'text-blue-700' : 'text-green-700'}>
                      {outbound ? '−' : '+'}{qty(row.quantity_total)}
                    </span>
                  </td>
                  <td className="px-4 py-3">{row.received_by_name || '—'}</td>
                  <td className="px-4 py-3">
                    <span className={`inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium capitalize ${STATUS_STYLES[row.status] || STATUS_STYLES.active}`}>
                      {row.status}
                    </span>
                    {reversed && row.reversal_reason && (
                      <div className="mt-0.5 text-xs text-gray-400" title={row.reversal_reason}>
                        {row.reversal_reason}
                      </div>
                    )}
                  </td>
                  <td className="px-4 py-3 text-right">
                    {!reversed && (
                      <PermissionGate module="stock-receipts" action="delete" subject={`receipt ${row.receipt_no}`}>
                        <button
                          type="button"
                          onClick={() => reverse(row)}
                          title="Reverse this receipt"
                          className="text-gray-400 hover:text-red-600"
                        >
                          <Undo2 className="h-4 w-4" />
                        </button>
                      </PermissionGate>
                    )}
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>

      <p className="text-xs text-gray-400">
        Reversed receipts are kept for audit; the stock movement they booked has been undone.
      </p>

      <div className="text-sm">
        <Link to="/dashboard/accounting/purchase" className="text-orange-600 hover:text-orange-700">
          ← Back to purchase vouchers
        </Link>
      </div>
    </div>
  );
};

export default StockReceiptsPage;
