import React, { useState } from 'react';
import { PackageCheck, Truck, Undo2, ChevronDown, ChevronRight, MapPin, Calendar, User } from 'lucide-react';
import StockStatusBadge from './StockStatusBadge';
import PermissionGate from '../Guards/PermissionGate';

/*
 * The warehouse half of a purchase / sales voucher: what has been received (or
 * delivered), when, how much, by whom, and where it went — plus the per-line
 * outstanding quantities that answer "what is still owed to us".
 *
 * The receipt list is a history, not a state: reversing a receipt keeps it in
 * the list, greyed and struck through, because "10 came in on the 3rd and went
 * back on the 5th" is the fact an auditor wants.
 */

const qty = (value) => {
  const number = Number(value);
  if (!Number.isFinite(number)) return '0';
  return String(Number(number.toFixed(3)));
};

const formatDate = (value) => {
  if (!value) return '—';
  const date = new Date(value);
  return Number.isNaN(date.getTime()) ? value : date.toLocaleDateString();
};

const ProgressBar = ({ ordered, moved }) => {
  const total = Number(ordered) || 0;
  const done = Number(moved) || 0;
  const percent = total > 0 ? Math.min(100, Math.round((done / total) * 100)) : 0;
  const tone = percent >= 100 ? 'bg-green-500' : percent > 0 ? 'bg-blue-500' : 'bg-gray-300';

  return (
    <div className="h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
      <div className={`h-full rounded-full transition-all ${tone}`} style={{ width: `${percent}%` }} />
    </div>
  );
};

const ReceiptRow = ({ receipt, outbound, onReverse, saving }) => {
  const [open, setOpen] = useState(false);
  const reversed = receipt.status === 'reversed';

  return (
    <div className={`rounded-lg border ${reversed ? 'border-gray-200 bg-gray-50' : 'border-gray-200 bg-white'}`}>
      <div className="flex items-center gap-3 px-3 py-2.5">
        <button
          type="button"
          onClick={() => setOpen((value) => !value)}
          className="text-gray-400 hover:text-gray-600"
          aria-label={open ? 'Collapse' : 'Expand'}
        >
          {open ? <ChevronDown className="h-4 w-4" /> : <ChevronRight className="h-4 w-4" />}
        </button>

        <div className={reversed ? 'line-through' : ''}>
          <div className="flex items-center gap-2 text-sm font-medium text-gray-900">
            {outbound ? <Truck className="h-4 w-4 text-gray-400" /> : <PackageCheck className="h-4 w-4 text-gray-400" />}
            {receipt.receipt_no}
            <span className="text-xs font-normal text-gray-500">{formatDate(receipt.receipt_date)}</span>
            {reversed && (
              <span className="rounded-full bg-rose-50 px-2 py-0.5 text-xs font-medium text-rose-700">Reversed</span>
            )}
          </div>
          <div className="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500">
            {receipt.location_name && (
              <span className="inline-flex items-center"><MapPin className="mr-1 h-3 w-3" />{receipt.location_name}</span>
            )}
            {receipt.received_by_name && (
              <span className="inline-flex items-center"><User className="mr-1 h-3 w-3" />{receipt.received_by_name}</span>
            )}
            {receipt.supplier_bill_no && (
              <span className="inline-flex items-center"><Calendar className="mr-1 h-3 w-3" />Bill {receipt.supplier_bill_no}</span>
            )}
          </div>
        </div>

        <div className="ml-auto flex items-center gap-3">
          <div className="text-right">
            <div className="text-sm font-semibold text-gray-900">{qty(receipt.quantity_total)}</div>
            <div className="text-xs text-gray-500">{receipt.items?.length || 0} line{(receipt.items?.length || 0) === 1 ? '' : 's'}</div>
          </div>
          {!reversed && onReverse && (
            <PermissionGate module="stock-receipts" action="delete" subject={`receipt ${receipt.receipt_no}`}>
              <button
                type="button"
                onClick={() => onReverse(receipt)}
                disabled={saving}
                title="Reverse this receipt"
                className="text-rose-500 hover:text-rose-700 disabled:opacity-40"
              >
                <Undo2 className="h-4 w-4" />
              </button>
            </PermissionGate>
          )}
        </div>
      </div>

      {open && (
        <div className="border-t border-gray-100 px-3 py-2">
          <table className="min-w-full text-xs">
            <thead>
              <tr className="text-gray-500">
                <th className="py-1 text-left font-medium">Product</th>
                <th className="py-1 text-right font-medium">Qty</th>
                <th className="py-1 text-right font-medium">Running total</th>
                <th className="py-1 text-left font-medium">Batch</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-50">
              {(receipt.items || []).map((line) => (
                <tr key={line.id}>
                  <td className="py-1 text-gray-800">{line.product_name || `Product #${line.product_id}`}</td>
                  <td className="py-1 text-right font-medium text-gray-900">{qty(line.quantity)}</td>
                  <td className="py-1 text-right text-gray-500">
                    {qty(line.quantity_before)} → {qty(line.quantity_after)}
                  </td>
                  <td className="py-1 text-gray-500">{line.batch_no || '—'}</td>
                </tr>
              ))}
            </tbody>
          </table>
          {receipt.notes && <p className="mt-2 text-xs text-gray-500">{receipt.notes}</p>}
          {reversed && receipt.reversal_reason && (
            <p className="mt-2 text-xs text-rose-700">Reversed: {receipt.reversal_reason}</p>
          )}
        </div>
      )}
    </div>
  );
};

