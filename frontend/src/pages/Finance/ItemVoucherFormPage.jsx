import React, { useEffect, useMemo, useRef, useState } from 'react';
import { createPortal } from 'react-dom';
import { useNavigate, useParams } from 'react-router-dom';
import { ArrowLeft, Plus, Trash2, Save, Info, PackageCheck, AlertTriangle, RefreshCw } from 'lucide-react';
import { useLedgerStore } from '../../store/accountingStore';
import { voucherService } from '../../services/accountingService';
import { useStockReceiptStore } from '../../store/stockReceiptStore';
import PermissionGate from '../../components/Guards/PermissionGate';
import StockReceiptPanel from '../../components/Accounting/StockReceiptPanel';
import StockReceiptModal from '../../components/Accounting/StockReceiptModal';
import { VOUCHER_TYPES, partyTypeForVoucher, tracksStock, movesStockOnSave, billSidesForVoucher, BILL_TYPES_FOR_SIDE } from '../../config/vouchers';
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

/**
 * A product picker that searches as you type.
 *
 * Matches on name, SKU, HSN and dimension, because the same fitting is often
 * remembered by its code or size rather than its name. Picking one hands the
 * whole product back to the caller, which stamps the line's description (the
 * product name), HSN and unit.
 *
 * The menu is portalled to `document.body` and positioned with fixed
 * coordinates taken from the input. The line-item table scrolls horizontally
 * (`overflow-x-auto`), which clips any absolutely-positioned child; a portal
 * sits outside that box so the menu is never cut off or hidden behind the
 * rows below it.
 */
