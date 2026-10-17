/*
 * Tally-style voucher types exposed in the UI (slice 2).
 *
 * `module` is the access-control module that gates the type and MUST match
 * config/accounting.php + config/access.php in the backend. `bills` marks the
 * types that carry bill-wise allocation details.
 */
export const VOUCHER_TYPES = {
  contra: { key: 'contra', label: 'Contra', module: 'contra', bills: false },
  payment: { key: 'payment', label: 'Payment', module: 'payment-vouchers', bills: true },
  receipt: { key: 'receipt', label: 'Receipt', module: 'receipt-vouchers', bills: true },
  journal: { key: 'journal', label: 'Journal', module: 'journal', bills: true },
};

export const PARTY_VOUCHER_TYPES = ['payment', 'receipt'];

/** The party ledger a Payment/Receipt voucher touches. */
export const partyTypeForVoucher = (type) => (type === 'payment' ? 'supplier' : 'customer');

export const voucherTypeLabel = (type) => VOUCHER_TYPES[type]?.label || type;

export const isPartyVoucher = (type) => PARTY_VOUCHER_TYPES.includes(type);
