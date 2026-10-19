import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Plus, Search, RefreshCw, ClipboardList, X } from 'lucide-react';
import { ordersService } from '../../services/ordersService';
import PermissionGate from '../../components/Guards/PermissionGate';
import {
  ORDER_SIDES, ORDER_STATUS_KEYS, orderStatusBadge, orderStatusLabel,
} from '../../config/orders';
import { formatMoney } from '../../utils/finance';
import { formatDate } from '../../utils/date';

const inputClass = 'w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 text-sm focus:ring-orange-500 focus:border-orange-500';
const labelClass = 'block text-xs font-medium text-gray-600 mb-1';

const fmtDate = (d) => formatDate(d, '--');

const today = () => new Date().toISOString().split('T')[0];

const firstError = (err) =>
  err?.response?.data?.data?.message || err?.response?.data?.message || 'Failed to load orders.';

/**
 * Order tracking — the register both sides share.
 *
 * Operational only: a row shows where an order stands and what it is worth;
 * the money itself lives on the linked bill (Finance), never here. Filters
 * run server-side through OrderController::index.
 */
const OrdersListPage = () => {
  const navigate = useNavigate();

  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const [side, setSide] = useState('');
  const [status, setStatus] = useState('');
  const [search, setSearch] = useState('');
  const [from, setFrom] = useState('');
  const [to, setTo] = useState('');

  const fetchOrders = async () => {
    setLoading(true);
    setError(null);
    try {
      const params = {};
      if (side) params.side = side;
      if (status) params.status = status;
      if (search.trim()) params.search = search.trim();
      if (from) params.from = from;
      if (to) params.to = to;
      setOrders(await ordersService.list(params));
    } catch (err) {
      setError(firstError(err));
    } finally {
      setLoading(false);
    }
  };

  // Debounced so typing a search fires one request, not one per keystroke.
  // Each re-run captures the fetch of its own render, so it always reads the
  // current filter values.
  useEffect(() => {
    const timer = setTimeout(fetchOrders, 300);
    return () => clearTimeout(timer);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [side, status, search, from, to]);

  const filtersActive = Boolean(side || status || search || from || to);

  const clearFilters = () => {
    setSide('');
    setStatus('');
    setSearch('');
    setFrom('');
    setTo('');
  };

  /** A delivery date that has passed while the order is still in flight. */
  const deliveryOverdue = (o) =>
    Boolean(o.expected_delivery_date)
    && !['fulfilled', 'invoiced', 'done', 'cancelled'].includes(o.status)
    && String(o.expected_delivery_date).slice(0, 10) < today();

  const partyName = (o) => o.customer?.name || o.supplier?.name || '—';

  return (
    <div className="p-6 max-w-7xl mx-auto">
      <div className="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Orders</h1>
          <p className="text-sm text-gray-500">
            Sales and purchase orders from confirmation to settlement. Orders never touch the books — the linked voucher does.
          </p>
        </div>
        <div className="flex items-center gap-2">
          <button
            type="button"
            onClick={fetchOrders}
            disabled={loading}
            className="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-60"
          >
            <RefreshCw className={`w-4 h-4 mr-1.5 ${loading ? 'animate-spin' : ''}`} /> Refresh
          </button>
          <PermissionGate module="orders" action="create" subject="orders">
            <button
              type="button"
              onClick={() => navigate('/dashboard/orders/new')}
              className="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-orange-500 rounded-lg hover:bg-orange-600"
            >
              <Plus className="w-4 h-4 mr-1.5" /> New Order
            </button>
          </PermissionGate>
        </div>
      </div>

      <div className="bg-white rounded-lg shadow p-4 mb-4">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
          <div>
            <label className={labelClass}>Side</label>
            <select value={side} onChange={(e) => setSide(e.target.value)} className={inputClass}>
              <option value="">Both sides</option>
              {Object.values(ORDER_SIDES).map((s) => (
                <option key={s.key} value={s.key}>{s.label}</option>
              ))}
            </select>
          </div>
          <div>
            <label className={labelClass}>Status</label>
            <select value={status} onChange={(e) => setStatus(e.target.value)} className={inputClass}>
              <option value="">Any status</option>
              {ORDER_STATUS_KEYS.map((key) => (
                <option key={key} value={key}>{orderStatusLabel(key)}</option>
              ))}
            </select>
          </div>
          <div className="lg:col-span-2">
            <label className={labelClass}>Search</label>
            <div className="relative">
              <Search className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                placeholder="Order no, party, reference…"
                className={`${inputClass} pl-9`}
              />
            </div>
          </div>
          <div className="grid grid-cols-2 gap-2">
            <div>
              <label className={labelClass}>From</label>
              <input type="date" value={from} onChange={(e) => setFrom(e.target.value)} className={inputClass} />
            </div>
            <div>
              <label className={labelClass}>To</label>
              <input type="date" value={to} onChange={(e) => setTo(e.target.value)} className={inputClass} />
            </div>
          </div>
        </div>
        {filtersActive && (
          <div className="mt-3 flex items-center justify-between text-xs text-gray-500">
            <span>{orders.length} order{orders.length === 1 ? '' : 's'} match{orders.length === 1 ? 'es' : ''} these filters</span>
            <button type="button" onClick={clearFilters} className="inline-flex items-center hover:text-gray-800">
              <X className="w-3 h-3 mr-1" /> Clear filters
            </button>
          </div>
        )}
      </div>

      {error && (
        <div className="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{error}</div>
      )}

      <div className="bg-white rounded-lg shadow overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm">
            <thead>
              <tr className="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                <th className="px-4 py-3">Order</th>
                <th className="px-4 py-3">Party</th>
                <th className="px-4 py-3">Date</th>
                <th className="px-4 py-3">Delivery due</th>
                <th className="px-4 py-3 text-center">Items</th>
                <th className="px-4 py-3 text-right">Total</th>
                <th className="px-4 py-3">Status</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {!loading && orders.map((o) => (
                <tr
                  key={o.id}
                  onClick={() => navigate(`/dashboard/orders/${o.id}`)}
                  className="hover:bg-orange-50/40 cursor-pointer"
                >
                  <td className="px-4 py-3">
                    <p className="font-semibold text-gray-900">{o.order_no}</p>
                    {o.reference_no && <p className="text-xs text-gray-400 mt-0.5">Ref: {o.reference_no}</p>}
                  </td>
                  <td className="px-4 py-3">
                    <span className="text-xs font-medium text-gray-400 uppercase mr-1.5">
                      {ORDER_SIDES[o.side]?.label}
                    </span>
                    <span className="text-gray-800">{partyName(o)}</span>
                  </td>
                  <td className="px-4 py-3 text-gray-600">{fmtDate(o.order_date)}</td>
                  <td className="px-4 py-3">
                    {o.expected_delivery_date ? (
                      <span className={deliveryOverdue(o) ? 'text-rose-600 font-medium' : 'text-gray-600'}>
                        {fmtDate(o.expected_delivery_date)}
                        {deliveryOverdue(o) && <span className="block text-xs">overdue</span>}
                      </span>
                    ) : (
                      <span className="text-gray-400">—</span>
                    )}
                  </td>
                  <td className="px-4 py-3 text-center text-gray-600">{o.items_count ?? '—'}</td>
                  <td className="px-4 py-3 text-right font-medium text-gray-900">{formatMoney(o.total_amount)}</td>
                  <td className="px-4 py-3">
                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${orderStatusBadge(o.status)}`}>
                      {orderStatusLabel(o.status)}
                    </span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {loading && (
          <div className="py-16 text-center text-sm text-gray-400">Loading orders…</div>
        )}

        {!loading && !error && orders.length === 0 && (
          <div className="py-16 text-center">
            <ClipboardList className="w-12 h-12 text-gray-300 mx-auto mb-3" />
            <p className="text-sm font-medium text-gray-600">
              {filtersActive ? 'No orders match these filters.' : 'No orders yet.'}
            </p>
            {filtersActive ? (
              <button type="button" onClick={clearFilters} className="text-sm text-orange-600 hover:underline mt-2">
                Clear filters
              </button>
            ) : (
              <PermissionGate module="orders" action="create" subject="orders">
                <button
                  type="button"
                  onClick={() => navigate('/dashboard/orders/new')}
                  className="mt-3 inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-orange-500 rounded-lg hover:bg-orange-600"
                >
                  <Plus className="w-4 h-4 mr-1.5" /> Raise the first order
                </button>
              </PermissionGate>
            )}
          </div>
        )}
      </div>
    </div>
  );
};

export default OrdersListPage;
