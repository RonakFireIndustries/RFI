import { useState, useEffect } from 'react';
import { Plus, Trash2, Save, FileText, ArrowLeft, Loader2, Info } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import api from '../../services/api';

export default function InvoiceBuilder() {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [customers, setCustomers] = useState([]);
  const [products, setProducts] = useState([]);

  const [formData, setFormData] = useState({
    customer_id: '',
    sales_order_id: '',
    status: 'Draft',
    due_date: '',
    gst_type: 'cgst',
    gst_rate: '18',
    notes: '',
    terms: '1. Payment is due within 30 days.\n2. Goods once sold will not be taken back.',
  });

  const [items, setItems] = useState([
    {
      id: Date.now(),
      product_id: '',
      item_description: '',
      hsn_code: '',
      quantity: 1,
      unit_price: 0,
    }
  ]);

  useEffect(() => {
    fetchInitialData();
  }, []);

  const fetchInitialData = async () => {
    try {
      const [customersRes, productsRes] = await Promise.all([
        api.get('/customers'),
        api.get('/products')
      ]);
      setCustomers(customersRes.data.data || customersRes.data);
      setProducts(productsRes.data.data || productsRes.data);
    } catch (error) {
      console.error("Error fetching data:", error);
    }
  };

  const handleItemChange = (id, field, value) => {
    setItems(items.map(item => {
      if (item.id === id) {
        const updatedItem = { ...item, [field]: value };
        if (field === 'product_id' && value) {
          const product = products.find(p => p.id.toString() === value);
          if (product) {
            updatedItem.item_description = product.name;
            updatedItem.unit_price = product.selling_price || 0;
          }
        }
        return updatedItem;
      }
      return item;
    }));
  };

  const addItem = () => {
    setItems([...items, {
      id: Date.now(),
      product_id: '',
      item_description: '',
      hsn_code: '',
      quantity: 1,
      unit_price: 0,
    }]);
  };

  const removeItem = (id) => {
    if (items.length === 1) return;
    setItems(items.filter(item => item.id !== id));
  };

  const calculateTotals = () => {
    const subtotal = items.reduce((sum, item) => {
      return sum + (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
    }, 0);

    const gstRate = parseFloat(formData.gst_rate) || 0;
    const taxAmount = subtotal * (gstRate / 100);

    let cgst_total = 0;
    let sgst_total = 0;
    let igst_total = 0;

    if (formData.gst_type === 'igst') {
      igst_total = taxAmount;
    } else {
      cgst_total = taxAmount / 2;
      sgst_total = taxAmount / 2;
    }

    return {
      subtotal,
      cgst_total,
      sgst_total,
      igst_total,
      grand_total: subtotal + taxAmount
    };
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      const payload = {
        customer_id: formData.customer_id,
        sales_order_id: formData.sales_order_id || null,
        status: formData.status,
        due_date: formData.due_date || null,
        notes: formData.notes,
        terms: formData.terms,
        gst_type: formData.gst_type,
        gst_rate: formData.gst_rate,
        items: items.map(i => ({
          product_id: i.product_id || null,
          item_description: i.item_description,
          hsn_code: i.hsn_code,
          quantity: i.quantity,
          unit_price: i.unit_price,
        }))
      };

      await api.post('/invoices', payload);
      navigate('/dashboard/invoices');
    } catch (error) {
      console.error("Error creating invoice:", error);
      alert(error.response?.data?.message || "Failed to create invoice.");
    } finally {
      setLoading(false);
    }
  };

  const totals = calculateTotals();

  return (
    <div className="max-w-7xl mx-auto pb-20">
      <div className="flex items-center justify-between mb-6">
        <div className="flex items-center">
          <button onClick={() => navigate('/dashboard/invoices')} className="mr-4 text-gray-500 hover:text-gray-700 transition-colors">
            <ArrowLeft className="w-5 h-5" />
          </button>
          <div>
            <h1 className="text-2xl font-bold text-gray-900">Create Invoice</h1>
            <p className="text-sm text-gray-500 mt-1">Generate a new GST compliant invoice.</p>
          </div>
        </div>
        <button
          onClick={handleSubmit}
          disabled={loading || !formData.customer_id}
          className="inline-flex items-center px-6 py-2.5 bg-primary hover:bg-primary/90 text-white font-medium rounded-lg transition-colors disabled:opacity-70 disabled:cursor-not-allowed shadow-sm"
        >
          {loading ? <Loader2 className="w-5 h-5 mr-2 animate-spin" /> : <Save className="w-5 h-5 mr-2" />}
          {loading ? 'Saving...' : 'Save Invoice'}
        </button>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2 space-y-6">
          <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 className="text-lg font-bold text-gray-900 mb-4 flex items-center">
              <FileText className="w-5 h-5 mr-2 text-gray-400" />
              Invoice Details
            </h2>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label className="block text-sm font-semibold text-color-white">Customer *</label>
                <select
                  required
                  className="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ring focus:border-ring transition-all outline-none bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                  value={formData.customer_id}
                  onChange={(e) => setFormData({ ...formData, customer_id: e.target.value })}
                >
                  <option value="">Select a customer</option>
                  {customers.map(c => (
                    <option key={c.id} value={c.id}>{c.name}</option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-semibold text-color-white">Due Date</label>
                <input
                  type="date"
                  className="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ring focus:border-ring transition-all outline-none dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                  value={formData.due_date}
                  onChange={(e) => setFormData({ ...formData, due_date: e.target.value })}
                />
              </div>

              <div>
                <label className="block text-sm font-semibold text-color-white">GST Type</label>
                <select
                  className="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ring focus:border-ring transition-all outline-none bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                  value={formData.gst_type}
                  onChange={(e) => setFormData({ ...formData, gst_type: e.target.value })}
                >
                  <option value="cgst">CGST + SGST (Intra-State)</option>
                  <option value="igst">IGST (Inter-State)</option>
                </select>
              </div>

              <div>
                <label className="block text-sm font-semibold text-color-white">GST Rate (%)</label>
                <select
                  className="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ring focus:border-ring transition-all outline-none bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                  value={formData.gst_rate}
                  onChange={(e) => setFormData({ ...formData, gst_rate: e.target.value })}
                >
                  <option value="0">0%</option>
                  <option value="5">5%</option>
                  <option value="12">12%</option>
                  <option value="18">18%</option>
                  <option value="28">28%</option>
                </select>
              </div>

              <div>
                <label className="block text-sm font-semibold text-color-white">Status</label>
                <select
                  className="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ring focus:border-ring transition-all outline-none bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                  value={formData.status}
                  onChange={(e) => setFormData({ ...formData, status: e.target.value })}
                >
                  <option value="Draft">Draft</option>
                  <option value="Sent">Sent</option>
                  <option value="Paid">Paid</option>
                  <option value="Partially Paid">Partially Paid</option>
                </select>
              </div>
            </div>
          </div>

          <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div className="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
              <h2 className="text-lg font-bold text-gray-900">Line Items</h2>
              <div className="text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded-md border border-blue-100 flex items-center">
                <Info className="w-3 h-3 mr-1" />
                GST at {formData.gst_rate || 0}% ({formData.gst_type === 'igst' ? 'IGST' : 'CGST+SGST'})
              </div>
            </div>

            <div className="overflow-x-auto">
              <table className="w-full text-left border-collapse">
                <thead>
                  <tr className="bg-white border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <th className="p-4 w-[35%]">Product / Description</th>
                    <th className="p-4 w-[15%]">HSN</th>
                    <th className="p-4 w-[12%]">Qty</th>
                    <th className="p-4 w-[15%]">Rate (₹)</th>
                    <th className="p-4 w-[15%] text-right">Total</th>
                    <th className="p-4 w-[5%]"></th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100 bg-white">
                  {items.map((item, index) => (
                    <tr key={item.id} className="hover:bg-gray-50/50 transition-colors">
                      <td className="p-4 align-top space-y-2">
                        <select
                          className="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-ring focus:border-ring dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                          value={item.product_id}
                          onChange={(e) => handleItemChange(item.id, 'product_id', e.target.value)}
                        >
                          <option value="">Custom Item</option>
                          {products.map(p => (
                            <option key={p.id} value={p.id}>{p.name}</option>
                          ))}
                        </select>
                        <input
                          type="text"
                          placeholder="Description..."
                          className="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-ring focus:border-ring dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                          value={item.item_description}
                          onChange={(e) => handleItemChange(item.id, 'item_description', e.target.value)}
                          required
                        />
                      </td>
                      <td className="p-4 align-top">
                        <input
                          type="text"
                          placeholder="HSN"
                          className="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-ring focus:border-ring dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                          value={item.hsn_code}
                          onChange={(e) => handleItemChange(item.id, 'hsn_code', e.target.value)}
                        />
                      </td>
                      <td className="p-4 align-top">
                        <input
                          type="number"
                          min="1"
                          className="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-ring focus:border-ring dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                          value={item.quantity}
                          onChange={(e) => handleItemChange(item.id, 'quantity', e.target.value)}
                          required
                        />
                      </td>
                      <td className="p-4 align-top">
                        <input
                          type="number"
                          min="0"
                          step="0.01"
                          className="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-ring focus:border-ring dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                          value={item.unit_price}
                          onChange={(e) => handleItemChange(item.id, 'unit_price', e.target.value)}
                          required
                        />
                      </td>
                      <td className="p-4 align-top text-right font-medium text-gray-900">
                        ₹{((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0)).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                      </td>
                      <td className="p-4 align-top text-center">
                        <button
                          type="button"
                          onClick={() => removeItem(item.id)}
                          className="text-gray-400 hover:text-red-600 transition-colors mt-2"
                        >
                          <Trash2 className="w-5 h-5" />
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            <div className="p-4 border-t border-gray-200 bg-gray-50">
              <button
                type="button"
                onClick={addItem}
                className="inline-flex items-center px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors"
              >
                <Plus className="w-4 h-4 mr-2" />
                Add Line Item
              </button>
            </div>
          </div>
        </div>

        <div className="space-y-6">
          <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 className="text-lg font-bold text-gray-900 mb-4">Summary</h2>

            <div className="space-y-3 text-sm">
              <div className="flex justify-between text-gray-600">
                <span>Subtotal</span>
                <span className="font-medium text-gray-900">₹{totals.subtotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
              </div>

              {totals.cgst_total > 0 && (
                <div className="flex justify-between text-gray-600">
                  <span>CGST</span>
                  <span className="font-medium text-gray-900">₹{totals.cgst_total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                </div>
              )}

              {totals.sgst_total > 0 && (
                <div className="flex justify-between text-gray-600">
                  <span>SGST</span>
                  <span className="font-medium text-gray-900">₹{totals.sgst_total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                </div>
              )}

              {totals.igst_total > 0 && (
                <div className="flex justify-between text-gray-600">
                  <span>IGST</span>
                  <span className="font-medium text-gray-900">₹{totals.igst_total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                </div>
              )}

              <div className="pt-3 mt-3 border-t border-gray-200 flex justify-between items-center">
                <span className="font-bold text-gray-900 text-base">Grand Total</span>
                <span className="font-bold text-primary text-xl">
                  ₹{totals.grand_total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                </span>
              </div>
            </div>
          </div>

          <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 className="text-lg font-bold text-gray-900 mb-4">Additional Info</h2>
            <div className="space-y-4">
              <div>
                <label className="block text-sm font-semibold text-color-white">Notes (Internal/Customer)</label>
                <textarea
                  className="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ring focus:border-ring transition-all outline-none resize-none dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                  rows="3"
                  value={formData.notes}
                  onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
                ></textarea>
              </div>
              <div>
                <label className="block text-sm font-semibold text-color-white">Terms & Conditions</label>
                <textarea
                  className="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ring focus:border-ring transition-all outline-none resize-none dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                  rows="4"
                  value={formData.terms}
                  onChange={(e) => setFormData({ ...formData, terms: e.target.value })}
                ></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
