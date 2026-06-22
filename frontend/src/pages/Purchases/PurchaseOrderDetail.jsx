import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, Eye, Loader2, FileText, Printer } from 'lucide-react';
import api from '../../services/api';

export default function PurchaseOrderDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

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

  const formatCurrency = (val) => {
    const num = parseFloat(val || 0);
    return '₹' + num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  };
  const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : '-';

  const statusBadge = (s) => {
    const m = { Approved: 'blue', 'Partially Received': 'yellow', Received: 'green', Cancelled: 'red', 'Pending Approval': 'orange' };
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
        <button onClick={() => navigate('/dashboard/purchases')} className="text-[#2563eb] hover:underline font-medium mt-4 inline-block">Return to Purchase Orders</button>
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
        <button onClick={() => window.print()} className="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
          <Printer className="w-4 h-4 mr-1.5" /> Print
        </button>
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
                      <p className="font-medium text-gray-900">{item.product?.name || `Product #${item.product_id}`}</p>
                      {item.product?.sku && <p className="text-xs text-gray-400 mt-0.5">SKU: {item.product.sku}</p>}
                    </td>
                    <td className="py-3 px-3 text-center text-gray-500 text-xs">{item.hsn_code || '-'}</td>
                    <td className="py-3 px-3 text-center font-medium text-gray-900">{item.quantity}</td>
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
                <div className="pt-3 mt-3 border-t border-gray-200 flex justify-between items-center">
                  <span className="font-bold text-gray-900">Total</span>
                  <span className="font-bold text-[#2563eb] text-lg">{formatCurrency(order.total_amount)}</span>
                </div>
              </div>
            </div>
          </div>

          {order.notes && (
            <div className="mt-6 pt-6 border-t border-gray-200">
              <p className="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Notes</p>
              <p className="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg border border-gray-100">{order.notes}</p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
