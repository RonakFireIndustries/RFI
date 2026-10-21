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
  'credit-note': { key: 'credit-note', label: 'Credit Note', module: 'credit-notes', bills: true, items: true, partyType: 'customer', stock: 'in' },
  'debit-note': { key: 'debit-note', label: 'Debit Note', module: 'debit-notes', bills: true, items: true, partyType: 'supplier', stock: 'out' },
};

export const PARTY_VOUCHER_TYPES = ['payment', 'receipt'];
export const ITEM_VOUCHER_TYPES = ['purchase', 'sales', 'credit-note', 'debit-note'];
/** Item types the warehouse fulfils in parts, through the receipt modal. */
export const STOCK_VOUCHER_TYPES = ['purchase', 'sales'];
/**
 * Item types that move stock *whole, at save* rather than in instalments.
 * A return is already in (or out of) the warehouse when the note is keyed,
 * so there is no receipt modal and no partial state.
 */
export const NOTE_STOCK_TYPES = ['credit-note', 'debit-note'];

export const partyTypeForVoucher = (type) => VOUCHER_TYPES[type]?.partyType || (type === 'payment' ? 'supplier' : 'customer');
export const voucherTypeLabel = (type) => VOUCHER_TYPES[type]?.label || type;
export const isPartyVoucher = (type) => PARTY_VOUCHER_TYPES.includes(type);
export const isItemVoucher = (type) => ITEM_VOUCHER_TYPES.includes(type);
export const stockDirectionForVoucher = (type) => VOUCHER_TYPES[type]?.stock || null;
export const tracksStock = (type) => Boolean(VOUCHER_TYPES[type]?.stock);
/** A note moves its stock when saved, so it has no receipt / delivery modal. */
export const movesStockOnSave = (type) => NOTE_STOCK_TYPES.includes(type);
/** Which bill kinds a note may allocate against: a credit note settles sales
 *  documents, a debit note purchase ones. `opening` (the brought-forward
 *  balance) belongs to both sides. */
export const billSidesForVoucher = (type) =>
  ({ 'credit-note': 'sales', 'debit-note': 'purchase' }[type] || null);

export const BILL_TYPES_FOR_SIDE = {
  sales: ['opening', 'invoice', 'sales-voucher'],
  purchase: ['opening', 'invoice', 'purchase-order', 'purchase-voucher'],
  // Purchase and sales vouchers carry bill-wise details too, but against the
  // party's documents of either side.
  all: null,
};