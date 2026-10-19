import React, { useEffect, useMemo, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { ArrowLeft, Plus, Trash2, Save, Info } from 'lucide-react';
import { useLedgerStore } from '../../store/accountingStore';
import { voucherService } from '../../services/accountingService';
import { productsService } from '../../services/productsService';
import { VOUCHER_TYPES, partyTypeForVoucher } from '../../config/vouchers';
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
  return err?.response?.data?.data?.message || err?.response?.data?.message || 'Could not save the voucher.';
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

const ItemVoucherFormPage = () => {
  const { id, type: typeParam } = useParams();
  const navigate = useNavigate();
  const ledgersStore = useLedgerStore();
  const isEdit = Boolean(id);

  const [type] = useState(typeParam || '');
  const [loading, setLoading] = useState(isEdit);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);

  const [form, setForm] = useState({ voucher_date: today(), reference_no: '', narration: '' });
  const [partyLedgerId, setPartyLedgerId] = useState('');
  const [gstType, setGstType] = useState('intra');
  const [roundOff, setRoundOff] = useState('0');
  const [lines, setLines] = useState([newLine()]);

  const [meta, setMeta] = useState(null);
  const [products, setProducts] = useState([]);

  const config = VOUCHER_TYPES[type] || { label: type };
  const partyOptionType = partyTypeForVoucher(type);

  useEffect(() => {
    ledgersStore.fetchItems({ status: 'active' }).catch(() => {});
    voucherService.meta().then(setMeta).catch(() => setMeta({}));
    productsService.list({}).then(setProducts).catch(() => setProducts([]));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    if (!isEdit || !type) return;
    let active = true;
    (async () => {
      setLoading(true);
      try {
        const voucher = await voucherService.get(id);
        if (!active) return;
        setForm({
          voucher_date: (voucher.voucher_date || '').slice(0, 10),
          reference_no: voucher.reference_no || '',
          narration: voucher.narration || '',
        });
        setPartyLedgerId(String(voucher.party_ledger_id || ''));
        setGstType(voucher.gst_type || 'intra');
        setRoundOff(String(voucher.round_off ?? '0'));
        setLines(
          (voucher.items || []).map((it) => ({
            id: it.id || Date.now(),
            product_id: String(it.product_id || ''),
            item_description: it.item_description || '',
            hsn_code: it.hsn_code || '',
            unit: it.unit || '',
            quantity: String(it.quantity ?? '1'),
            rate: String(it.rate ?? '0'),
            discount_percent: String(it.discount_percent ?? '0'),
            tax_rate: String(it.tax_rate ?? '0'),
          }))
        );
      } catch (err) {
        if (active) setError(firstError(err));
      } finally {
        if (active) setLoading(false);
      }
    })();
    return () => { active = false; };
  }, [id, isEdit, type]);

  const ledgers = ledgersStore.items;
  const partyLedgers = useMemo(() => ledgers.filter((l) => l.party_type === partyOptionType), [ledgers, partyOptionType]);

  const computedLines = useMemo(() => {
    return lines.map((l) => {
      const qty = round3(l.quantity);
      const rate = round2(l.rate);
      const discPct = round2(l.discount_percent);
      const taxRate = round2(l.tax_rate);
      const gross = round2(qty * rate);
      const disc = round2(gross * discPct / 100);
      const taxable = round2(gross - disc);
      const tax = round2(taxable * taxRate / 100);
      let cgst = 0, sgst = 0, igst = 0;
      if (gstType === 'inter') {
        igst = round2(tax);
      } else {
        cgst = round2(tax / 2);
        sgst = round2(tax - cgst);
      }
      const lineTax = round2(cgst + sgst + igst);
      const total = round2(taxable + lineTax);
      return { ...l, qty, rate, gross, disc, taxable, tax, cgst, sgst, igst, lineTax, total };
    });
  }, [lines, gstType]);

  const totals = useMemo(() => {
    const taxable_total = round2(computedLines.reduce((s, x) => s + x.taxable, 0));
    const discount_total = round2(computedLines.reduce((s, x) => s + x.disc, 0));
    const cgst_total = round2(computedLines.reduce((s, x) => s + x.cgst, 0));
    const sgst_total = round2(computedLines.reduce((s, x) => s + x.sgst, 0));
    const igst_total = round2(computedLines.reduce((s, x) => s + x.igst, 0));
    const tax_total = round2(cgst_total + sgst_total + igst_total);
    const ro = round2(roundOff);
    const grand = round2(taxable_total + tax_total + ro);
    return { taxable_total, discount_total, cgst_total, sgst_total, igst_total, tax_total, round_off: ro, grand };
  }, [computedLines, roundOff]);

  const handleLineChange = (id, field, value) => {
    setLines((prev) => prev.map((l) => {
      if (l.id === id) {
        const upd = { ...l, [field]: value };
        if (field === 'product_id' && value) {
          const p = products.find((pr) => String(pr.id) === String(value));
          if (p) {
            upd.item_description = upd.item_description || p.name || '';
            upd.hsn_code = upd.hsn_code || p.hsn_code || '';
          }
        }
        return upd;
      }
      return l;
    }));
  };

  const addLine = () => setLines((prev) => [...prev, newLine()]);
  const removeLine = (id) => setLines((prev) => (prev.length <= 1 ? prev : prev.filter((l) => l.id !== id)));

  const validate = () => {
    if (!form.voucher_date) return 'Pick a voucher date.';
    if (!partyLedgerId) return 'Choose the party ledger.';
    if (lines.some((l) => !l.item_description)) return 'Every line needs a description.';
    if (lines.some((l) => !(Number(l.quantity) > 0))) return 'Quantity must be greater than zero.';
    if (lines.some((l) => Number(l.rate) < 0)) return 'Rate cannot be negative.';
    if (totals.grand <= 0) return 'The voucher total must be greater than zero.';
    return null;
  };

  const submit = async (e) => {
    e.preventDefault();
    if (saving) return;
    const problem = validate();
    if (problem) { setError(problem); return; }
    setError(null); setSaving(true);
    const payload = {
      voucher_type: type,
      voucher_date: form.voucher_date,
      reference_no: nullable(form.reference_no),
      narration: nullable(form.narration),
      party_ledger_id: Number(partyLedgerId),
      gst_type: gstType,
      round_off: round2(roundOff),
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
      if (isEdit) await voucherService.update(id, payload);
      else await voucherService.create(payload);
      navigate(`/dashboard/accounting/${type}`);
    } catch (err) {
      setError(firstError(err));
      setSaving(false);
    }
  };

  if (loading) return <div className="p-6 text-gray-500">Loading voucher…</div>;

  const baseLedgerLabel = config.key === 'sales' || config.key === 'credit-note' ? 'Sales ledger' : 'Purchase ledger';
  const defaults = meta?.defaults || {};

  return (
    <div className="p-6 max-w-7xl mx-auto">
      <div className="flex items-center justify-between gap-3 mb-6">
        <div className="flex items-center gap-3">
          <button onClick={() => navigate(`/dashboard/accounting/${type}`)} className="text-gray-500 hover:text-gray-800" title="Back to register">
            <ArrowLeft className="w-5 h-5" />
          </button>
          <div>
            <h1 className="text-2xl font-bold text-gray-900">{isEdit ? 'Edit' : 'New'} {config.label} Voucher</h1>
            <p className="text-sm text-gray-500">Item-based voucher with GST</p>
          </div>
        </div>
      </div>
      {error && <div className="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{error}</div>}
      <form onSubmit={submit} className="space-y-6">
        <div className="bg-white rounded-lg shadow p-5 space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label className={labelClass}>Voucher date *</label>
              <input type="date" required value={form.voucher_date} onChange={(e) => setForm({ ...form, voucher_date: e.target.value })} className={inputClass} />
            </div>
            <div>
              <label className={labelClass}>Reference no.</label>
              <input value={form.reference_no} onChange={(e) => setForm({ ...form, reference_no: e.target.value })} className={inputClass} placeholder="Bill/Ref" />
            </div>
            <div>
              <label className={labelClass}>Party ledger *</label>
              <select required value={partyLedgerId} onChange={(e) => setPartyLedgerId(e.target.value)} className={inputClass}>
                <option value="">— Select {partyOptionType} —</option>
                {partyLedgers.map((l) => <option key={l.id} value={l.id}>{l.name}</option>)}
              </select>
            </div>
            <div>
              <label className={labelClass}>GST Type</label>
              <select value={gstType} onChange={(e) => setGstType(e.target.value)} className={inputClass}>
                <option value="intra">Intra-State (CGST+SGST)</option>
                <option value="inter">Inter-State (IGST)</option>
              </select>
            </div>
          </div>
          <div>
            <label className={labelClass}>Narration</label>
            <input value={form.narration} onChange={(e) => setForm({ ...form, narration: e.target.value })} className={inputClass} />
          </div>
        </div>

        <div className="bg-white rounded-lg shadow p-5">
          <div className="flex items-center justify-between mb-2">
            <h3 className="text-sm font-semibold text-gray-800">Line Items</h3>
            <span className="text-xs text-gray-400 inline-flex items-center"><Info className="w-3 h-3 mr-1" />GST at line level</span>
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
                    <td className="px-2 py-2 align-top text-right text-gray-700">{formatMoney(l.lineTax)}</td>
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

        <div className="bg-white rounded-lg shadow p-5 grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h4 className="text-sm font-semibold text-gray-800 mb-3">Ledgers (defaults)</h4>
            <div className="space-y-2 text-sm text-gray-600">
              <div className="flex justify-between"><span>{baseLedgerLabel}</span><span>{defaults[config.key === 'sales' || config.key === 'credit-note' ? 'sales_ledger' : 'purchase_ledger']?.name || '—'}</span></div>
              <div className="flex justify-between"><span>CGST</span><span>{defaults.cgst_ledger?.name || '—'}</span></div>
              <div className="flex justify-between"><span>SGST</span><span>{defaults.sgst_ledger?.name || '—'}</span></div>
              <div className="flex justify-between"><span>IGST</span><span>{defaults.igst_ledger?.name || '—'}</span></div>
              <div className="flex justify-between"><span>Round Off</span><span>{defaults.round_off_ledger?.name || '—'}</span></div>
            </div>
          </div>
          <div>
            <h4 className="text-sm font-semibold text-gray-800 mb-3">Totals</h4>
            <div className="space-y-2 text-sm">
              <div className="flex justify-between text-gray-600"><span>Taxable</span><span>{formatMoney(totals.taxable_total)}</span></div>
              <div className="flex justify-between text-gray-600"><span>Discount</span><span>{formatMoney(totals.discount_total)}</span></div>
              {gstType === 'intra' && totals.cgst_total !== 0 && <div className="flex justify-between text-gray-600"><span>CGST</span><span>{formatMoney(totals.cgst_total)}</span></div>}
              {gstType === 'intra' && totals.sgst_total !== 0 && <div className="flex justify-between text-gray-600"><span>SGST</span><span>{formatMoney(totals.sgst_total)}</span></div>}
              {gstType === 'inter' && totals.igst_total !== 0 && <div className="flex justify-between text-gray-600"><span>IGST</span><span>{formatMoney(totals.igst_total)}</span></div>}
              <div className="flex justify-between text-gray-600"><span>Tax Total</span><span>{formatMoney(totals.tax_total)}</span></div>
              <div className="flex justify-between items-center text-gray-600">
                <span>Round Off</span>
                <input type="number" step="0.01" value={roundOff} onChange={(e) => setRoundOff(e.target.value)} className="w-24 border border-gray-300 rounded-lg px-2 py-1 text-sm" />
              </div>
              <div className="pt-2 mt-2 border-t border-gray-100 flex justify-between font-semibold text-gray-900"><span>Grand Total</span><span>{formatMoney(totals.grand)}</span></div>
            </div>
          </div>
        </div>

        <div className="flex justify-end gap-2">
          <button type="button" onClick={() => navigate(`/dashboard/accounting/${type}`)} className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
          <button type="submit" disabled={saving} className="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg inline-flex items-center disabled:opacity-60">
            <Save className="w-4 h-4 mr-2" /> {saving ? 'Saving…' : 'Save Voucher'}
          </button>
        </div>
      </form>
    </div>
  );
};

export default ItemVoucherFormPage;