import { VOUCHER_TYPES } from './vouchers';

/*
 * Tally's voucher-entry keys, mapped onto this app's voucher types.
 *
 *   F4 Contra   F5 Payment   F6 Receipt   F7 Journal
 *   F8 Sales    F9 Purchase  Alt+F8 Credit Note   Alt+F9 Debit Note
 *
 * The keys are declared as data so the sidebar hints, the legend on the
 * voucher registers and the key handler can never drift apart; the labels and
 * the access-control modules come straight from VOUCHER_TYPES.
 *
 * Consumed by hooks/useVoucherShortcuts.js (the handler) and the two hint
 * components that render `combo` next to the voucher's name.
 */
export const VOUCHER_SHORTCUTS = [
  { type: 'contra', key: 'F4' },
  { type: 'payment', key: 'F5' },
  { type: 'receipt', key: 'F6' },
  { type: 'journal', key: 'F7' },
  { type: 'sales', key: 'F8' },
  { type: 'purchase', key: 'F9' },
  { type: 'credit-note', key: 'F8', alt: true },
  { type: 'debit-note', key: 'F9', alt: true },
].map((shortcut) => ({
  ...shortcut,
  alt: !!shortcut.alt,
  combo: shortcut.alt ? `Alt+${shortcut.key}` : shortcut.key,
  label: VOUCHER_TYPES[shortcut.type]?.label || shortcut.type,
  module: VOUCHER_TYPES[shortcut.type]?.module || null,
}));

/** Where a new voucher of `type` is entered. */
export const newVoucherPath = (type) => `/dashboard/accounting/vouchers/new/${type}`;

/** The Finance section's own routes; the shortcuts only run inside it. */
export const FINANCE_PATH = '/dashboard/accounting';

export const inFinanceSection = (pathname = '') =>
  pathname === FINANCE_PATH || pathname.startsWith(`${FINANCE_PATH}/`);

/**
 * True on a voucher entry screen — `/accounting/vouchers/new/:type` and
 * `/accounting/vouchers/:id/edit/:type`.
 *
 * The keys stand still there: bouncing from a half-filled Payment to a fresh
 * Sales screen would quietly discard whatever has been typed so far.
 */
export const isVoucherFormPath = (pathname = '') => pathname.startsWith(`${FINANCE_PATH}/vouchers/`);

/** The one predicate the handler and the sidebar hints both read. */
export const shortcutsLiveFor = (pathname = '') =>
  inFinanceSection(pathname) && !isVoucherFormPath(pathname);

/**
 * True when the keystroke landed on a typing surface — an input, a textarea,
 * a select or a contenteditable — in which case the shortcuts stay away.
 */
export const isTypingTarget = (target) =>
  typeof target?.closest === 'function' &&
  target.closest('input, textarea, select, [contenteditable]:not([contenteditable="false"])') !== null;