const ProductPicker = ({ value, products, onPick }) => {
  const [open, setOpen] = useState(false);
  const [query, setQuery] = useState('');
  const [highlight, setHighlight] = useState(0);
  const [rect, setRect] = useState(null);
  const boxRef = useRef(null);
  const menuRef = useRef(null);

  const selected = products.find((p) => String(p.id) === String(value));

  const matches = useMemo(() => {
    const needle = query.trim().toLowerCase();
    if (!needle) return products.slice(0, 50);
    return products
      .filter((p) =>
        [p.name, p.sku, p.hsn_code, p.dimension]
          .some((field) => String(field || '').toLowerCase().includes(needle))
      )
      .slice(0, 50);
  }, [products, query]);

  /** Pin the menu to the input's viewport box, opening upward when short on room. */
  const measure = () => {
    const input = boxRef.current?.querySelector('input');
    if (!input) return;
    const r = input.getBoundingClientRect();
    const menuHeight = 260;
    const below = window.innerHeight - r.bottom;
    const flip = below < menuHeight && r.top > below;
    setRect({
      left: r.left,
      width: Math.max(r.width, 260),
      top: flip ? undefined : r.bottom + 4,
      bottom: flip ? window.innerHeight - r.top + 4 : undefined,
    });
  };

  const openMenu = () => { measure(); setOpen(true); };

  // Keep the menu glued to the input while the page scrolls or resizes.
  useEffect(() => {
    if (!open) return undefined;
    const onScrollOrResize = () => measure();
    window.addEventListener('scroll', onScrollOrResize, true);
    window.addEventListener('resize', onScrollOrResize);
    return () => {
      window.removeEventListener('scroll', onScrollOrResize, true);
      window.removeEventListener('resize', onScrollOrResize);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [open]);

  // Close when focus (or a click) leaves both the input and the portalled menu.
  useEffect(() => {
    if (!open) return undefined;
    const onDown = (e) => {
      const inInput = boxRef.current?.contains(e.target);
      const inMenu = menuRef.current?.contains(e.target);
      if (!inInput && !inMenu) setOpen(false);
    };
    document.addEventListener('mousedown', onDown);
    return () => document.removeEventListener('mousedown', onDown);
  }, [open]);

  const choose = (product) => {
    onPick(product);
    setQuery('');
    setOpen(false);
  };

  const onKeyDown = (e) => {
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      if (!open) { openMenu(); return; }
      setHighlight((h) => Math.min(h + 1, matches.length - 1));
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      setHighlight((h) => Math.max(h - 1, 0));
    } else if (e.key === 'Enter' && open && matches[highlight]) {
      e.preventDefault();
      choose(matches[highlight]);
    } else if (e.key === 'Escape') {
      setOpen(false);
    }
  };

  const menu = open && rect ? createPortal(
    <div
      ref={menuRef}
      style={{
        position: 'fixed',
        left: rect.left,
        width: rect.width,
        top: rect.top,
        bottom: rect.bottom,
        zIndex: 1000,
      }}
      className="max-h-64 overflow-auto rounded-lg border border-gray-200 bg-white shadow-xl"
    >
      {matches.length === 0 && (
        <div className="px-3 py-2 text-xs text-gray-500">No products match “{query}”.</div>
      )}
      {matches.map((p, index) => (
        <button
          key={p.id}
          type="button"
          onMouseEnter={() => setHighlight(index)}
          onClick={() => choose(p)}
          className={`block w-full px-3 py-2 text-left text-sm ${index === highlight ? 'bg-orange-50' : 'bg-white hover:bg-gray-50'}`}
        >
          <div className="font-medium text-gray-800">{p.name}</div>
          <div className="text-[11px] text-gray-500">
            {[p.sku, p.dimension, p.hsn_code && `HSN ${p.hsn_code}`].filter(Boolean).join(' · ') || '—'}
          </div>
        </button>
      ))}
    </div>,
    document.body
  ) : null;

  return (
    <div ref={boxRef} className="relative min-w-[14rem]">
      <input
        value={open ? query : (selected?.name || query)}
        placeholder={selected ? selected.name : 'Search name / SKU / HSN…'}
        onFocus={openMenu}
        onClick={openMenu}
        onChange={(e) => { if (!open) measure(); setQuery(e.target.value); setOpen(true); setHighlight(0); }}
        onKeyDown={onKeyDown}
        className={inputClass}
      />
      {selected && !open && (
        <div className="mt-0.5 truncate text-[11px] text-gray-500">
          {[selected.sku, selected.dimension].filter(Boolean).join(' · ') || 'Product'}
        </div>
      )}
      {menu}
    </div>
  );
};

const newLine = () => ({
  id: Date.now() + Math.random(),
  product_id: '',
  product_name: '',
  hsn_code: '',
  unit: '',
  quantity: 1,
  rate: 0,
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

  const [form, setForm] = useState({ voucher_date: today(), due_date: '', reference_no: '', narration: '' });
  const [partyLedgerId, setPartyLedgerId] = useState('');
  const [gstType, setGstType] = useState('intra');
  const [roundOff, setRoundOff] = useState('0');
  // Tax and discount are document-level: one rate on the whole bill, and one
  // discount entered either as a percentage or a flat amount.
  const [taxRate, setTaxRate] = useState('18');
  const [discountType, setDiscountType] = useState('percent');
  const [discountValue, setDiscountValue] = useState('0');
  const [lines, setLines] = useState([newLine()]);

  const [meta, setMeta] = useState(null);
  const [products, setProducts] = useState([]);
  const [availability, setAvailability] = useState(null);
  const [receiptModalOpen, setReceiptModalOpen] = useState(false);

  // Bill-wise allocation: which of the party's outstanding bills this document
  // settles, and by how much.
  const [allocations, setAllocations] = useState({});
  const [loadingBills, setLoadingBills] = useState(false);

  const receipts = useStockReceiptStore();

  const config = VOUCHER_TYPES[type] || { label: type };
  const partyOptionType = partyTypeForVoucher(type);
  const outbound = config.stock === 'out';
  const tracks = tracksStock(type);
  // A note moves its stock whole at save, so it shows no receipt / delivery
  // button — there is nothing to receive in instalments.
  const onSaveStock = movesStockOnSave(type);
  const billSide = billSidesForVoucher(type);
  const billsEnabled = Boolean(config.bills);

  /** On-hand per product, so a sale shows when the warehouse is short. */
  const loadAvailability = () => {
    if (!isEdit || !tracks) return;
    voucherService.stockAvailability(id).then(setAvailability).catch(() => setAvailability(null));
  };

  useEffect(() => {
    ledgersStore.fetchItems({ status: 'active' }).catch(() => {});
    voucherService.meta().then(setMeta).catch(() => setMeta({}));
    voucherService.products(type).then(setProducts).catch(() => setProducts([]));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    if (!isEdit || !tracks) return;
    receipts.fetchForVoucher(id).catch(() => {});
    loadAvailability();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [id, isEdit, tracks]);

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
          due_date: (voucher.due_date || '').slice(0, 10),
          reference_no: voucher.reference_no || '',
          narration: voucher.narration || '',
        });
        setPartyLedgerId(String(voucher.party_ledger_id || ''));
        setGstType(voucher.gst_type || 'intra');
        setRoundOff(String(voucher.round_off ?? '0'));

        // Bill-level tax seeded from the header. Vouchers keyed before this
        // existed carry the rate per line, so fall back to the first line that
        // has one rather than dropping the tax to zero.
        const headerRate = Number(voucher.tax_rate || 0);
        const lineRate = (voucher.items || []).reduce(
          (found, it) => (found || Number(it.tax_rate || 0)), 0
        );
        setTaxRate(String(headerRate || lineRate || 0));
        setDiscountType(voucher.discount_type === 'amount' ? 'amount' : 'percent');
        setDiscountValue(String(voucher.discount_value ?? '0'));

        // Seed any bill-wise allocation already on the document.
        const seeded = {};
        (voucher.bill_allocations || []).forEach((a) => {
          const key = `${a.bill_type}:${a.bill_id ?? ''}`;
          seeded[key] = {
            key,
            bill_type: a.bill_type,
            bill_id: a.bill_id ?? null,
            bill_name: a.bill_name || a.bill_type,
            balance: null,
            amount: String(a.amount ?? ''),
          };
        });
        setAllocations(seeded);

        setLines(
          (voucher.items || []).map((it) => ({
            id: it.id || Date.now(),
            product_id: String(it.product_id || ''),
            product_name: it.item_description || it.product?.name || '',
            hsn_code: it.hsn_code || '',
            unit: it.unit || '',
            quantity: String(it.quantity ?? '1'),
            rate: String(it.rate ?? '0'),
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

  /** Enough of the document for the receipt modal's header line. */
  const voucherForModal = useMemo(() => {
    const party = ledgers.find((l) => String(l.id) === String(partyLedgerId));
    return {
      id,
      voucher_no: form.reference_no || `#${id}`,
      voucher_type: type,
      party: party ? { name: party.name } : null,
    };
  }, [id, type, form.reference_no, partyLedgerId, ledgers]);

  const submitReceipt = async (payload) => {
    await receipts.createReceipt(id, payload);
    setReceiptModalOpen(false);
    loadAvailability();
  };

  /* --------------------------- bill-wise allocation --------------------- */

  const billTypes = useMemo(
    () => BILL_TYPES_FOR_SIDE[billSide || 'all'] || null,
    [billSide]
  );

  // Pull the party's outstanding bills so they can be allocated against. Kept
  // to the side the document settles: a credit note against sales documents, a
  // debit note against purchase ones. Purchase / sales see both.
  useEffect(() => {
    if (!billsEnabled || !partyLedgerId) return undefined;
    let active = true;

    (async () => {
      setLoadingBills(true);
      try {
        const data = await voucherService.partyOutstanding(partyLedgerId);
        if (!active) return;
        const bills = (data.bills || []).filter(
          (b) => !billTypes || billTypes.includes(b.bill_type)
        );
        setAllocations((prev) => {
          const rows = {};
          bills.forEach((b) => {
            const key = `${b.bill_type}:${b.bill_id ?? ''}`;
            rows[key] = {
              key,
              bill_type: b.bill_type,
              bill_id: b.bill_id ?? null,
              bill_name: b.bill_name,
              balance: b.balance,
              amount: prev[key]?.amount ?? '',
            };
          });
          // Keep allocations no longer outstanding (already settled) so an edit
          // never silently drops them.
          Object.values(prev).forEach((row) => {
            if (!rows[row.key] && (!billTypes || billTypes.includes(row.bill_type))) {
              rows[row.key] = row;
            }
          });
          if (!rows['advance:']) {
            rows['advance:'] = {
              key: 'advance:',
              bill_type: 'advance',
              bill_id: null,
              bill_name: 'On Account / Advance',
              balance: null,
              amount: '',
            };
          }
          return rows;
        });
      } catch {
        // Leave whatever was seeded; a missing outstanding list must not block
        // keying the document.
        if (active) setAllocations((prev) => prev);
      } finally {
        if (active) setLoadingBills(false);
      }
    })();

    return () => { active = false; };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [billsEnabled, partyLedgerId, billSide]);

  const updateAllocation = (key, value) => {
    setAllocations((prev) => ({ ...prev, [key]: { ...prev[key], amount: value } }));
  };

  /** Changing party invalidates the allocations made against the old one. */
  const changeParty = (value) => {
    setPartyLedgerId(value);
    setAllocations({});
  };

  /**
   * Line and document figures, mirroring ItemVoucherService exactly.
   *
   * The discount comes off the taxable value first, tax is charged on what
   * remains, and each line's share of the discount/tax is apportioned pro-rata
   * so the line columns add up to the document totals.
   */
  const computedLines = useMemo(() => {
    const byItemId = Object.fromEntries(
      (availability?.lines || []).map((line) => [String(line.voucher_item_id), line])
    );

    const rate = round2(taxRate);
    const gross = lines.map((l) => round2(round3(l.quantity) * round2(l.rate)));
    const taxableTotal = round2(gross.reduce((s, g) => s + g, 0));

    const discInput = round2(discountValue);
    let discountTotal = discountType === 'amount' ? discInput : round2(taxableTotal * discInput / 100);
    if (discountTotal > taxableTotal) discountTotal = taxableTotal;
    discountTotal = round2(discountTotal);

    const discountedTaxable = round2(taxableTotal - discountTotal);
    const taxTotal = round2(discountedTaxable * rate / 100);
    let cgstTotal = 0, sgstTotal = 0, igstTotal = 0;
    if (gstType === 'inter') {
      igstTotal = taxTotal;
    } else {
      cgstTotal = round2(taxTotal / 2);
      sgstTotal = round2(taxTotal - cgstTotal);
    }

    const rows = lines.map((l, index) => {
      const lineGross = gross[index];
      const disc = taxableTotal > 0 ? round2(lineGross / taxableTotal * discountTotal) : 0;
      const taxable = round2(lineGross - disc);
      const tax = round2(taxable * rate / 100);
      let cgst = 0, sgst = 0, igst = 0;
      if (gstType === 'inter') {
        igst = tax;
      } else {
        cgst = round2(tax / 2);
        sgst = round2(tax - cgst);
      }
      return {
        ...l,
        qty: round3(l.quantity),
        rate: round2(l.rate),
        gross: lineGross,
        disc,
        taxable,
        tax,
        cgst,
        sgst,
        igst,
        lineTax: round2(cgst + sgst + igst),
        total: round2(taxable + cgst + sgst + igst),
        stock: byItemId[String(l.id)] || null,
      };
    });

    const totals = {
      taxable_total: taxableTotal,
      discount_total: discountTotal,
      cgst_total: cgstTotal,
      sgst_total: sgstTotal,
      igst_total: igstTotal,
      tax_total: taxTotal,
    };
    return { rows, totals };
  }, [lines, gstType, taxRate, discountType, discountValue, availability]);

  const totals = useMemo(() => {
    const t = computedLines.totals;
    const ro = round2(roundOff);
    const grand = round2(t.taxable_total - t.discount_total + t.tax_total + ro);
    return { ...t, round_off: ro, grand };
  }, [computedLines, roundOff]);

  const allocatedTotal = useMemo(
    () => round2(Object.values(allocations).reduce((s, row) => s + (Number(row.amount) || 0), 0)),
    [allocations]
  );
  const allocationRemainder = round2(totals.grand - allocatedTotal);

  /**
   * Picking a product overwrites the line's description, HSN and unit.
   *
   * Overwriting rather than "fill if blank" is deliberate: the line must
   * describe the product actually chosen, not the one chosen a moment ago.
   */
  const pickProduct = (lineId, product) => {
    setLines((prev) => prev.map((l) => (
      l.id === lineId
        ? {
            ...l,
            product_id: product ? String(product.id) : '',
            product_name: product ? (product.name || '') : l.product_name,
            hsn_code: product ? (product.hsn_code || '') : l.hsn_code,
            unit: product ? (product.unit || '') : l.unit,
          }
        : l
    )));
  };

  const handleLineChange = (id, field, value) => {
    setLines((prev) => prev.map((l) => (l.id === id ? { ...l, [field]: value } : l)));
  };

  const addLine = () => setLines((prev) => [...prev, newLine()]);
  const removeLine = (id) => setLines((prev) => (prev.length <= 1 ? prev : prev.filter((l) => l.id !== id)));

  const validate = () => {
    if (!form.voucher_date) return 'Pick a voucher date.';
    if (!partyLedgerId) return 'Choose the party ledger.';
    if (lines.some((l) => !l.product_name)) return 'Every line needs a product.';
    if (lines.some((l) => !(Number(l.quantity) > 0))) return 'Quantity must be greater than zero.';
    if (lines.some((l) => Number(l.rate) < 0)) return 'Rate cannot be negative.';
    if (Number(discountValue) < 0) return 'The discount cannot be negative.';
    if (discountType === 'percent' && Number(discountValue) > 100) return 'A percentage discount cannot exceed 100%.';
    if (billsEnabled && allocationRemainder < -0.005) return 'Bill-wise allocations cannot exceed the voucher total.';
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
      due_date: nullable(form.due_date),
      reference_no: nullable(form.reference_no),
      narration: nullable(form.narration),
      party_ledger_id: Number(partyLedgerId),
      gst_type: gstType,
      round_off: round2(roundOff),
      // Bill-level tax and discount.
      tax_rate: round2(taxRate),
      discount_type: discountType,
      discount_value: round2(discountValue),
      items: lines.map((l) => ({
        product_id: l.product_id ? Number(l.product_id) : null,
        item_description: l.product_name,
        hsn_code: nullable(l.hsn_code),
        unit: nullable(l.unit),
        quantity: round3(l.quantity),
        rate: round2(l.rate),
      })),
    };

    // Bill-wise allocation, only for the types that carry it.
    if (billsEnabled) {
      const rows = Object.values(allocations)
        .map((row) => ({
          bill_type: row.bill_type,
          bill_id: row.bill_id ?? null,
          bill_name: row.bill_name || null,
          amount: round2(row.amount),
        }))
        .filter((row) => row.amount > 0);
      if (rows.length) payload.bill_allocations = rows;
    }
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
        {isEdit && tracks && !onSaveStock && (
          <PermissionGate module="stock-receipts" action="create" subject={`goods for this ${config.label.toLowerCase()}`}>
            <button
              type="button"
              onClick={() => setReceiptModalOpen(true)}
              disabled={receipts.summary?.status === 'complete'}
              title={receipts.summary?.status === 'complete' ? 'Everything has already arrived' : undefined}
              className="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-100 disabled:opacity-50"
            >
              <PackageCheck className="mr-2 h-4 w-4" />
              {outbound ? 'Record Delivery' : 'Receive Stock'}
            </button>
          </PermissionGate>
        )}
        {onSaveStock && (
          <span className="inline-flex items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-600">
            <PackageCheck className="mr-2 h-4 w-4" />
            Stock {outbound ? 'leaves' : 'returns to'} the warehouse when this note is saved
          </span>
        )}
      </div>
      {error && <div className="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{error}</div>}
      {isEdit && tracks && !onSaveStock && (
        <div className="mb-6">
          <StockReceiptPanel
            summary={receipts.summary}
            loading={receipts.loading}
            saving={receipts.saving}
            onReverse={(receipt) => {
              const reason = window.prompt(`Reverse ${receipt.receipt_no}? The stock movement will be undone.`, '');
              if (reason === null) return;
              receipts.reverseReceipt(receipt.id, reason || 'Reversed from the voucher screen')
                .then(loadAvailability)
                .catch((err) => setError(firstError(err)));
            }}
          />
        </div>
      )}
      <form onSubmit={submit} className="space-y-6">
        <div className="bg-white rounded-lg shadow p-5 space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
              <label className={labelClass}>Voucher date *</label>
              <input type="date" required value={form.voucher_date} onChange={(e) => setForm({ ...form, voucher_date: e.target.value })} className={inputClass} />
            </div>
            <div>
              <label className={labelClass}>Due date</label>
              <input type="date" value={form.due_date} onChange={(e) => setForm({ ...form, due_date: e.target.value })} className={inputClass} />
              <p className="mt-1 text-xs text-gray-400">Blank = party credit days</p>
            </div>
            <div>
              <label className={labelClass}>Reference no.</label>
              <input value={form.reference_no} onChange={(e) => setForm({ ...form, reference_no: e.target.value })} className={inputClass} placeholder="Bill/Ref" />
            </div>
            <div>
              <label className={labelClass}>Party ledger *</label>
              <select required value={partyLedgerId} onChange={(e) => changeParty(e.target.value)} className={inputClass}>
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
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4 rounded-lg bg-gray-50 p-3">
            <div>
              <label className={labelClass}>Tax % (whole bill)</label>
              <input type="number" step="0.01" min="0" max="100" value={taxRate} onChange={(e) => setTaxRate(e.target.value)} className={inputClass} />
            </div>
            <div>
              <label className={labelClass}>Discount type</label>
              <select value={discountType} onChange={(e) => setDiscountType(e.target.value)} className={inputClass}>
                <option value="percent">Percentage (%)</option>
                <option value="amount">Amount (₹)</option>
              </select>
            </div>
            <div>
              <label className={labelClass}>{discountType === 'amount' ? 'Discount amount (₹)' : 'Discount percentage (%)'}</label>
              <input type="number" step="0.01" min="0" value={discountValue} onChange={(e) => setDiscountValue(e.target.value)} className={inputClass} />
              <p className="mt-1 text-xs text-gray-400">Applied to the taxable value before tax</p>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow p-5">
          <div className="flex items-center justify-between mb-2">
            <h3 className="text-sm font-semibold text-gray-800">Line Items</h3>
            <span className="text-xs text-gray-400 inline-flex items-center"><Info className="w-3 h-3 mr-1" />GST &amp; discount on the whole bill</span>
          </div>
          <div className="overflow-x-auto">
            <table className="min-w-full text-xs md:text-sm">
              <thead>
                <tr className="text-xs text-gray-500 uppercase tracking-wider">
                  <th className="px-2 py-2 text-left">Product</th>
                  <th className="px-2 py-2 text-left w-28">HSN</th>
                  <th className="px-2 py-2 text-left w-24">Unit</th>
                  <th className="px-2 py-2 text-left w-24">Qty *</th>
                  <th className="px-2 py-2 text-left w-28">Rate *</th>
                  {tracks && <th className="px-2 py-2 text-left w-24">On hand</th>}
                  <th className="px-2 py-2 text-right w-28">Taxable</th>
                  <th className="px-2 py-2 text-right w-28">Tax</th>
                  <th className="px-2 py-2 text-right w-28">Total</th>
                  <th className="px-2 py-2 w-10"></th>
                </tr>
              </thead>
              <tbody>
                {computedLines.rows.map((l) => (
                  <tr key={l.id}>
                    <td className="px-2 py-2 align-top">
                      <ProductPicker
                        value={l.product_id}
                        products={products}
                        onPick={(product) => pickProduct(l.id, product)}
                      />
                      {l.product_name && (
                        <div className="mt-1 text-[11px] text-gray-600">{l.product_name}</div>
                      )}
                    </td>
                    <td className="px-2 py-2 align-top"><input value={l.hsn_code} onChange={(e) => handleLineChange(l.id, 'hsn_code', e.target.value)} className={inputClass} /></td>
                    <td className="px-2 py-2 align-top"><input value={l.unit} onChange={(e) => handleLineChange(l.id, 'unit', e.target.value)} className={inputClass} /></td>
                    <td className="px-2 py-2 align-top"><input type="number" step="0.001" min="0" value={l.quantity} onChange={(e) => handleLineChange(l.id, 'quantity', e.target.value)} className={inputClass} required /></td>
                    <td className="px-2 py-2 align-top"><input type="number" step="0.01" min="0" value={l.rate} onChange={(e) => handleLineChange(l.id, 'rate', e.target.value)} className={inputClass} required /></td>
                    {tracks && (
                      <td className="px-2 py-2 align-top">
                        {!isEdit ? (
                          <span className="text-xs text-gray-400" title="Available once the voucher is saved">—</span>
                        ) : !l.stock?.tracked ? (
                          <span className="text-xs text-gray-400">n/a</span>
                        ) : (
                          <div className="text-xs">
                            <div className={`font-semibold ${Number(l.stock?.shortfall_quantity) > 0 ? 'text-amber-700' : 'text-gray-800'}`}>
                              {round3(l.stock?.on_hand_quantity)}
                            </div>
                            {Number(l.stock?.shortfall_quantity) > 0 && (
                              <div className="inline-flex items-center text-amber-700" title="Recording this sale will take stock negative">
                                <AlertTriangle className="mr-0.5 h-3 w-3" />
                                short {round3(l.stock?.shortfall_quantity)}
                              </div>
                            )}
                          </div>
                        )}
                      </td>
                    )}
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

        {billsEnabled && (
          <div className="bg-white rounded-lg shadow p-5">
            <div className="flex items-center justify-between mb-2">
              <h4 className="text-sm font-semibold text-gray-800">Bill-wise details</h4>
              {loadingBills && (
                <span className="inline-flex items-center text-xs text-gray-400">
                  <RefreshCw className="mr-1 h-3 w-3 animate-spin" /> Loading bills…
                </span>
              )}
            </div>
            <p className="mb-3 text-xs text-gray-500">
              {billSide === 'sales'
                ? 'Which sales bills this note settles. Leave blank to post on account.'
                : billSide === 'purchase'
                  ? 'Which purchase bills this note settles. Leave blank to post on account.'
                  : 'Which bills this document settles. Leave blank to post on account.'}
            </p>
            <div className="space-y-2">
              {Object.values(allocations).length === 0 && (
                <div className="rounded-lg border border-dashed border-gray-200 px-3 py-4 text-center text-xs text-gray-400">
                  {partyLedgerId ? 'No outstanding bills for this party.' : 'Choose a party to see its outstanding bills.'}
                </div>
              )}
              {Object.values(allocations).map((row) => (
                <div key={row.key} className="flex items-center gap-3">
                  <div className="flex-1 min-w-0">
                    <div className="truncate text-sm text-gray-800">{row.bill_name}</div>
                    {row.balance !== null && row.balance !== undefined && (
                      <div className="text-xs text-gray-400">Balance {formatMoney(row.balance)}</div>
                    )}
                  </div>
                  <input
                    type="number"
                    step="0.01"
                    min="0"
                    value={row.amount}
                    onChange={(e) => updateAllocation(row.key, e.target.value)}
                    placeholder="0.00"
                    className="w-32 border border-gray-300 rounded-lg px-2 py-1 text-sm text-right"
                  />
                </div>
              ))}
            </div>
            <div className="mt-3 space-y-1 border-t border-gray-100 pt-3 text-sm">
              <div className="flex justify-between text-gray-600"><span>Allocated</span><span>{formatMoney(allocatedTotal)}</span></div>
              <div className="flex justify-between font-medium text-gray-900">
                <span>{allocationRemainder < -0.005 ? 'Over-allocated' : 'Remaining'}</span>
                <span className={allocationRemainder < -0.005 ? 'text-red-600' : ''}>{formatMoney(allocationRemainder)}</span>
              </div>
            </div>
          </div>
        )}

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
              <div className="flex justify-between text-gray-600">
                <span>
                  Discount
                  {Number(totals.discount_total) > 0 && (
                    <span className="ml-1 text-xs text-gray-400">
                      ({discountType === 'amount' ? 'flat' : `${round2(discountValue)}%`})
                    </span>
                  )}
                </span>
                <span>{totals.discount_total > 0 ? `− ${formatMoney(totals.discount_total)}` : formatMoney(0)}</span>
              </div>
              <div className="flex justify-between text-gray-600">
                <span>Tax ({round2(taxRate)}%)</span>
                <span>{formatMoney(totals.tax_total)}</span>
              </div>
              {gstType === 'intra' && totals.cgst_total !== 0 && <div className="flex justify-between pl-4 text-gray-500"><span>CGST</span><span>{formatMoney(totals.cgst_total)}</span></div>}
              {gstType === 'intra' && totals.sgst_total !== 0 && <div className="flex justify-between pl-4 text-gray-500"><span>SGST</span><span>{formatMoney(totals.sgst_total)}</span></div>}
              {gstType === 'inter' && totals.igst_total !== 0 && <div className="flex justify-between pl-4 text-gray-500"><span>IGST</span><span>{formatMoney(totals.igst_total)}</span></div>}
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

      <StockReceiptModal
        open={receiptModalOpen}
        summary={receipts.summary}
        voucher={voucherForModal}
        saving={receipts.saving}
        onClose={() => setReceiptModalOpen(false)}
        onSubmit={submitReceipt}
      />
    </div>
  );
};

export default ItemVoucherFormPage;