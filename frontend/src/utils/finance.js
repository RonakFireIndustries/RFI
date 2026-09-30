/**
 * Price visibility rules.
 *
 * Monetary values (product prices, invoice amounts, order totals, balances,
 * dashboards) are only shown to:
 *
 *   - super admins (is_super_admin column, or the __super_admin__ marker), and
 *   - anyone who can add or edit purchase orders or sales orders.
 *
 * This replaces the old hardcoded `FINANCE_ROLES = ['Admin', 'Accountant']`
 * check that lived in three product/supplier pages. Accountants are no longer
 * implicitly exempt; they see prices only if they hold one of these
 * permissions, same as everyone else.
 *
 * NOTE: this is presentation only. The API still returns the monetary fields to
 * every authenticated user, so this hides prices from the UI, it does not make
 * them confidential. If they ever need to be confidential, the fields have to
 * be stripped in the backend API resources.
 */

/** Any one of these grants price visibility (OR semantics). */
export const PRICE_VISIBILITY_PERMISSIONS = [
  'purchase-orders.create',
  'purchase-orders.update',
  'sales-orders.create',
  'sales-orders.update',
];

/** Rendered in place of a hidden amount. */
export const MASKED_VALUE = '••••';

export const REASON = 'Hidden. Requires purchase or sales order add/edit access.';

/**
 * Pure check, so it can be used outside React.
 * `ctx` is `{ user, permissions }` as produced by the auth store.
 */
export const canSeePrices = (ctx) => {
  const list = ctx?.permissions || [];
  if (ctx?.user?.is_super_admin || list.includes('__super_admin__')) return true;
  return PRICE_VISIBILITY_PERMISSIONS.some((permission) => list.includes(permission));
};

const formatterCache = new Map();

const getFormatter = (decimals) => {
  if (!formatterCache.has(decimals)) {
    formatterCache.set(
      decimals,
      new Intl.NumberFormat('en-IN', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
      })
    );
  }
  return formatterCache.get(decimals);
};

/**
 * Format a monetary value with Indian digit grouping (lakh/crore aware).
 * Returns '' for null/undefined so callers can decide on a fallback.
 */
export const formatAmount = (value, decimals = 2) => {
  if (value === null || value === undefined || value === '') return '';
  const numeric = Number(value);
  if (!Number.isFinite(numeric)) return '';
  return getFormatter(decimals).format(numeric);
};

/** formatAmount with a leading currency symbol, e.g. "₹1,23,456.00". */
export const formatMoney = (value, { decimals = 2, symbol = '₹' } = {}) => {
  const formatted = formatAmount(value, decimals);
  if (!formatted) return '';
  return `${symbol}${formatted}`;
};
