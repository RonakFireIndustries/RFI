/*
 * Tally-style voucher types exposed in the UI (slice 2+).
 *
 * `module` is the access-control module that gates the type and MUST match
 * config/accounting.php + config/access.php in the backend. `bills` marks the
 * types that carry bill-wise allocation details.
 */
export const VOUCHER_TYPES = {
  contra: { key: 'contra', label: 'Contra', module: 'contra', bills: false, items: false, partyType: null },
  payment: { key: 'payment', label: 'Payment', module: 'payment-vouchers', bills: true, items: false, partyType: 'supplier' },
  receipt: { key: 'receipt', label: 'Receipt', module: 'receipt-vouchers', bills: true, items: false, partyType: 'customer' },
  journal: { key: 'journal', label: 'Journal', module: 'journal', bills: true, items: false, partyType: null },
  // `stock` mirrors config/accounting.php: an `in` type is fulfilled by goods
  // receipts, an `out` type by deliveries. Types without it never touch stock.
  purchase: { key: 'purchase', label: 'Purchase', module: 'purchase-vouchers', bills: true, items: true, partyType: 'supplier', stock: 'in' },
  sales: { key: 'sales', label: 'Sales', module: 'sales-vouchers', bills: true, items: true, partyType: 'customer', stock: 'out' },
  'credit-note': { key: 'credit-note', label: 'Credit Note', module: 'credit-notes', bills: true, items: true, partyType: 'customer' },
  'debit-note': { key: 'debit-note', label: 'Debit Note', module: 'debit-notes', bills: true, items: true, partyType: 'supplier' },
};

export const PARTY_VOUCHER_TYPES = ['payment', 'receipt'];
export const ITEM_VOUCHER_TYPES = ['purchase', 'sales', 'credit-note', 'debit-note'];
/** Item types whose goods the warehouse receives (or delivers). */
export const STOCK_VOUCHER_TYPES = ['purchase', 'sales'];

export const partyTypeForVoucher = (type) => VOUCHER_TYPES[type]?.partyType || (type === 'payment' ? 'supplier' : 'customer');
export const voucherTypeLabel = (type) => VOUCHER_TYPES[type]?.label || type;
export const isPartyVoucher = (type) => PARTY_VOUCHER_TYPES.includes(type);
export const isItemVoucher = (type) => ITEM_VOUCHER_TYPES.includes(type);
export const stockDirectionForVoucher = (type) => VOUCHER_TYPES[type]?.stock || null;
export const tracksStock = (type) => Boolean(VOUCHER_TYPES[type]?.stock);