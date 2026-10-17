import { useCallback, useMemo } from 'react';
import { useAuthStore } from '../store/authStore';
import {
  MASKED_VALUE,
  REASON,
  BOQ_REASON,
  canSeePrices as canSeePricesWithContext,
  canSeeBoqPrices,
  formatMoney,
} from '../utils/finance';

/**
 * Price visibility for the signed-in user.
 *
 *   const { canSeePrices, money } = usePriceVisibility();
 *   canSeePrices                       // -> boolean
 *   money(1234.5)                      // -> "₹1,234.50" or "••••"
 *   money(1234.5, { symbol: '' })      // -> "1,234.50"
 *
 * `money` is the form to use for string props (StatCard value={...}) and inline
 * template literals. For markup use the <Money> component in
 * components/Shared/Money.jsx.
 *
 * Pass scope 'boq' on BOQ / Quotation screens to apply the Design-and-Admin-only
 * rule instead of the app-wide purchase/sales-order rule.
 */
export const usePriceVisibility = (scope = 'default') => {
  const user = useAuthStore((state) => state.user);
  const permissions = useAuthStore((state) => state.permissions);
  const roles = useAuthStore((state) => state.roles);

  const list = permissions || [];
  const context = useMemo(
    () => ({ user, permissions: list, roles: roles || [] }),
    [user, list, roles]
  );

  const isBoq = scope === 'boq';

  const canSeePrices = useMemo(
    () => (isBoq ? canSeeBoqPrices(context) : canSeePricesWithContext(context)),
    [isBoq, context]
  );

  const money = useCallback(
    (value, options) => {
      if (!canSeePrices) return MASKED_VALUE;
      return formatMoney(value, options) || MASKED_VALUE;
    },
    [canSeePrices]
  );

  return {
    canSeePrices,
    money,
    maskedValue: MASKED_VALUE,
    reason: isBoq ? BOQ_REASON : REASON,
  };
};
