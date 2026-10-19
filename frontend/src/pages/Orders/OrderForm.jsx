import React, { useEffect, useMemo, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { ArrowLeft, Plus, Trash2, Save, Info } from 'lucide-react';
import { ordersService } from '../../services/ordersService';
import { customersService } from '../../services/customersService';
import { suppliersService } from '../../services/suppliersService';
import { voucherService } from '../../services/accountingService';
import { ORDER_SIDES, isEditableOrderStatus, orderStatusLabel } from '../../config/orders';
import { formatMoney } from '../../utils/finance';

const inputClass = 'w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 text-sm focus:ring-orange-500 focus:border-orange-500';
const labelClass = 'block text-xs font-medium text-gray-600 mb-1';

const today = () => new Date().toISOString().split('T')[0];
const nullable = (value) => (value === '' || value === undefined || value === null ? null : value);
const round2 = (n) => Math.round((Number(n) || 0) * 100) / 100;
const round3 = (n) => Math.round((Number(n) || 0) * 1000) / 1000;

const firstError = (err) => {
  const errors = err?.response?.data?.data?.errors || err?.response?.data?.errors;
  if (errors) return Object.values(errors)[0]?.[0];
  return err?.response?.data?.data?.message || err?.response?.data?.message || 'Could not save the order.';
};

const newLine = () => ({
  id: Date.now() + Math.random(),
  product_id: '',
  item_description: '',
  hsn_code: '',
  unit: '',
  quantity: 1,
  rate: 0,
  discount_percent: 0,
  tax_rate: 18,
});

/**
 * Raise or edit an operational order.
 *
 * Same line-item shape as the item voucher form (product, qty, rate,
 * discount, tax), but nothing here reaches a ledger: the order records what
 * was agreed, and the books move only when Finance posts the bill the order
 * is later linked to. Totals shown live are recomputed by the server on
 * save, so they can never disagree with the register.
 */
const OrderForm = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const isEdit = Boolean(id);

  const [loading, setLoading] = useState(isEdit);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);

  const [side, setSide] = useState('sales');
  const [partyId, setPartyId] = useState('');
  const [form, setForm] = useState({
    order_date: today(),
    expected_delivery_date: '',
    reference_no: '',
    notes: '',
  });
  const [lines, setLines] = useState([newLine()]);
  const [parties, setParties] = useState([]);
  const [products, setProducts] = useState([]);
  const [order, setOrder] = useState(null); // edit mode: the loaded row, for status checks

  const sideMeta = ORDER_SIDES[side];

  useEffect(() => {
    let active = true;
    const load = side === 'purchase' ? suppliersService.list : customersService.list;
    load()
      .then((rows) => { if (active) setParties(rows); })
      .catch(() => { if (active) setParties([]); });
    return () => { active = false; };
  }, [side]);

  useEffect(() => {
    let active = true;
    voucherService.products(side === 'purchase' ? 'purchase' : 'sales')
      .then((rows) => { if (active) setProducts(rows); })
      .catch(() => { if (active) setProducts([]); });
    return () => { active = false; };
  }, [side]);

  useEffect(() => {
    if (!isEdit) return;
    let active = true;
    (async () => {
      setLoading(true);
      try {
        const loaded = await ordersService.get(id);
        if (!active) return;
        setOrder(loaded);
        setSide(loaded.side || 'sales');
        setPartyId(String(loaded.customer_id || loaded.supplier_id || ''));
        setForm({
          order_date: (loaded.order_date || '').slice(0, 10),
          expected_delivery_date: (loaded.expected_delivery_date || '').slice(0, 10),
          reference_no: loaded.reference_no || '',
          notes: loaded.notes || '',
        });
        setLines((loaded.items || []).map((it) => ({
          id: it.id || Date.now(),
          product_id: String(it.product_id || ''),
          item_description: it.item_description || '',
          hsn_code: it.hsn_code || '',
          unit: it.unit || '',
          quantity: String(it.quantity ?? '1'),
          rate: String(it.rate ?? '0'),
          discount_percent: String(it.discount_percent ?? '0'),
          tax_rate: String(it.tax_rate ?? '0'),
        })));
      } catch (err) {
        if (active) setError(firstError(err));
      } finally {
        if (active) setLoading(false);
      }
    })();
    return () => { active = false; };
  }, [id, isEdit]);

  const computedLines = useMemo(() => lines.map((l) => {
    const qty = round3(l.quantity);
    const rate = round2(l.rate);
    const discPct = round2(l.discount_percent);
    const taxRate = round2(l.tax_rate);
    const gross = round2(qty * rate);
    const disc = round2(gross * discPct / 100);
    const taxable = round2(gross - disc);
    const tax = round2(taxable * taxRate / 100);
    const total = round2(taxable + tax);
    return { ...l, qty, rate, discPct, taxRate, gross, disc, taxable, tax, total };
  }), [lines]);

  const totals = useMemo(() => {
    const subtotal = round2(computedLines.reduce((s, x) => s + x.gross, 0));
    const discount_total = round2(computedLines.reduce((s, x) => s + x.disc, 0));
    const tax_total = round2(computedLines.reduce((s, x) => s + x.tax, 0));
    return { subtotal, discount_total, tax_total, total_amount: round2(subtotal - discount_total + tax_total) };
  }, [computedLines]);

  const handleLineChange = (lineId, field, value) => {
    setLines((prev) => prev.map((l) => {
      if (l.id !== lineId) return l;
      const upd = { ...l, [field]: value };
      if (field === 'product_id' && value) {
        const p = products.find((pr) => String(pr.id) === String(value));
        if (p) {
          upd.item_description = upd.item_description || p.name || '';
          upd.hsn_code = upd.hsn_code || p.hsn_code || '';
          upd.unit = upd.unit || p.unit || '';
        }
      }
      return upd;
    }));
  };

  const addLine = () => setLines((prev) => [...prev, newLine()]);
  const removeLine = (lineId) => setLines((prev) => (prev.length <= 1 ? prev : prev.filter((l) => l.id !== lineId)));

  const validate = () => {
    if (!partyId) return `Choose the ${sideMeta.partyLabel.toLowerCase()}.`;
    if (!form.order_date) return 'Pick an order date.';
    if (lines.some((l) => !l.item_description)) return 'Every line needs a description.';
    if (lines.some((l) => !(Number(l.quantity) > 0))) return 'Quantity must be greater than zero.';
    if (lines.some((l) => Number(l.rate) < 0)) return 'Rate cannot be negative.';
    return null;
  };

  const submit = async (e) => {
    e.preventDefault();
    if (saving) return;
    if (isEdit && order && !isEditableOrderStatus(order.status)) {
      setError(`This order is ${orderStatusLabel(order.status).toLowerCase()} and can no longer be edited.`);
      return;
    }
    const problem = validate();
    if (problem) { setError(problem); return; }
    setError(null);
    setSaving(true);
    const payload = {
      side,
      customer_id: side === 'sales' ? Number(partyId) : null,
      supplier_id: side === 'purchase' ? Number(partyId) : null,
      order_date: form.order_date,
      expected_delivery_date: nullable(form.expected_delivery_date),
      reference_no: nullable(form.reference_no),
      notes: nullable(form.notes),
      items: lines.map((l) => ({
        product_id: l.product_id ? Number(l.product_id) : null,
        item_description: l.item_description,
        hsn_code: nullable(l.hsn_code),
        unit: nullable(l.unit),
        quantity: round3(l.quantity),
        rate: round2(l.rate),
        discount_percent: round2(l.discount_percent),
        tax_rate: round2(l.tax_rate),
      })),
    };
    try {
      const saved = isEdit
        ? await ordersService.update(id, payload)
        : await ordersService.create(payload);
      navigate(`/dashboard/orders/${saved.id}`);
    } catch (err) {
      setError(firstError(err));
      setSaving(false);
    }
  };

  if (loading) return <div className="p-6 text-gray-500">Loading order…</div>;

  const lockedByStatus = isEdit && order && !isEditableOrderStatus(order.status);

  return (
    <div className="p-6 max-w-7xl mx-auto">
      <div className="flex items-center gap-3 mb-6">
        <button onClick={() => navigate('/dashboard/orders')} className="text-gray-500 hover:text-gray-800" title="Back to orders">
          <ArrowLeft className="w-5 h-5" />
        </button>
        <div>
          <h1 className="text-2xl font-bold text-gray-900">
            {isEdit ? 'Edit' : 'New'} {sideMeta.label} Order
          </h1>
          <p className="text-sm text-gray-500">
            Operational tracking only — the books move when the linked bill is posted in Finance.
          </p>
        </div>
      </div>

      {lockedByStatus && (
        <div className="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
          This order is <span className="font-semibold">{orderStatusLabel(order.status)}</span> and can no longer be edited —
          only the document link may still be corrected while advancing it.
        </div>
      )}
      {error && <div className="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{error}</div>}

      <form onSubmit={submit} className="space-y-6">
        <div className="bg-white rounded-lg shadow p-5 space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label className={labelClass}>Side *</label>
              <select
                value={side}
                disabled={isEdit}
                onChange={(e) => { setSide(e.target.value); setPartyId(''); }}
                className={`${inputClass} disabled:bg-gray-100 disabled:text-gray-500`}
              >
                {Object.values(ORDER_SIDES).map((s) => (
                  <option key={s.key} value={s.key}>{s.label}</option>
                ))}
              </select>
            </div>
            <div>
              <label className={labelClass}>{sideMeta.partyLabel} *</label>
              <select required value={partyId} onChange={(e) => setPartyId(e.target.value)} className={inputClass}>
                <option value="">— Select {sideMeta.partyLabel.toLowerCase()} —</option>
                {parties.map((p) => <option key={p.id} value={p.id}>{p.name}</option>)}
              </select>
            </div>
            <div>
              <label className={labelClass}>Order date *</label>
              <input type="date" required value={form.order_date} onChange={(e) => setForm({ ...form, order_date: e.target.value })} className={inputClass} />
            </div>
            <div>
              <label className={labelClass}>Delivery due</label>
              <input type="date" value={form.expected_delivery_date} onChange={(e) => setForm({ ...form, expected_delivery_date: e.target.value })} className={inputClass} />
            </div>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label className={labelClass}>Reference no.</label>
              <input value={form.reference_no} onChange={(e) => setForm({ ...form, reference_no: e.target.value })} className={inputClass} placeholder="PO/SO ref" />
            </div>
            <div className="md:col-span-2">
              <label className={labelClass}>Notes</label>
              <input value={form.notes} onChange={(e) => setForm({ ...form, notes: e.target.value })} className={inputClass} />
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow p-5">
          <div className="flex items-center justify-between mb-2">
            <h3 className="text-sm font-semibold text-gray-800">Line Items</h3>
            <span className="text-xs text-gray-400 inline-flex items-center"><Info className="w-3 h-3 mr-1" />Tax at line level; the split belongs to the GST invoice</span>
          </div>
          <div className="overflow-x-auto">
            <table className="min-w-full text-xs md:text-sm">
              <thead>
                <tr className="text-xs text-gray-500 uppercase tracking-wider">
                  <th className="px-2 py-2 text-left">Product</th>
                  <th className="px-2 py-2 text-left">Description *</th>
                  <th className="px-2 py-2 text-left w-28">HSN</th>
                  <th className="px-2 py-2 text-left w-20">Unit</th>
                  <th className="px-2 py-2 text-left w-24">Qty *</th>
                  <th className="px-2 py-2 text-left w-28">Rate *</th>
                  <th className="px-2 py-2 text-left w-24">Disc %</th>
                  <th className="px-2 py-2 text-left w-24">Tax %</th>
                  <th className="px-2 py-2 text-right w-28">Taxable</th>
                  <th className="px-2 py-2 text-right w-28">Tax</th>
                  <th className="px-2 py-2 text-right w-28">Total</th>
                  <th className="px-2 py-2 w-10"></th>
                </tr>
              </thead>
              <tbody>
                {computedLines.map((l) => (
                  <tr key={l.id}>
                    <td className="px-2 py-2 align-top">
                      <select value={l.product_id} onChange={(e) => handleLineChange(l.id, 'product_id', e.target.value)} className={inputClass}>
                        <option value="">— Custom —</option>
                        {products.map((p) => <option key={p.id} value={p.id}>{p.name}</option>)}
                      </select>
                    </td>
                    <td className="px-2 py-2 align-top">
                      <input value={l.item_description} onChange={(e) => handleLineChange(l.id, 'item_description', e.target.value)} className={inputClass} required />
                    </td>
                    <td className="px-2 py-2 align-top"><input value={l.hsn_code} onChange={(e) => handleLineChange(l.id, 'hsn_code', e.target.value)} className={inputClass} /></td>
                    <td className="px-2 py-2 align-top"><input value={l.unit} onChange={(e) => handleLineChange(l.id, 'unit', e.target.value)} className={inputClass} /></td>
                    <td className="px-2 py-2 align-top"><input type="number" step="0.001" min="0" value={l.quantity} onChange={(e) => handleLineChange(l.id, 'quantity', e.target.value)} className={inputClass} required /></td>
                    <td className="px-2 py-2 align-top"><input type="number" step="0.01" min="0" value={l.rate} onChange={(e) => handleLineChange(l.id, 'rate', e.target.value)} className={inputClass} required /></td>
                    <td className="px-2 py-2 align-top"><input type="number" step="0.01" min="0" max="100" value={l.discount_percent} onChange={(e) => handleLineChange(l.id, 'discount_percent', e.target.value)} className={inputClass} /></td>
                    <td className="px-2 py-2 align-top"><input type="number" step="0.01" min="0" max="100" value={l.tax_rate} onChange={(e) => handleLineChange(l.id, 'tax_rate', e.target.value)} className={inputClass} /></td>
                    <td className="px-2 py-2 align-top text-right text-gray-700">{formatMoney(l.taxable)}</td>
                    <td className="px-2 py-2 align-top text-right text-gray-700">{formatMoney(l.tax)}</td>
                    <td className="px-2 py-2 align-top text-right font-medium text-gray-900">{formatMoney(l.total)}</td>
                    <td className="px-2 py-2 align-top text-center">
                      <button type="button" onClick={() => removeLine(l.id)} disabled={lines.length <= 1} className="text-red-500 hover:text-red-700 disabled:opacity-30">
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          <button type="button" onClick={addLine} className="mt-3 inline-flex items-center text-sm text-orange-600 hover:text-orange-700 font-medium">
            <Plus className="w-4 h-4 mr-1" /> Add line
          </button>
        </div>

        <div className="flex flex-wrap items-center justify-between gap-4">
          <p className="text-xs text-gray-400 max-w-md">
            Saving records the order and its status timeline; it posts nothing to the ledger.
          </p>
          <div className="w-64 bg-gray-50 rounded-xl border border-gray-100 p-5 space-y-2.5 text-sm">
            <div className="flex justify-between text-gray-500"><span>Subtotal</span><span className="font-medium text-gray-900">{formatMoney(totals.subtotal)}</span></div>
            {totals.discount_total !== 0 && (
              <div className="flex justify-between text-gray-500"><span>Discount</span><span className="font-medium text-gray-900">− {formatMoney(totals.discount_total)}</span></div>
            )}
            {totals.tax_total !== 0 && (
              <div className="flex justify-between text-gray-500"><span>Tax</span><span className="font-medium text-gray-900">{formatMoney(totals.tax_total)}</span></div>
            )}
            <div className="pt-3 mt-1 border-t border-gray-200 flex justify-between items-center">
              <span className="font-bold text-gray-900">Total</span>
              <span className="font-bold text-orange-600 text-lg">{formatMoney(totals.total_amount)}</span>
            </div>
          </div>
        </div>

        <div className="flex justify-end gap-2">
          <button type="button" onClick={() => navigate('/dashboard/orders')} className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
          <button type="submit" disabled={saving || lockedByStatus} className="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg inline-flex items-center disabled:opacity-60">
            <Save className="w-4 h-4 mr-2" /> {saving ? 'Saving…' : isEdit ? 'Save Changes' : 'Create Order'}
          </button>
        </div>
      </form>
    </div>
  );
};

export default OrderForm;
