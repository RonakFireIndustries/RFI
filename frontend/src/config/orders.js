/*
 * Order tracking (operational) — statuses, sides and their transitions.
 *
 * Orders are deliberately NOT accounting documents: they never write to the
 * ledger. Money enters the books when a purchase / sales voucher is posted
 * (Slice 3 of the accounting work), and an order is merely linked to the
 * document it became so the payment panel can read its bill.
 *
 * The pipeline is the simple five-stage flow agreed for slice 1:
 *
 *   open → in_progress → fulfilled → invoiced → done        (+ cancelled)
 *
 * `next` is the single source of truth for "can this order advance?" in the
 * UI; the backend enforces the identical rule in OrderService, so the button
 * and the API can never disagree.
 */

export const ORDER_SIDES = {
  sales: {
    key: 'sales',
    label: 'Sales',
    party: 'customer',
    partyLabel: 'Customer',
    prefix: 'SO',
    invoiceLabel: 'Sales invoice',
  },
  purchase: {
    key: 'purchase',
    label: 'Purchase',
    party: 'supplier',
    partyLabel: 'Supplier',
    prefix: 'PO',
    invoiceLabel: 'Purchase invoice',
  },
};

export const ORDER_STATUSES = {
  open: { key: 'open', label: 'Open', badge: 'bg-sky-50 text-sky-700', next: 'in_progress' },
  in_progress: { key: 'in_progress', label: 'In progress', badge: 'bg-amber-50 text-amber-700', next: 'fulfilled' },
  fulfilled: { key: 'fulfilled', label: 'Fulfilled', badge: 'bg-blue-50 text-blue-700', next: 'invoiced' },
  invoiced: { key: 'invoiced', label: 'Invoiced', badge: 'bg-violet-50 text-violet-700', next: 'done' },
  done: { key: 'done', label: 'Done', badge: 'bg-green-50 text-green-700', next: null },
  cancelled: { key: 'cancelled', label: 'Cancelled', badge: 'bg-rose-50 text-rose-700', next: null },
};

/** Order in which the list's status filter (and the timeline) read. */
export const ORDER_STATUS_KEYS = ['open', 'in_progress', 'fulfilled', 'invoiced', 'done', 'cancelled'];

/** A status nothing can leave except by being read. */
export const isTerminalOrderStatus = (status) => !ORDER_STATUSES[status]?.next;

export const orderStatusMeta = (status) =>
  ORDER_STATUSES[status] || { key: status || '', label: status || '—', badge: 'bg-gray-100 text-gray-700', next: null };

export const orderStatusLabel = (status) => orderStatusMeta(status).label;

export const orderStatusBadge = (status) => orderStatusMeta(status).badge;

/** The status an advance action would move to, or null when it cannot move. */
export const nextOrderStatus = (status) => orderStatusMeta(status).next;

/** Permission gating, mirroring config/access.php's `orders` module. */
export const orderPermission = (action) => `orders.${action}`;

/**
 * The bill types an order on each side may be marked invoiced against —
 * `sides.*.documents` in backend config/orders.php, in the same vocabulary
 * as the accounting bill-wise details.
 */
export const ORDER_DOCUMENTS = {
  sales: [
    { type: 'invoice', label: 'Sales invoice' },
    { type: 'sales-voucher', label: 'Sales voucher' },
  ],
  purchase: [
    { type: 'purchase-order', label: 'Purchase order (legacy)' },
    { type: 'purchase-voucher', label: 'Purchase voucher' },
  ],
};

export const orderDocumentLabel = (type) =>
  Object.values(ORDER_DOCUMENTS).flat().find((d) => d.type === type)?.label || type || '';

/**
 * Where a linked document lives in the app, so the detail page can offer an
 * "open it" link without hard-coding routes per page.
 */
export const orderDocumentPath = (document) => {
  if (!document) return null;
  switch (document.type) {
    case 'invoice': return '/dashboard/invoices';
    case 'purchase-order': return `/dashboard/purchases/${document.id}`;
    case 'sales-voucher': return '/dashboard/accounting/sales';
    case 'purchase-voucher': return '/dashboard/accounting/purchase';
    default: return null;
  }
};

/* Statuses whose lines, dates and party may still be edited, and those a
 * cancel is offered from — the two lists in backend config/orders.php. */

export const ORDER_EDITABLE_STATUSES = ['open', 'in_progress'];

export const ORDER_CANCELLABLE_STATUSES = ['open', 'in_progress', 'fulfilled', 'invoiced'];

export const isEditableOrderStatus = (status) => ORDER_EDITABLE_STATUSES.includes(status);

export const isCancellableOrderStatus = (status) => ORDER_CANCELLABLE_STATUSES.includes(status);
