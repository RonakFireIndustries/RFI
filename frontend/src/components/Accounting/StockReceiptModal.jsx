import React, { useEffect, useMemo, useState } from 'react';
import { X, Loader2, PackageCheck, AlertTriangle } from 'lucide-react';
import StockStatusBadge from './StockStatusBadge';
import { inventoryLocationService } from '../../services/inventoryLocationService';

/*
 * Record a goods receipt (or a delivery) against one item voucher.
 *
 * The modal is deliberately "fill what arrived": every line opens pre-filled
 * with its outstanding quantity, because a supplier delivering the whole bill
 * in one drop — the common case — is then a single click. A part-delivery just
 * overwrites the lines it covers and saves, and because the receipts are
 * additive the outstanding figures come back smaller each time.
 *
 * Lines with no product attached cannot move stock and are shown struck
 * through, so nobody wonders why a line is missing from the form.
 */

const inputClass = 'w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 text-sm focus:ring-orange-500 focus:border-orange-500';
const labelClass = 'block text-xs font-medium text-gray-600 mb-1';

const today = () => new Date().toISOString().split('T')[0];

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
    || 'Could not record the receipt.';
};

const StockReceiptModal = ({ open, summary, voucher, saving, onClose, onSubmit }) => {
  const [receiptDate, setReceiptDate] = useState(today());
  const [locationId, setLocationId] = useState('');
  const [supplierBillNo, setSupplierBillNo] = useState('');
  const [supplierBillDate, setSupplierBillDate] = useState('');
  const [notes, setNotes] = useState('');
  const [quantities, setQuantities] = useState({});
  const [batches, setBatches] = useState({});
  const [locations, setLocations] = useState([]);
  const [error, setError] = useState(null);

  const outbound = summary?.direction === 'out';
  const lines = summary?.lines || [];
  const trackable = useMemo(() => lines.filter((line) => line.tracks_stock), [lines]);

  // Reset to "everything that is still outstanding" each time the modal opens,
  // so a part-delivery from yesterday never silently carries over.
  useEffect(() => {
    if (!open) return;
    setReceiptDate(today());
    setSupplierBillNo('');
    setSupplierBillDate('');
    setNotes('');
    setError(null);
    setQuantities(Object.fromEntries(
      trackable.map((line) => [line.voucher_item_id, qty(line.pending_quantity)])
    ));
    setBatches({});
  }, [open, trackable]);

  useEffect(() => {
    if (!open) return;
    let active = true;
    inventoryLocationService.list()
      .then((rows) => {
        if (!active) return;
        setLocations(rows || []);
        // Default to the only site, or the first one: receiving without
        // saying where defeats the point of per-site stock.
        setLocationId((current) => current || (rows?.[0]?.id ? String(rows[0].id) : ''));
      })
      .catch(() => setLocations([]));
    return () => { active = false; };
  }, [open]);

  const totals = useMemo(() => {
    const entered = trackable.reduce((sum, line) => sum + (Number(quantities[line.voucher_item_id]) || 0), 0);
    const outstanding = trackable.reduce((sum, line) => sum + Number(line.pending_quantity || 0), 0);
    return { entered, outstanding };
  }, [quantities, trackable]);

  if (!open) return null;

  const setQuantity = (id, value) => setQuantities((prev) => ({ ...prev, [id]: value }));

  const submit = () => {
    if (saving) return;
    setError(null);

    if (!receiptDate) { setError('Pick the date the goods arrived.'); return; }
    if (!locationId) { setError('Choose where the goods landed.'); return; }

    const items = trackable
      .map((line) => ({
        voucher_item_id: line.voucher_item_id,
        quantity: Number(quantities[line.voucher_item_id]) || 0,
        batch_no: batches[line.voucher_item_id] || null,
      }))
      .filter((item) => item.quantity > 0);

    if (items.length === 0) { setError('Enter a quantity for at least one line.'); return; }

    const over = trackable.find(
      (line) => (Number(quantities[line.voucher_item_id]) || 0) > Number(line.pending_quantity) + 0.0005
    );
    if (over) {
      setError(`Only ${qty(over.pending_quantity)} of "${over.item_description}" is still outstanding.`);
      return;
    }

    const site = locations.find((row) => String(row.id) === String(locationId));

    onSubmit({
      receipt_date: receiptDate,
      location_type: site?.location_type || 'App\\Models\\Site',
      location_id: site?.location_id ?? Number(String(locationId).replace('site_', '')),
      supplier_bill_no: supplierBillNo || null,
      supplier_bill_date: supplierBillDate || null,
      notes: notes || null,
      items,
    }).catch((err) => setError(firstError(err)));
  };

  return (
    <div className="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/40 p-4">
      <div className="my-8 w-full max-w-3xl rounded-xl bg-white shadow-xl">
        <div className="flex items-start justify-between border-b border-gray-100 px-5 py-4">
          <div>
            <h2 className="flex items-center text-lg font-semibold text-gray-900">
              <PackageCheck className="mr-2 h-5 w-5 text-orange-500" />
              {outbound ? 'Record Delivery' : 'Receive Stock'}
            </h2>
            <p className="mt-0.5 text-sm text-gray-500">
              {voucher?.voucher_no} · {voucher?.party?.name || '—'}
            </p>
          </div>
          <button type="button" onClick={onClose} className="text-gray-400 hover:text-gray-600" aria-label="Close">
            <X className="h-5 w-5" />
          </button>
        </div>

        <div className="space-y-5 px-5 py-4">
          {error && (
            <div className="flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
              <AlertTriangle className="mt-0.5 h-4 w-4 shrink-0" />
              <span>{error}</span>
            </div>
          )}

          <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div>
              <label className={labelClass}>{outbound ? 'Delivery date *' : 'Receipt date *'}</label>
              <input type="date" value={receiptDate} onChange={(e) => setReceiptDate(e.target.value)} className={inputClass} />
            </div>
            <div>
              <label className={labelClass}>{outbound ? 'From location *' : 'Received at *'}</label>
              <select value={locationId} onChange={(e) => setLocationId(e.target.value)} className={inputClass}>
                <option value="">— Select location —</option>
                {locations.map((row) => <option key={row.id} value={row.id}>{row.name}</option>)}
              </select>
            </div>
            {!outbound && (
              <>
                <div>
                  <label className={labelClass}>Supplier bill no.</label>
                  <input value={supplierBillNo} onChange={(e) => setSupplierBillNo(e.target.value)} className={inputClass} placeholder="Invoice / DC no." />
                </div>
                <div>
                  <label className={labelClass}>Bill date</label>
                  <input type="date" value={supplierBillDate} onChange={(e) => setSupplierBillDate(e.target.value)} className={inputClass} />
                </div>
              </>
            )}
          </div>

          <div className="overflow-x-auto">
            <table className="min-w-full text-sm">
              <thead>
                <tr className="text-xs uppercase tracking-wider text-gray-500">
                  <th className="px-2 py-2 text-left">Product</th>
                  <th className="px-2 py-2 text-right w-20">Ordered</th>
                  <th className="px-2 py-2 text-right w-24">Outstanding</th>
                  <th className="px-2 py-2 text-left w-28">{outbound ? 'Delivering' : 'Receiving'}</th>
                  <th className="px-2 py-2 text-left w-32">Batch / DC ref</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {lines.map((line) => (
                  <tr key={line.voucher_item_id} className={line.tracks_stock ? '' : 'opacity-60'}>
                    <td className="px-2 py-2">
                      <div className="font-medium text-gray-900">{line.product_name || line.item_description}</div>
                      {!line.tracks_stock ? (
                        <div className="text-xs text-gray-500">
                          Free-text line — no product, so no stock to move
                        </div>
                      ) : (
                        <div className="text-xs text-gray-500">
                          {line.item_description}
                          {line.unit ? ` · ${line.unit}` : ''}
                        </div>
                      )}
                    </td>
                    <td className="px-2 py-2 text-right text-gray-700">{qty(line.quantity)}</td>
                    <td className="px-2 py-2 text-right">
                      <span className="font-medium text-gray-900">{qty(line.pending_quantity)}</span>
                    </td>
                    <td className="px-2 py-2">
                      <input
                        type="number"
                        step="0.001"
                        min="0"
                        max={line.pending_quantity}
                        disabled={!line.tracks_stock || Number(line.pending_quantity) <= 0}
                        value={quantities[line.voucher_item_id] ?? ''}
                        onChange={(e) => setQuantity(line.voucher_item_id, e.target.value)}
                        className={`${inputClass} disabled:bg-gray-50 disabled:text-gray-400`}
                      />
                    </td>
                    <td className="px-2 py-2">
                      <input
                        value={batches[line.voucher_item_id] || ''}
                        disabled={!line.tracks_stock}
                        onChange={(e) => setBatches((prev) => ({ ...prev, [line.voucher_item_id]: e.target.value }))}
                        className={`${inputClass} disabled:bg-gray-50`}
                        placeholder="optional"
                      />
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          {trackable.length === 0 && (
            <div className="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
              None of this document's lines carry a product, so there is no stock to {outbound ? 'deliver' : 'receive'}.
            </div>
          )}

          <div>
            <label className={labelClass}>Notes</label>
            <input value={notes} onChange={(e) => setNotes(e.target.value)} className={inputClass} placeholder="Condition, shortage remark, DC reference…" />
          </div>

          <div className="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-gray-50 px-4 py-3">
            <div className="text-sm text-gray-600">
              <span className="font-medium text-gray-900">{qty(totals.entered)}</span> {outbound ? 'of' : 'of'} {qty(totals.outstanding)} outstanding
              {totals.entered < totals.outstanding && (
                <span className="ml-2 text-amber-700">— this will remain partially {outbound ? 'delivered' : 'received'}</span>
              )}
            </div>
            <div className="text-sm text-gray-500">
              {summary?.status && (
                <StockStatusBadge
                  status={summary.status}
                  direction={summary.direction}
                  ordered={summary.ordered_quantity}
                  moved={summary.moved_quantity}
                  pending={summary.pending_quantity}
                />
              )}
            </div>
          </div>
        </div>

        <div className="flex justify-end gap-2 border-t border-gray-100 px-5 py-4">
          <button type="button" onClick={onClose} className="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50">
            Cancel
          </button>
          <button
            type="button"
            onClick={submit}
            disabled={saving || trackable.length === 0}
            className="inline-flex items-center rounded-lg bg-orange-500 px-4 py-2 text-white hover:bg-orange-600 disabled:opacity-60"
          >
            {saving ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <PackageCheck className="mr-2 h-4 w-4" />}
            {saving ? 'Saving…' : outbound ? 'Record Delivery' : 'Receive Stock'}
          </button>
        </div>
      </div>
    </div>
  );
};

export default StockReceiptModal;