const StockReceiptPanel = ({ summary, loading, saving, onReverse }) => {
  if (loading && !summary) {
    return (
      <div className="rounded-lg border border-gray-200 bg-white p-6 text-center text-sm text-gray-500">
        Loading receipt history…
      </div>
    );
  }

  if (!summary) return null;

  if (!summary.tracks_stock) {
    return (
      <div className="rounded-lg border border-gray-200 bg-white p-5 text-sm text-gray-500">
        This document type does not post warehouse stock, so there is nothing to receive against it.
      </div>
    );
  }

  const outbound = summary.direction === 'out';
  const tracked = (summary.lines || []).filter((line) => line.tracks_stock);

  return (
    <div className="space-y-4 rounded-lg border border-gray-200 bg-white p-5">
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h3 className="text-sm font-semibold text-gray-800">
            {outbound ? 'Dispatch & delivery' : 'Goods received'}
          </h3>
          <p className="mt-0.5 text-xs text-gray-500">
            {outbound
              ? 'Stock leaves the warehouse as each delivery is recorded.'
              : 'Stock is added to the chosen location as each delivery arrives.'}
          </p>
        </div>
        <StockStatusBadge
          status={summary.status}
          direction={summary.direction}
          ordered={summary.ordered_quantity}
          moved={summary.moved_quantity}
          pending={summary.pending_quantity}
        />
      </div>

      {tracked.length > 0 && (
        <div>
          <div className="mb-2 flex items-center justify-between text-xs text-gray-500">
            <span>{qty(summary.moved_quantity)} of {qty(summary.ordered_quantity)} {outbound ? 'delivered' : 'received'}</span>
            <span>{qty(summary.pending_quantity)} pending</span>
          </div>
          <ProgressBar ordered={summary.ordered_quantity} moved={summary.moved_quantity} />

          <table className="mt-3 min-w-full text-sm">
            <thead>
              <tr className="text-xs uppercase tracking-wider text-gray-500">
                <th className="py-1.5 text-left">Line</th>
                <th className="py-1.5 text-right">Ordered</th>
                <th className="py-1.5 text-right">{outbound ? 'Delivered' : 'Received'}</th>
                <th className="py-1.5 text-right">Outstanding</th>
                <th className="py-1.5 text-left pl-4">History</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {tracked.map((line) => (
                <tr key={line.voucher_item_id}>
                  <td className="py-2">
                    <div className="font-medium text-gray-900">{line.product_name || line.item_description}</div>
                    {line.unit && <div className="text-xs text-gray-500">{line.unit}</div>}
                  </td>
                  <td className="py-2 text-right text-gray-700">{qty(line.quantity)}</td>
                  <td className="py-2 text-right text-gray-700">{qty(line.moved_quantity)}</td>
                  <td className="py-2 text-right font-medium">
                    <span className={Number(line.pending_quantity) > 0 ? 'text-amber-700' : 'text-green-700'}>
                      {qty(line.pending_quantity)}
                    </span>
                  </td>
                  <td className="py-2 pl-4">
                    {(line.receipts || []).length === 0 ? (
                      <span className="text-xs text-gray-400">Nothing yet</span>
                    ) : (
                      <div className="flex flex-wrap gap-1">
                        {line.receipts.map((entry) => (
                          <span
                            key={`${entry.stock_receipt_id}-${entry.receipt_no}`}
                            title={`${entry.receipt_no} · ${entry.status}`}
                            className={`rounded px-1.5 py-0.5 text-xs ${
                              entry.status === 'reversed'
                                ? 'bg-gray-100 text-gray-400 line-through'
                                : 'bg-blue-50 text-blue-700'
                            }`}
                          >
                            {qty(entry.quantity)} · {formatDate(entry.receipt_date)}
                          </span>
                        ))}
                      </div>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {tracked.length === 0 && (
        <p className="text-sm text-gray-500">
          No line on this document carries a product, so nothing can be {outbound ? 'delivered' : 'received'}.
        </p>
      )}

      <div>
        <h4 className="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">
          {outbound ? 'Delivery history' : 'Receipt history'}
        </h4>
        {(summary.receipts || []).length === 0 ? (
          <p className="rounded-lg border border-dashed border-gray-200 px-3 py-4 text-center text-sm text-gray-500">
            No {outbound ? 'deliveries' : 'receipts'} recorded yet.
          </p>
        ) : (
          <div className="space-y-2">
            {summary.receipts.map((receipt) => (
              <ReceiptRow
                key={receipt.id}
                receipt={receipt}
                outbound={outbound}
                onReverse={onReverse}
                saving={saving}
              />
            ))}
          </div>
        )}
      </div>

      {(summary.readiness || []).length > 0 && (
        <ul className="space-y-1 text-xs text-gray-500">
          {summary.readiness.map((note) => <li key={note}>• {note}</li>)}
        </ul>
      )}
    </div>
  );
};

export default StockReceiptPanel;
