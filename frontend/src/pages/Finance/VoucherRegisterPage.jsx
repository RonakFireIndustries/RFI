import React, { useEffect, useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Plus, Edit2, Trash2, Search, RefreshCw, FileText, PackageCheck,
} from 'lucide-react';
import { useVoucherStore } from '../../store/accountingStore';
import PermissionGate from '../../components/Guards/PermissionGate';
import VoucherShortcutLegend from '../../components/Accounting/VoucherShortcutLegend';
import StockStatusBadge from '../../components/Accounting/StockStatusBadge';
import StockReceiptModal from '../../components/Accounting/StockReceiptModal';
import { useStockReceiptStore } from '../../store/stockReceiptStore';
import { VOUCHER_TYPES, tracksStock } from '../../config/vouchers';
import { formatMoney } from '../../utils/finance';
import { formatDate } from '../../utils/date';

const STATUSES = ['draft', 'posted', 'cancelled'];

/*
 * Which slice of the stock story the user is looking at. The stock status is
 * derived server-side, so this only ever filters what came back — there is no
 * second source of truth to keep in step.
 */
const STOCK_FILTERS = [
  { value: '', label: 'All goods' },
  { value: 'pending', label: 'Not received' },
  { value: 'partial', label: 'Partially received' },
  { value: 'complete', label: 'Fully received' },
];

const OUTBOUND_STOCK_FILTERS = [
  { value: '', label: 'All goods' },
  { value: 'pending', label: 'Not delivered' },
  { value: 'partial', label: 'Partially delivered' },
  { value: 'complete', label: 'Fully delivered' },
];

const fmtDate = (d) => formatDate(d, '--');

const today = () => new Date().toISOString().split('T')[0];

const STATUS_BADGE = {
  posted: 'bg-green-50 text-green-700',
  draft: 'bg-amber-50 text-amber-700',
  cancelled: 'bg-rose-50 text-rose-700',
};

const inputClass = 'w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 text-sm focus:ring-orange-500 focus:border-orange-500';

const firstError = (err) => {
  const errors = err?.response?.data?.data?.errors || err?.response?.data?.errors;
  if (errors) return Object.values(errors)[0]?.[0];
  return err?.response?.data?.data?.message || err?.response?.data?.message || 'Something went wrong.';
};

/** One-line description of what the voucher touched. */
const describe = (voucher) => {
  if (voucher.party?.name) return voucher.party.name;
  const names = (voucher.entries || []).map((e) => e.ledger?.name).filter(Boolean);
  return names.join(' / ') || '—';
};

/**
 * Voucher register for one Tally voucher type. The same page renders Contra,
 * Payment, Receipt and Journal, driven by the `voucherType` prop.
 *
 * Purchase and sales registers additionally carry the warehouse column: the
 * receipt count and the derived receive / deliver status, with a Receive Stock
 * action that opens the delivery form without leaving the list.
 */
