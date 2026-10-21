import React from 'react';

/*
 * Receive / deliver status pill.
 *
 * Derived server-side from the voucher's receipt lines (never stored), so the
 * four states are mutually exclusive and can never drift from the counters:
 *
 *   not_tracked  the document has no product lines — nothing to receive
 *   pending      billed, nothing has arrived
 *   partial      some of it has arrived, the rest is still outstanding
 *   complete     everything billed has been received (or delivered)
 *
 * `direction` flips the wording for a sales voucher, where the goods move out.
 */

const TONES = {
  pending: 'bg-amber-50 text-amber-700 border-amber-200',
  partial: 'bg-blue-50 text-blue-700 border-blue-200',
  complete: 'bg-green-50 text-green-700 border-green-200',
  not_tracked: 'bg-gray-50 text-gray-600 border-gray-200',
};

const DOTS = {
  pending: 'bg-amber-500',
  partial: 'bg-blue-500',
  complete: 'bg-green-500',
  not_tracked: 'bg-gray-400',
};

const INBOUND_LABELS = {
  pending: 'Not received',
  partial: 'Partially received',
  complete: 'Fully received',
  not_tracked: 'No stock',
};

const OUTBOUND_LABELS = {
  pending: 'Not delivered',
  partial: 'Partially delivered',
  complete: 'Fully delivered',
  not_tracked: 'No stock',
};

/** Trim trailing zeros so "6.000" reads as "6". */
const qty = (value) => {
  const number = Number(value);
  if (!Number.isFinite(number)) return '0';
  return String(Number(number.toFixed(3)));
};

const StockStatusBadge = ({ status, direction = 'in', ordered, moved, pending, className = '' }) => {
  if (!status) return null;

  const labels = direction === 'out' ? OUTBOUND_LABELS : INBOUND_LABELS;
  const label = labels[status] || status;

  // "Partially received" of what? — the quantities ride along whenever the
  // caller has them, so a glance at the list says how much is still owed.
  const detail = ['partial', 'complete'].includes(status) && ordered != null
    ? ` ${qty(moved)}/${qty(ordered)}`
    : '';

  const title = ordered != null
    ? `${qty(moved)} of ${qty(ordered)} ${direction === 'out' ? 'delivered' : 'received'}`
      + (pending != null ? ` · ${qty(pending)} pending` : '')
    : undefined;

  return (
    <span
      title={title}
      className={`inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium whitespace-nowrap ${TONES[status] || TONES.not_tracked} ${className}`}
    >
      <span className={`mr-1.5 h-1.5 w-1.5 rounded-full ${DOTS[status] || DOTS.not_tracked}`} />
      {label}{detail}
    </span>
  );
};

export default StockStatusBadge;
