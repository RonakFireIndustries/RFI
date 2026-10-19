import { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import {
  ArrowLeft, Banknote, ChevronRight, FileText, History, Link2, Loader2,
  Printer, SquarePen, X, XCircle,
} from 'lucide-react';
import { ordersService } from '../../services/ordersService';
import { voucherService } from '../../services/accountingService';
import api from '../../services/api';
import PermissionGate from '../../components/Guards/PermissionGate';
import {
  ORDER_SIDES, ORDER_DOCUMENTS, isEditableOrderStatus, isCancellableOrderStatus,
  nextOrderStatus, orderStatusLabel, orderStatusBadge, orderDocumentLabel, orderDocumentPath,
} from '../../config/orders';
import { formatMoney } from '../../utils/finance';
import { formatDate } from '../../utils/date';

const inputClass = 'w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 text-sm focus:ring-orange-500 focus:border-orange-500';
const labelClass = 'block text-xs font-medium text-gray-600 mb-1';

const fmtDate = (d) => formatDate(d, '--');

const fmtDateTime = (d) => (d
  ? new Date(d).toLocaleString('en-GB', {
    day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
  })
  : '—');

const firstError = (err) => {
  const errors = err?.response?.data?.data?.errors || err?.response?.data?.errors;
  if (errors) return Object.values(errors)[0]?.[0];
  return err?.response?.data?.data?.message || err?.response?.data?.message || 'Something went wrong.';
};

/* Timeline dot colours, one per stage. */
const DOT_RING = {
  open: 'bg-sky-100', in_progress: 'bg-amber-100', fulfilled: 'bg-blue-100',
  invoiced: 'bg-violet-100', done: 'bg-green-100', cancelled: 'bg-rose-100',
};
const DOT = {
  open: 'bg-sky-500', in_progress: 'bg-amber-500', fulfilled: 'bg-blue-500',
  invoiced: 'bg-violet-500', done: 'bg-green-500', cancelled: 'bg-rose-500',
};

/**
 * One order, end to end: what was promised (party, lines, totals), where it
 * stands (the status timeline), what it became (the linked document) and what
 * is still owed on that bill (read from the Outstanding report — the books
 * are always the source of truth for money).
 */
const OrderDetail = () => {
  const { id } = useParams();
  const navigate = useNavigate();

  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // Advance / cancel modal: one dialog, two intents.
  const [advanceOpen, setAdvanceOpen] = useState(false);
  const [cancelOpen, setCancelOpen] = useState(false);
  const [note, setNote] = useState('');
  const [docType, setDocType] = useState('');
  const [docId, setDocId] = useState('');
  const [candidates, setCandidates] = useState(null); // null = loading
  const [actionError, setActionError] = useState('');
  const [acting, setActing] = useState(false);

  const fetchOrder = async () => {
    setLoading(true);
    setError(null);
    try {
      setOrder(await ordersService.get(id));
    } catch (err) {
      if (err?.response?.status === 404) setError('Order not found.');
      else setError(firstError(err));
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchOrder();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [id]);

  /**
   * The bills the advance into `invoiced` may pick from, per document type.
   * Loaded lazily when the modal opens; the list endpoints may sit outside
   * this user's own permissions, in which case we fall back to entering the
   * id by hand — the backend still verifies the document exists and belongs
   * to this order's party.
   */
  const loadCandidates = async (type) => {
    setCandidates(null);
    try {
      let rows = [];
      if (type === 'invoice') {
        const res = await api.get('/invoices');
        rows = (res.data?.data || res.data || []).map((r) => ({
          id: r.id,
          label: `${r.invoice_number} · ${fmtDate(r.issue_date)}`,
          partyId: r.customer_id,
        }));
      } else if (type === 'purchase-order') {
        const res = await api.get('/purchases/orders');
        rows = (res.data?.data || res.data || []).map((r) => ({
          id: r.id,
          label: `${r.po_number} · ${fmtDate(r.created_at)}`,
          partyId: r.supplier_id,
        }));
      } else {
        const voucherType = type === 'sales-voucher' ? 'sales' : 'purchase';
        rows = (await voucherService.list({ voucher_type: voucherType }))
          .filter((r) => !r.voucher_type || r.voucher_type === voucherType)
          .map((r) => ({
            id: r.id,
            label: `${r.voucher_no} · ${fmtDate(r.voucher_date)}`,
          }));
      }
      const partyId = order?.customer_id || order?.supplier_id;
      setCandidates(partyId ? rows.filter((r) => !r.partyId || r.partyId === partyId) : rows);
    } catch {
      setCandidates([]);
    }
  };

  const openAdvance = () => {
    const target = nextOrderStatus(order.status);
    setNote('');
    setDocId('');
    setActionError('');
    if (target === 'invoiced') {
      const first = ORDER_DOCUMENTS[order.side]?.[0]?.type || '';
      setDocType(first);
      loadCandidates(first);
    }
    setAdvanceOpen(true);
  };

  const changeDocType = (type) => {
    setDocType(type);
    setDocId('');
    loadCandidates(type);
  };

  const openCancel = () => {
    setNote('');
    setActionError('');
    setCancelOpen(true);
  };

  const submitAdvance = async () => {
    const target = nextOrderStatus(order.status);
    const payload = { note: note.trim() || undefined };
    if (target === 'invoiced') {
      if (!docType || !docId) {
        setActionError('Pick the invoice or voucher this order became.');
        return;
      }
      payload.document_type = docType;
      payload.document_id = Number(docId);
    }
    setActionError('');
    setActing(true);
    try {
      await ordersService.advance(order.id, payload);
      await fetchOrder(); // show() carries the document/payment block the advance response lacks
      setAdvanceOpen(false);
    } catch (err) {
      setActionError(firstError(err));
    } finally {
      setActing(false);
    }
  };

  const submitCancel = async () => {
    setActionError('');
    setActing(true);
    try {
      await ordersService.cancel(order.id, { note: note.trim() || undefined });
      await fetchOrder();
      setCancelOpen(false);
    } catch (err) {
      setActionError(firstError(err));
    } finally {
      setActing(false);
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-[60vh]">
        <Loader2 className="w-8 h-8 animate-spin text-gray-300" />
      </div>
    );
  }

  if (error || !order) {
    return (
      <div className="text-center py-20">
        <FileText className="w-16 h-16 text-gray-300 mx-auto mb-4" />
        <h2 className="text-xl font-bold text-gray-900 mb-2">{error || 'Order Not Found'}</h2>
        <button onClick={() => navigate('/dashboard/orders')} className="text-orange-600 hover:underline font-medium mt-4 inline-block">
          Return to Orders
        </button>
      </div>
    );
  }

  const side = ORDER_SIDES[order.side] || ORDER_SIDES.sales;
  const party = side.party === 'customer' ? order.customer : order.supplier;
  const partyLabel = side.partyLabel;
  const next = nextOrderStatus(order.status);
  const events = order.events || [];
  const documentPath = orderDocumentPath(order.document);
  const payment = order.payment;
  const bill = payment?.bill;

  return (
    <div className="max-w-5xl mx-auto pb-10">
      {/* Header */}
      <div className="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div className="flex items-center gap-4">
          <button onClick={() => navigate('/dashboard/orders')} className="p-2 hover:bg-gray-200 rounded-lg text-gray-500 hover:text-gray-700">
            <ArrowLeft className="w-5 h-5" />
          </button>
          <div>
            <h1 className="text-xl font-bold text-gray-900">
              {side.label} Order {order.order_no}
            </h1>
            <div className="flex items-center gap-3 mt-1">
              <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${orderStatusBadge(order.status)}`}>
                {orderStatusLabel(order.status)}
              </span>
              <span className="text-sm text-gray-500">Created {fmtDate(order.created_at)}{order.creator?.name ? ` by ${order.creator.name}` : ''}</span>
            </div>
          </div>
        </div>
        <div className="flex items-center gap-2">
          {isEditableOrderStatus(order.status) && (
            <PermissionGate permission="orders.update" subject="this order">
              <button
                onClick={() => navigate(`/dashboard/orders/${order.id}/edit`)}
                className="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                <SquarePen className="w-4 h-4 mr-1.5" /> Edit
              </button>
            </PermissionGate>
          )}
          {next && (
            <PermissionGate permission="orders.fulfil" subject="this order">
              <button
                onClick={openAdvance}
                className="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-orange-500 rounded-lg hover:bg-orange-600"
              >
                <ChevronRight className="w-4 h-4 mr-1.5" /> Advance to {orderStatusLabel(next)}
              </button>
            </PermissionGate>
          )}
          {isCancellableOrderStatus(order.status) && (
            <PermissionGate permission="orders.cancel" subject="this order">
              <button
                onClick={openCancel}
                className="inline-flex items-center px-3 py-2 text-sm font-medium text-rose-700 bg-white border border-rose-200 rounded-lg hover:bg-rose-50"
              >
                <XCircle className="w-4 h-4 mr-1.5" /> Cancel Order
              </button>
            </PermissionGate>
          )}
          <button onClick={() => window.print()} className="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            <Printer className="w-4 h-4 mr-1.5" /> Print
          </button>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Left: the document itself */}
        <div className="lg:col-span-2 space-y-6">
          <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div className="flex flex-col sm:flex-row justify-between gap-6 pb-5 border-b border-gray-100">
              <div>
                <p className="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">{partyLabel}</p>
                <h3 className="text-lg font-bold text-gray-900">{party?.name || `${partyLabel} #${side.party === 'customer' ? order.customer_id : order.supplier_id}`}</h3>
              </div>
              <div className="sm:text-right space-y-1 text-sm text-gray-600">
                <p><span className="font-medium">Order date:</span> {fmtDate(order.order_date)}</p>
                {order.expected_delivery_date && (
                  <p><span className="font-medium">Delivery due:</span> {fmtDate(order.expected_delivery_date)}</p>
                )}
                {order.reference_no && <p><span className="font-medium">Reference:</span> {order.reference_no}</p>}
              </div>
            </div>

            <div className="overflow-x-auto mt-5">
              <table className="w-full text-left text-sm">
                <thead>
                  <tr className="bg-gray-50 border-y border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                    <th className="py-2.5 px-3">#</th>
                    <th className="py-2.5 px-3">Item</th>
                    <th className="py-2.5 px-3 text-center">HSN</th>
                    <th className="py-2.5 px-3 text-center">Qty</th>
                    <th className="py-2.5 px-3 text-right">Rate</th>
                    <th className="py-2.5 px-3 text-right">Disc %</th>
                    <th className="py-2.5 px-3 text-right">Tax %</th>
                    <th className="py-2.5 px-3 text-right">Taxable</th>
                    <th className="py-2.5 px-3 text-right">Tax</th>
                    <th className="py-2.5 px-3 text-right">Total</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {(order.items || []).map((item, i) => (
                    <tr key={item.id} className="text-sm">
                      <td className="py-2.5 px-3 text-gray-400">{i + 1}</td>
                      <td className="py-2.5 px-3">
                        <p className="font-medium text-gray-900">{item.item_description || item.product?.name || `Product #${item.product_id}`}</p>
                        {item.unit && <p className="text-xs text-gray-400 mt-0.5">Unit: {item.unit}</p>}
                      </td>
                      <td className="py-2.5 px-3 text-center text-gray-500 text-xs">{item.hsn_code || '—'}</td>
                      <td className="py-2.5 px-3 text-center font-medium text-gray-900">{item.quantity}</td>
                      <td className="py-2.5 px-3 text-right text-gray-600">{formatMoney(item.rate)}</td>
                      <td className="py-2.5 px-3 text-right text-gray-600">{item.discount_percent > 0 ? `${item.discount_percent}%` : '—'}</td>
                      <td className="py-2.5 px-3 text-right text-gray-600">{item.tax_rate > 0 ? `${item.tax_rate}%` : '—'}</td>
                      <td className="py-2.5 px-3 text-right text-gray-600">{formatMoney(item.taxable_amount)}</td>
                      <td className="py-2.5 px-3 text-right text-gray-600">{formatMoney(item.tax_amount)}</td>
                      <td className="py-2.5 px-3 text-right font-medium text-gray-900">{formatMoney(item.total_amount)}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            <div className="flex justify-end mt-5">
              <div className="w-64 bg-gray-50 rounded-xl border border-gray-100 p-5 space-y-2.5 text-sm">
                <div className="flex justify-between text-gray-500"><span>Subtotal</span><span className="font-medium text-gray-900">{formatMoney(order.subtotal)}</span></div>
                {Number(order.discount_total) > 0 && (
                  <div className="flex justify-between text-gray-500"><span>Discount</span><span className="font-medium text-gray-900">{formatMoney(order.discount_total)}</span></div>
                )}
                {Number(order.tax_total) > 0 && (
                  <div className="flex justify-between text-gray-500"><span>Tax</span><span className="font-medium text-gray-900">{formatMoney(order.tax_total)}</span></div>
                )}
                <div className="pt-3 mt-1 border-t border-gray-200 flex justify-between items-center">
                  <span className="font-bold text-gray-900">Total</span>
                  <span className="font-bold text-orange-600 text-lg">{formatMoney(order.total_amount)}</span>
                </div>
              </div>
            </div>

            {order.notes && (
              <div className="mt-5 pt-5 border-t border-gray-100">
                <p className="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Notes</p>
                <p className="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg border border-gray-100 whitespace-pre-wrap">{order.notes}</p>
              </div>
            )}
          </div>

          {/* Status timeline */}
          <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 className="font-semibold text-gray-800 flex items-center gap-2 mb-5">
              <History className="w-4 h-4 text-gray-400" />
              Status History
            </h3>
            {events.length === 0 ? (
              <p className="text-sm text-gray-400">No status events recorded.</p>
            ) : (
              <div className="space-y-0">
                {events.map((ev, idx) => (
                  <div key={ev.id || idx} className="relative flex gap-4 pb-5 last:pb-0">
                    {idx < events.length - 1 && (
                      <div className="absolute left-[11px] top-6 bottom-0 w-0.5 bg-gray-200" />
                    )}
                    <div className={`flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center ${DOT_RING[ev.to_status] || 'bg-gray-100'}`}>
                      <div className={`w-2.5 h-2.5 rounded-full ${DOT[ev.to_status] || 'bg-gray-400'}`} />
                    </div>
                    <div className="flex-1 min-w-0 pt-0.5">
                      <div className="flex items-center gap-2">
                        <span className="font-semibold text-sm text-gray-900">
                          {ev.from_status
                            ? `${orderStatusLabel(ev.from_status)} → ${orderStatusLabel(ev.to_status)}`
                            : `Order created · ${orderStatusLabel(ev.to_status)}`}
                        </span>
                        <span className="text-xs text-gray-400">{fmtDateTime(ev.created_at)}</span>
                      </div>
                      {ev.user && <p className="text-xs text-gray-500 mt-0.5">by {ev.user.name}</p>}
                      {ev.note && (
                        <p className="text-sm text-gray-600 mt-1 bg-gray-50 p-2.5 rounded-md">{ev.note}</p>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>

        {/* Right rail: what it became, and what is owed */}
        <div className="space-y-6">
          {/* Linked document */}
          <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 className="font-semibold text-gray-800 flex items-center gap-2 mb-4">
              <Link2 className="w-4 h-4 text-gray-400" />
              Linked Document
            </h3>
            {order.document ? (
              <div className="text-sm">
                <p className="text-gray-500">{orderDocumentLabel(order.document.type)}</p>
                <p className="font-semibold text-gray-900 mt-0.5">{order.document.number || `#${order.document.id}`}</p>
                {order.document.date && <p className="text-xs text-gray-500 mt-0.5">{fmtDate(order.document.date)}</p>}
                {documentPath && (
                  <Link to={documentPath} className="inline-block mt-2 text-sm text-orange-600 hover:underline">
                    Open in {order.document.type === 'invoice' ? 'Invoices' : order.document.type === 'purchase-order' ? 'Purchase Orders' : 'Voucher register'} →
                  </Link>
                )}
              </div>
            ) : (
              <p className="text-sm text-gray-400">
                Not linked to an {side.party === 'customer' ? 'invoice' : 'invoice or bill'} yet — it is attached while advancing to {orderStatusLabel('invoiced')}.
              </p>
            )}
          </div>

          {/* Payment (read from the Outstanding report) */}
          {payment && (
            <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
              <h3 className="font-semibold text-gray-800 flex items-center gap-2 mb-4">
                <Banknote className="w-4 h-4 text-gray-400" />
                Payment
              </h3>
              {bill ? (
                <div className="space-y-2.5 text-sm">
                  <div className="flex justify-between text-gray-500"><span>Bill</span><span className="font-medium text-gray-900">{bill.bill_name || `#${bill.bill_id}`}</span></div>
                  <div className="flex justify-between text-gray-500"><span>Bill date</span><span className="text-gray-900">{bill.date ? fmtDate(bill.date) : '—'}</span></div>
                  <div className="flex justify-between text-gray-500">
                    <span>Due date</span>
                    <span className={`font-medium ${bill.overdue ? 'text-rose-600' : 'text-gray-900'}`}>
                      {bill.due_date ? fmtDate(bill.due_date) : '—'}
                      {bill.overdue && <span className="block text-xs font-semibold text-right">overdue</span>}
                    </span>
                  </div>
                  <div className="flex justify-between text-gray-500"><span>Bill total</span><span className="text-gray-900">{formatMoney(bill.total)}</span></div>
                  <div className="flex justify-between text-gray-500"><span>Settled</span><span className="text-emerald-600">{formatMoney(bill.settled)}</span></div>
                  <div className="flex justify-between text-gray-500"><span>Ageing</span><span className="text-gray-900">{bill.bucket} ({Math.abs(bill.days)}d)</span></div>
                  <div className="pt-2.5 mt-1 border-t border-gray-200 flex justify-between items-center">
                    <span className="font-bold text-gray-900">Outstanding</span>
                    <span className={`font-bold text-base ${bill.balance > 0 ? 'text-amber-600' : 'text-emerald-600'}`}>
                      {formatMoney(bill.balance)}
                    </span>
                  </div>
                  {payment.party_outstanding !== null && (
                    <div className="flex justify-between text-gray-500 pt-1">
                      <span>{partyLabel} total</span>
                      <span className="text-gray-900">{formatMoney(payment.party_outstanding)}</span>
                    </div>
                  )}
                </div>
              ) : payment.party_outstanding !== null ? (
                <div>
                  <p className="text-sm font-medium text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-lg px-3 py-2.5">
                    Settled in full — this bill no longer appears in Outstandings.
                  </p>
                  <p className="text-sm text-gray-500 mt-3">
                    {partyLabel} total outstanding:{' '}
                    <span className="font-medium text-gray-900">{formatMoney(payment.party_outstanding)}</span>
                  </p>
                </div>
              ) : (
                <p className="text-sm text-gray-400">
                  No ledger is linked to this {partyLabel.toLowerCase()} yet, so outstanding figures are unavailable.
                </p>
              )}
              <p className="text-xs text-gray-400 mt-4">
                Figures come straight from the Outstanding report — pay or receive through Finance, never here.
              </p>
            </div>
          )}

          {/* Pipeline */}
          <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 className="font-semibold text-gray-800 mb-4">Pipeline</h3>
            <div className="space-y-2 text-sm">
              {['open', 'in_progress', 'fulfilled', 'invoiced', 'done'].map((stage) => {
                const reached = ['open', 'in_progress', 'fulfilled', 'invoiced', 'done'].indexOf(stage)
                  <= ['open', 'in_progress', 'fulfilled', 'invoiced', 'done'].indexOf(order.status)
                  && order.status !== 'cancelled';
                const current = order.status === stage;
                return (
                  <div key={stage} className="flex items-center gap-2.5">
                    <div className={`w-2.5 h-2.5 rounded-full ${reached ? DOT[stage] : 'bg-gray-200'}`} />
                    <span className={current ? 'font-semibold text-gray-900' : reached ? 'text-gray-700' : 'text-gray-400'}>
                      {orderStatusLabel(stage)}
                    </span>
                    {current && <span className="text-xs text-orange-600 font-medium">← current</span>}
                  </div>
                );
              })}
              {order.status === 'cancelled' && (
                <div className="flex items-center gap-2.5 pt-2 border-t border-gray-100 mt-2">
                  <div className="w-2.5 h-2.5 rounded-full bg-rose-500" />
                  <span className="font-semibold text-rose-700">Cancelled</span>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>

      {/* Advance modal */}
      {advanceOpen && next && (
        <div className="fixed inset-0 bg-gray-900/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col overflow-hidden">
            <div className="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
              <div>
                <h2 className="text-lg font-bold text-gray-900">Advance {order.order_no}</h2>
                <p className="text-sm text-gray-500 mt-0.5">
                  {orderStatusLabel(order.status)} → <span className="font-semibold text-gray-800">{orderStatusLabel(next)}</span>
                </p>
              </div>
              <button onClick={() => setAdvanceOpen(false)} className="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="flex-1 overflow-y-auto p-6 space-y-4">
              {next === 'invoiced' && (
                <div>
                  <label className={labelClass}>Mark invoiced against *</label>
                  <select value={docType} onChange={(e) => changeDocType(e.target.value)} className={inputClass}>
                    {(ORDER_DOCUMENTS[order.side] || []).map((d) => (
                      <option key={d.type} value={d.type}>{d.label}</option>
                    ))}
                  </select>

                  {candidates === null ? (
                    <p className="mt-2 text-xs text-gray-400 inline-flex items-center">
                      <Loader2 className="w-3 h-3 animate-spin mr-1.5" /> Loading documents…
                    </p>
                  ) : candidates.length > 0 ? (
                    <select value={docId} onChange={(e) => setDocId(e.target.value)} className={`${inputClass} mt-2`}>
                      <option value="">— Select {orderDocumentLabel(docType).toLowerCase()} —</option>
                      {candidates.map((c) => (
                        <option key={c.id} value={c.id}>{c.label}</option>
                      ))}
                    </select>
                  ) : (
                    <>
                      <p className="mt-2 text-xs text-gray-400">
                        None found for this {partyLabel.toLowerCase()} — enter the id by hand, or post the bill in Finance first.
                      </p>
                      <input
                        type="number"
                        min="1"
                        value={docId}
                        onChange={(e) => setDocId(e.target.value)}
                        placeholder={`${orderDocumentLabel(docType)} id`}
                        className={`${inputClass} mt-2`}
                      />
                    </>
                  )}
                  <p className="mt-1.5 text-xs text-gray-400">
                    The bill must belong to this {partyLabel.toLowerCase()} — the server checks that before linking.
                  </p>
                </div>
              )}

              <div>
                <label className={labelClass}>Note (optional)</label>
                <textarea
                  value={note}
                  onChange={(e) => setNote(e.target.value)}
                  rows="2"
                  placeholder="Why is it moving to this stage?"
                  className={inputClass}
                />
              </div>

              {actionError && <p className="text-sm text-red-600">{actionError}</p>}
            </div>

            <div className="px-6 py-4 border-t border-gray-200 flex justify-end gap-2">
              <button onClick={() => setAdvanceOpen(false)} className="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                Back
              </button>
              <button onClick={submitAdvance} disabled={acting} className="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-orange-500 rounded-lg hover:bg-orange-600 disabled:opacity-50">
                {acting ? <Loader2 className="w-4 h-4 animate-spin mr-1.5" /> : <ChevronRight className="w-4 h-4 mr-1.5" />}
                {acting ? 'Advancing…' : `Advance to ${orderStatusLabel(next)}`}
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Cancel modal */}
      {cancelOpen && (
        <div className="fixed inset-0 bg-gray-900/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl shadow-2xl w-full max-w-md max-h-[90vh] flex flex-col overflow-hidden">
            <div className="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
              <div>
                <h2 className="text-lg font-bold text-gray-900">Cancel {order.order_no}</h2>
                <p className="text-sm text-gray-500 mt-0.5">The order stops here — nothing is written to the books.</p>
              </div>
              <button onClick={() => setCancelOpen(false)} className="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="flex-1 overflow-y-auto p-6 space-y-4">
              <div>
                <label className={labelClass}>Reason (optional)</label>
                <textarea
                  value={note}
                  onChange={(e) => setNote(e.target.value)}
                  rows="2"
                  placeholder="Recorded on the order's timeline"
                  className={inputClass}
                />
              </div>
              {actionError && <p className="text-sm text-red-600">{actionError}</p>}
            </div>

            <div className="px-6 py-4 border-t border-gray-200 flex justify-end gap-2">
              <button onClick={() => setCancelOpen(false)} className="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                Keep Order
              </button>
              <button onClick={submitCancel} disabled={acting} className="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-rose-600 rounded-lg hover:bg-rose-700 disabled:opacity-50">
                {acting ? <Loader2 className="w-4 h-4 animate-spin mr-1.5" /> : <XCircle className="w-4 h-4 mr-1.5" />}
                {acting ? 'Cancelling…' : 'Cancel Order'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default OrderDetail;