const VoucherRegisterPage = ({ voucherType }) => {
  const navigate = useNavigate();
  const { items, loading, saving, error, fetchItems, deleteItem } = useVoucherStore();
  const receipts = useStockReceiptStore();
  const config = VOUCHER_TYPES[voucherType] || { label: voucherType, module: voucherType };
  const outbound = config.stock === 'out';
  const tracks = tracksStock(voucherType);

  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [stockFilter, setStockFilter] = useState('');
  const [from, setFrom] = useState('');
  const [to, setTo] = useState('');
  const [receiptTarget, setReceiptTarget] = useState(null);

  const load = () => fetchItems({
    voucher_type: voucherType,
    search: search || undefined,
    status: statusFilter || undefined,
    from: from || undefined,
    to: to || undefined,
  }).catch(() => {});

  useEffect(() => {
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [voucherType, statusFilter, from, to]);

  useEffect(() => {
    const timer = setTimeout(load, 300);
    return () => clearTimeout(timer);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [search]);

  const visible = useMemo(() => {
    if (!stockFilter) return items;
    return items.filter((voucher) => voucher.stock?.status === stockFilter);
  }, [items, stockFilter]);

  const pendingCount = useMemo(
    () => items.filter((voucher) => ['pending', 'partial'].includes(voucher.stock?.status)).length,
    [items]
  );

  const totalAmount = useMemo(
    () => visible.reduce((sum, v) => sum + Number(v.amount || 0), 0),
    [visible]
  );

  const remove = async (voucher) => {
    if (!window.confirm(`Delete voucher ${voucher.voucher_no}? This cannot be undone.`)) return;
    try {
      await deleteItem(voucher.id);
    } catch (err) {
      window.alert(firstError(err));
    }
  };

  const openNew = () => navigate(`/dashboard/accounting/vouchers/new/${voucherType}`);
  const openEdit = (voucher) => navigate(`/dashboard/accounting/vouchers/${voucher.id}/edit/${voucher.voucher_type}`);

  const openReceipts = async (voucher) => {
    try {
      const summary = await receipts.fetchForVoucher(voucher.id);
      setReceiptTarget({ voucher, summary });
    } catch (err) {
      window.alert(firstError(err));
    }
  };

  const closeReceipts = () => setReceiptTarget(null);

  const submitReceipt = async (payload) => {
    const voucher = receiptTarget?.voucher;
    if (!voucher) return;
    await receipts.createReceipt(voucher.id, payload);
    closeReceipts();
    // The register's own stock column came from the list payload, so refetch it.
    load();
  };

  const stockFilters = outbound ? OUTBOUND_STOCK_FILTERS : STOCK_FILTERS;

  return (
    <div className="p-6 max-w-7xl mx-auto">
      <div className="flex flex-wrap justify-between items-center gap-3 mb-6">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">{config.label} Vouchers</h1>
          <p className="text-sm text-gray-500">
            {visible.length} voucher{visible.length === 1 ? '' : 's'} · {formatMoney(totalAmount)} total
            {tracks && pendingCount > 0 && (
              <span className="ml-2 text-amber-700">
                · {pendingCount} awaiting {outbound ? 'delivery' : 'receipt'}
              </span>
            )}
          </p>
        </div>
        <PermissionGate module={config.module} action="create" subject={`${config.label.toLowerCase()} vouchers`}>
          <button onClick={openNew} className="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg flex items-center shadow-sm transition-colors">
            <Plus className="w-4 h-4 mr-2" /> New {config.label}
          </button>
        </PermissionGate>
      </div>

      <VoucherShortcutLegend />

      <div className="flex flex-wrap gap-3 mb-4">
        <div className="relative flex-1 min-w-[220px]">
          <Search className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search voucher no, reference, narration…" className={`${inputClass} pl-9`} />
        </div>
        <select value={statusFilter} onChange={(e) => setStatusFilter(e.target.value)} className={`${inputClass} max-w-[160px]`}>
          <option value="">All statuses</option>
          {STATUSES.map((s) => <option key={s} value={s}>{s}</option>)}
        </select>
        {tracks && (
          <select value={stockFilter} onChange={(e) => setStockFilter(e.target.value)} className={`${inputClass} max-w-[200px]`}>
            {stockFilters.map((option) => (
              <option key={option.value} value={option.value}>{option.label}</option>
            ))}
          </select>
        )}
        <input type="date" value={from} onChange={(e) => setFrom(e.target.value)} className={`${inputClass} max-w-[160px]`} />
        <input type="date" value={to} onChange={(e) => setTo(e.target.value)} className={`${inputClass} max-w-[160px]`} />
        <button onClick={load} className="px-3 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 inline-flex items-center text-sm">
          <RefreshCw className={`w-4 h-4 mr-1 ${loading ? 'animate-spin' : ''}`} /> Refresh
        </button>
      </div>

      {error && (
        <div className="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          Could not load vouchers. You may not have access.
        </div>
      )}

      <div className="bg-white rounded-lg shadow overflow-hidden">
        <div className="overflow-x-auto max-w-full">
          <table className="min-w-full divide-y divide-gray-200 text-sm">
            <thead className="bg-gray-50">
              <tr>
                {['Date', 'Voucher No', 'Particulars', 'Reference', 'Amount', 'Status'].map((h) => (
                  <th key={h} className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{h}</th>
                ))}
                {tracks && (
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {outbound ? 'Delivery' : 'Receipt'}
                  </th>
                )}
                <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {visible.map((v) => (
                <tr key={v.id} className="hover:bg-gray-50">
                  <td className="px-4 py-3 whitespace-nowrap text-gray-700">{fmtDate(v.voucher_date)}</td>
                  <td className="px-4 py-3 whitespace-nowrap font-medium text-gray-900">{v.voucher_no}</td>
                  <td className="px-4 py-3 text-gray-700">{describe(v)}</td>
                  <td className="px-4 py-3 text-gray-500">{v.reference_no || '—'}</td>
                  <td className="px-4 py-3 whitespace-nowrap text-right font-medium text-gray-900">{formatMoney(v.amount)}</td>
                  <td className="px-4 py-3">
                    <span className={`px-2 py-1 rounded-full text-xs font-semibold ${STATUS_BADGE[v.status] || 'bg-gray-100 text-gray-700'}`}>{v.status}</span>
                  </td>
                  {tracks && (
                    <td className="px-4 py-3">
                      <div className="flex flex-col items-start gap-1">
                        <StockStatusBadge
                          status={v.stock?.status}
                          direction={v.stock?.direction}
                          ordered={v.stock?.ordered_quantity}
                          moved={v.stock?.moved_quantity}
                          pending={v.stock?.pending_quantity}
                        />
                        <span className="text-xs text-gray-500">
                          {(v.stock?.receipts || []).filter((r) => r.status === 'active').length} {outbound ? 'delivery' : 'receipt'}
                          {(v.stock?.receipts || []).filter((r) => r.status === 'active').length === 1 ? '' : 's'}
                        </span>
                      </div>
                    </td>
                  )}
                  <td className="px-4 py-3 text-right">
                    <div className="inline-flex items-center gap-3">
                      {tracks && v.status === 'posted' && (
                        <PermissionGate module="stock-receipts" action="create" subject={`goods for ${v.voucher_no}`}>
                          <button onClick={() => openReceipts(v)} title={outbound ? 'Record delivery' : 'Receive stock'} className="text-emerald-600 hover:text-emerald-800">
                            <PackageCheck className="w-4 h-4" />
                          </button>
                        </PermissionGate>
                      )}
                      <PermissionGate module={config.module} action="update" subject={`${config.label.toLowerCase()} vouchers`}>
                        <button onClick={() => openEdit(v)} title="Edit" className="text-blue-600 hover:text-blue-900">
                          <Edit2 className="w-4 h-4" />
                        </button>
                      </PermissionGate>
                      <PermissionGate module={config.module} action="delete" subject={`${config.label.toLowerCase()} vouchers`}>
                        <button onClick={() => remove(v)} disabled={saving} title="Delete" className="text-red-500 hover:text-red-700 disabled:opacity-40">
                          <Trash2 className="w-4 h-4" />
                        </button>
                      </PermissionGate>
                    </div>
                  </td>
                </tr>
              ))}
              {visible.length === 0 && !loading && (
                <tr>
                  <td colSpan={tracks ? 8 : 7} className="px-4 py-10 text-center text-gray-500">
                    <FileText className="w-8 h-8 mx-auto mb-2 text-gray-300" />
                    {items.length > 0
                      ? `No ${config.label.toLowerCase()} vouchers match that filter.`
                      : `No ${config.label.toLowerCase()} vouchers yet.`}
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      <StockReceiptModal
        open={Boolean(receiptTarget)}
        summary={receiptTarget?.summary}
        voucher={receiptTarget?.voucher}
        saving={receipts.saving}
        onClose={closeReceipts}
        onSubmit={submitReceipt}
      />
    </div>
  );
};

export default VoucherRegisterPage;
