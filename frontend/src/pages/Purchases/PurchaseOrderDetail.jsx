import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, Eye, Loader2, FileText, Printer, CheckCircle, XCircle, PackageCheck, X, Banknote } from 'lucide-react';
import api from '../../services/api';
import { useAuthStore } from '../../store/authStore';

export default function PurchaseOrderDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { permissions } = useAuthStore();
  const canPay = permissions.includes('create_payments');
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [receiveOpen, setReceiveOpen] = useState(false);
  const [receivedQtys, setReceivedQtys] = useState({});
  const [receiving, setReceiving] = useState(false);
  const [receiveError, setReceiveError] = useState(null);
  const [payOpen, setPayOpen] = useState(false);
  const [payAmount, setPayAmount] = useState('');
  const [payDate, setPayDate] = useState(new Date().toISOString().split('T')[0]);
  const [payMethod, setPayMethod] = useState('Bank Transfer');
  const [payRef, setPayRef] = useState('');
  const [payNotes, setPayNotes] = useState('');
  const [payError, setPayError] = useState(null);
  const [savingPayment, setSavingPayment] = useState(false);

  useEffect(() => {
    fetchOrder();
  }, [id]);

  const fetchOrder = async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await api.get(`/purchases/orders/${id}`);
      setOrder(res.data?.data || res.data);
    } catch (err) {
      if (err.response?.status === 404) setError('Purchase order not found.');
      else setError('Failed to load purchase order.');
    } finally {
      setLoading(false);
    }
  };

  const handleApprove = async () => {
    if (!window.confirm('Approve this purchase order?')) return;
    try {
      const res = await api.post(`/purchases/orders/${id}/approve`);
      setOrder(res.data?.data || res.data);
    } catch (error) {
      alert(error.response?.data?.message || 'Failed to approve order.');
    }
  };

  const handleReject = async () => {
    if (!window.confirm('Reject this purchase order?')) return;
    try {
      const res = await api.post(`/purchases/orders/${id}/reject`);
      setOrder(res.data?.data || res.data);
    } catch (error) {
      alert(error.response?.data?.message || 'Failed to reject order.');
    }
  };

  const openReceiveModal = () => {
    const qtyMap = {};
    (order.items || []).forEach(item => {
      qtyMap[item.id] = item.quantity - (item.received_quantity || 0);
    });
    setReceivedQtys(qtyMap);
    setReceiveError(null);
    setReceiveOpen(true);
  };

  const handleConfirmReceipt = async () => {
    setReceiveError(null);
    const payload = (order.items || []).map(item => {
      const val = parseInt(receivedQtys[item.id], 10);
      return { id: item.id, received_qty: Number.isNaN(val) ? 0 : val };
    });

    if (!payload.some(p => p.received_qty > 0)) {
      setReceiveError('Enter received quantity for at least one item.');
      return;
    }

    for (const p of payload) {
      const item = order.items.find(i => i.id === p.id);
      const remaining = item.quantity - (item.received_quantity || 0);
      if (p.received_qty < 0 || p.received_qty > remaining) {
        setReceiveError(`Received quantity for '${item.product?.name || item.custom_product_name}' exceeds ordered quantity.`);
        return;
      }
    }

    try {
      setReceiving(true);
      const res = await api.post(`/purchases/orders/${id}/confirm-receipt`, { items: payload });
      setOrder(res.data?.data || res.data);
      setReceiveOpen(false);
    } catch (error) {
      setReceiveError(error.response?.data?.message || 'Failed to confirm receipt.');
    } finally {
      setReceiving(false);
    }
  };

  const openPayModal = () => {
    setPayAmount(outstanding > 0 ? outstanding : '');
    setPayDate(new Date().toISOString().split('T')[0]);
    setPayMethod('Bank Transfer');
    setPayRef('');
    setPayNotes('');
    setPayError(null);
    setPayOpen(true);
  };

  const handleSavePayment = async () => {
    setPayError(null);
    const amount = parseFloat(payAmount);
    if (!amount || amount <= 0) {
      setPayError('Enter a valid payment amount.');
      return;
    }
    try {
      setSavingPayment(true);
      await api.post('/payments', {
        type: 'Payable',
        amount,
        payment_date: payDate,
        payment_method: payMethod,
        reference_number: payRef || null,
        notes: payNotes || null,
        supplier_id: order.supplier_id,
        purchase_order_id: order.id,
      });
      await fetchOrder();
      setPayOpen(false);
    } catch (error) {
      setPayError(error.response?.data?.message || 'Failed to record payment.');
    } finally {
      setSavingPayment(false);
    }
  };

  const formatCurrency = (val) => {
    const num = parseFloat(val || 0);
    return '₹' + num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  };
  const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : '-';
  const formatDateShort = (d) => d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '-';

  const grandTotal = parseFloat(order?.total_amount || 0) + parseFloat(order?.shipping_cost || 0) + parseFloat(order?.other_cost || 0);
  const paidAmount = parseFloat(order?.paid_amount || 0);
  const outstanding = Math.max(0, grandTotal - paidAmount);

  const statusBadge = (s) => {
    const m = { Approved: 'blue', 'Partially Received': 'yellow', Received: 'green', Cancelled: 'red', Rejected: 'red', 'Pending Approval': 'orange' };
    const c = m[s] || 'gray';
    return <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-${c}-100 text-${c}-800`}>{s}</span>;
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
        <button onClick={() => navigate('/dashboard/purchases')} className="text-primary hover:underline font-medium mt-4 inline-block">Return to Purchase Orders</button>
      </div>
    );
  }

  return (
    <div className="max-w-5xl mx-auto pb-10">
      <div className="flex items-center justify-between mb-6">
        <div className="flex items-center gap-4">
          <button onClick={() => navigate('/dashboard/purchases')} className="p-2 hover:bg-gray-200 rounded-lg text-gray-500 hover:text-gray-700">
            <ArrowLeft className="w-5 h-5" />
          </button>
          <div>
            <h1 className="text-xl font-bold text-gray-900">Purchase Order {order.po_number}</h1>
            <div className="flex items-center gap-3 mt-1">
              {statusBadge(order.status)}
              <span className="text-sm text-gray-500">Created {formatDate(order.created_at)}</span>
            </div>
          </div>
        </div>
        <div className="flex items-center gap-2">
          {(order.status === 'Pending Approval' || order.status === 'Pending') && (
            <>
              <button onClick={handleApprove} className="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                <CheckCircle className="w-4 h-4 mr-1.5" /> Approve
              </button>
              <button onClick={handleReject} className="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                <XCircle className="w-4 h-4 mr-1.5" /> Reject
              </button>
            </>
          )}
          {['Approved', 'Partially Received'].includes(order.status) && (
            <button onClick={openReceiveModal} className="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
              <PackageCheck className="w-4 h-4 mr-1.5" /> Receive Order
            </button>
          )}
          {canPay && (
            <button onClick={openPayModal} className="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700">
              <Banknote className="w-4 h-4 mr-1.5" /> Record Payment
            </button>
          )}
          <button onClick={() => window.print()} className="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            <Printer className="w-4 h-4 mr-1.5" /> Print
          </button>
        </div>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div className="p-6 sm:p-8">
          <div className="flex flex-col sm:flex-row justify-between gap-6 mb-8 pb-6 border-b border-gray-200">
            <div>
              <p className="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Supplier</p>
              <h3 className="text-lg font-bold text-gray-900">{order.supplier?.name || `Supplier #${order.supplier_id}`}</h3>
              {order.supplier?.email && <p className="text-sm text-gray-500 mt-1">{order.supplier.email}</p>}
              {order.supplier?.phone && <p className="text-sm text-gray-500">{order.supplier.phone}</p>}
              {order.supplier?.gst_number && <span className="inline-block mt-2 text-xs font-medium bg-gray-100 text-gray-700 px-2 py-1 rounded">GSTIN: {order.supplier.gst_number}</span>}
            </div>
            <div className="sm:text-right">
              <p className="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Order Info</p>
              <p className="text-sm text-gray-600"><span className="font-medium">Date:</span> {formatDate(order.created_at)}</p>
              {order.gst_type && <p className="text-sm text-gray-600 mt-1"><span className="font-medium">GST:</span> {order.gst_type.toUpperCase()} @ {order.gst_rate}%</p>}
              {order.shipping_cost > 0 && <p className="text-sm text-gray-600 mt-1"><span className="font-medium">Shipping:</span> {formatCurrency(order.shipping_cost)}</p>}
              {order.site && (
                <div className="mt-3 pt-3 border-t border-gray-100 text-left sm:text-right">
                  <p className="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Drop-off Site</p>
                  <p className="text-sm font-semibold text-gray-900">{order.site.name}</p>
                  {order.site.address && <p className="text-xs text-gray-500 mt-0.5">{order.site.address}</p>}
                  {(order.site.city || order.site.state) && <p className="text-xs text-gray-500">{[order.site.city, order.site.state].filter(Boolean).join(', ')}</p>}
                </div>
              )}
            </div>
          </div>

          <div className="overflow-x-auto mb-6">
            <table className="w-full text-left">
              <thead>
                <tr className="bg-gray-50 border-y border-gray-200">
                  <th className="py-3 px-3 text-xs font-bold text-gray-500 uppercase tracking-wider">#</th>
                  <th className="py-3 px-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Item</th>
                  <th className="py-3 px-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">HSN</th>
                  <th className="py-3 px-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Qty</th>
                  <th className="py-3 px-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Rate</th>
                  <th className="py-3 px-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Amount</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {order.items?.length > 0 ? order.items.map((item, i) => (
                  <tr key={item.id} className="text-sm">
                    <td className="py-3 px-3 text-gray-400">{i + 1}</td>
                    <td className="py-3 px-3">
                      <p className="font-medium text-gray-900">{item.product?.name || item.custom_product_name || `Product #${item.product_id}`}</p>
                      {item.product?.sku && <p className="text-xs text-gray-400 mt-0.5">SKU: {item.product.sku}</p>}
                    </td>
                    <td className="py-3 px-3 text-center text-gray-500 text-xs">{item.hsn_code || '-'}</td>
                    <td className="py-3 px-3 text-center">
                      <span className="font-medium text-gray-900">{item.quantity}</span>
                      {(item.received_quantity || 0) > 0 && (
                        <span className="block text-xs text-green-600">received {item.received_quantity}</span>
                      )}
                    </td>
                    <td className="py-3 px-3 text-right text-gray-600">{formatCurrency(item.unit_price || item.unit_cost)}</td>
                    <td className="py-3 px-3 text-right font-medium text-gray-900">{formatCurrency(item.total)}</td>
                  </tr>
                )) : (
                  <tr><td colSpan="6" className="py-8 text-center text-gray-400 text-sm">No items found.</td></tr>
                )}
              </tbody>
            </table>
          </div>

          <div className="flex justify-end">
            <div className="w-64 bg-gray-50 rounded-xl border border-gray-100 p-5">
              <div className="space-y-2.5 text-sm">
                <div className="flex justify-between text-gray-500"><span>Subtotal</span><span className="font-medium text-gray-900">{formatCurrency(order.subtotal || order.total_amount)}</span></div>
                {parseFloat(order.cgst_total || 0) > 0 && <div className="flex justify-between text-gray-500"><span>CGST</span><span className="font-medium text-gray-900">{formatCurrency(order.cgst_total)}</span></div>}
                {parseFloat(order.sgst_total || 0) > 0 && <div className="flex justify-between text-gray-500"><span>SGST</span><span className="font-medium text-gray-900">{formatCurrency(order.sgst_total)}</span></div>}
                {parseFloat(order.igst_total || 0) > 0 && <div className="flex justify-between text-gray-500"><span>IGST</span><span className="font-medium text-gray-900">{formatCurrency(order.igst_total)}</span></div>}
                {parseFloat(order.shipping_cost || 0) > 0 && <div className="flex justify-between text-gray-500"><span>Shipping</span><span className="font-medium text-gray-900">{formatCurrency(order.shipping_cost)}</span></div>}
                {parseFloat(order.other_cost || 0) > 0 && (
                  <div className="flex justify-between text-gray-500">
                    <span>{order.other_cost_note ? `Other Cost (${order.other_cost_note})` : 'Other Cost'}</span>
                    <span className="font-medium text-gray-900">{formatCurrency(order.other_cost)}</span>
                  </div>
                )}
                <div className="pt-3 mt-3 border-t border-gray-200 flex justify-between items-center">
                  <span className="font-bold text-gray-900">Total</span>
                  <span className="font-bold text-primary text-lg">{formatCurrency(grandTotal)}</span>
                </div>
                <div className="flex justify-between text-gray-500"><span>Paid</span><span className="font-medium text-emerald-600">{formatCurrency(paidAmount)}</span></div>
                <div className="flex justify-between text-gray-500"><span>Outstanding</span><span className={`font-semibold ${outstanding > 0 ? 'text-amber-600' : 'text-emerald-600'}`}>{formatCurrency(outstanding)}</span></div>
              </div>
            </div>
          </div>

          {order.payments?.length > 0 && (
            <div className="mt-6 pt-6 border-t border-gray-200">
              <p className="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Payment History</p>
              <div className="overflow-x-auto">
                <table className="w-full text-left">
                  <thead>
                    <tr className="bg-gray-50 border-y border-gray-200">
                      <th className="py-2.5 px-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                      <th className="py-2.5 px-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Method</th>
                      <th className="py-2.5 px-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Reference</th>
                      <th className="py-2.5 px-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Amount</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-100">
                    {order.payments.map((p) => (
                      <tr key={p.id} className="text-sm">
                        <td className="py-2.5 px-3 text-gray-600">{formatDateShort(p.payment_date)}</td>
                        <td className="py-2.5 px-3 text-gray-600">{p.payment_method || '-'}</td>
                        <td className="py-2.5 px-3 text-gray-600">{p.reference_number || '-'}</td>
                        <td className="py-2.5 px-3 text-right font-medium text-emerald-600">{formatCurrency(p.amount)}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
              {order.payments.some(p => p.notes) && (
                <div className="mt-3 space-y-1">
                  {order.payments.filter(p => p.notes).map(p => (
                    <p key={p.id} className="text-xs text-gray-500"><span className="font-medium text-gray-600">{formatDateShort(p.payment_date)}:</span> {p.notes}</p>
                  ))}
                </div>
              )}
            </div>
          )}

          {order.notes && (
            <div className="mt-6 pt-6 border-t border-gray-200">
              <p className="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Notes</p>
              <p className="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg border border-gray-100">{order.notes}</p>
            </div>
          )}

          {order.terms_conditions && (
            <div className="mt-6 pt-6 border-t border-gray-200">
              <p className="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Terms & Conditions</p>
              <p className="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg border border-gray-100 whitespace-pre-wrap">{order.terms_conditions}</p>
            </div>
          )}
        </div>
      </div>

      {receiveOpen && (
        <div className="fixed inset-0 bg-gray-900/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden">
            <div className="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
              <div>
                <h2 className="text-lg font-bold text-gray-900">Receive Order {order.po_number}</h2>
                <p className="text-sm text-gray-500 mt-0.5">Enter received quantity for each item (full or partial).</p>
              </div>
              <button onClick={() => setReceiveOpen(false)} className="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="flex-1 overflow-y-auto p-6 space-y-4">
              {(order.items || []).map(item => {
                const already = item.received_quantity || 0;
                const remaining = item.quantity - already;
                return (
                  <div key={item.id} className="flex flex-col sm:flex-row sm:items-center gap-3 p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-semibold text-gray-900 truncate">{item.product?.name || item.custom_product_name || `Product #${item.product_id}`}</p>
                      <p className="text-xs text-gray-500 mt-0.5">
                        Ordered: {item.quantity}
                        {already > 0 && <span className="text-gray-400"> · Already received: {already}</span>}
                        <span className="text-gray-400"> · Remaining: {remaining}</span>
                      </p>
                    </div>
                    <div className="flex items-center gap-2">
                      <input
                        type="number"
                        min="0"
                        max={remaining}
                        value={receivedQtys[item.id] ?? ''}
                        onChange={(e) => setReceivedQtys(prev => ({ ...prev, [item.id]: e.target.value }))}
                        className="w-24 text-center border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
                      />
                      <button
                        type="button"
                        onClick={() => setReceivedQtys(prev => ({ ...prev, [item.id]: remaining }))}
                        className="px-3 py-2 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100"
                      >
                        Full
                      </button>
                    </div>
                  </div>
                );
              })}
              {receiveError && <p className="text-sm text-red-600">{receiveError}</p>}
            </div>

            <div className="px-6 py-4 border-t border-gray-200 flex justify-end gap-2">
              <button onClick={() => setReceiveOpen(false)} className="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                Cancel
              </button>
              <button onClick={handleConfirmReceipt} disabled={receiving} className="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                {receiving ? <Loader2 className="w-4 h-4 animate-spin mr-1.5" /> : <PackageCheck className="w-4 h-4 mr-1.5" />}
                {receiving ? 'Confirming...' : 'Confirm Receipt'}
              </button>
            </div>
          </div>
        </div>
      )}

      {payOpen && (
        <div className="fixed inset-0 bg-gray-900/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col overflow-hidden">
            <div className="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
              <div>
                <h2 className="text-lg font-bold text-gray-900">Record Payment — {order.po_number}</h2>
                <p className="text-sm text-gray-500 mt-0.5">Payable to {order.supplier?.name || `Supplier #${order.supplier_id}`}</p>
              </div>
              <button onClick={() => setPayOpen(false)} className="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="flex-1 overflow-y-auto p-6 space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div className="col-span-1">
                  <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Total</label>
                  <p className="text-sm font-semibold text-gray-900">{formatCurrency(grandTotal)}</p>
                </div>
                <div className="col-span-1">
                  <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Outstanding</label>
                  <p className="text-sm font-semibold text-amber-600">{formatCurrency(outstanding)}</p>
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Amount</label>
                <input
                  type="number"
                  min="0"
                  step="0.01"
                  value={payAmount}
                  onChange={(e) => setPayAmount(e.target.value)}
                  placeholder="0.00"
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Payment Date</label>
                  <input
                    type="date"
                    value={payDate}
                    onChange={(e) => setPayDate(e.target.value)}
                    className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Method</label>
                  <select
                    value={payMethod}
                    onChange={(e) => setPayMethod(e.target.value)}
                    className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none"
                  >
                    {['Cash', 'Bank Transfer', 'UPI', 'Cheque', 'Card', 'Other'].map(m => (
                      <option key={m} value={m}>{m}</option>
                    ))}
                  </select>
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Reference Number</label>
                <input
                  type="text"
                  value={payRef}
                  onChange={(e) => setPayRef(e.target.value)}
                  placeholder="e.g. NEFT/UTR/Cheque no."
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Notes</label>
                <textarea
                  value={payNotes}
                  onChange={(e) => setPayNotes(e.target.value)}
                  rows="2"
                  placeholder="Optional"
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none"
                />
              </div>

              {payError && <p className="text-sm text-red-600">{payError}</p>}
            </div>

            <div className="px-6 py-4 border-t border-gray-200 flex justify-end gap-2">
              <button onClick={() => setPayOpen(false)} className="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                Cancel
              </button>
              <button onClick={handleSavePayment} disabled={savingPayment} className="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 disabled:opacity-50">
                {savingPayment ? <Loader2 className="w-4 h-4 animate-spin mr-1.5" /> : <Banknote className="w-4 h-4 mr-1.5" />}
                {savingPayment ? 'Saving...' : 'Save Payment'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
