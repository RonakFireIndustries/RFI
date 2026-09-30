import { usePriceVisibility } from '../../hooks/usePriceVisibility';
import { formatMoney } from '../../utils/finance';

/**
 * Renders a monetary value, or a mask when the signed-in user may not see
 * prices. Use this for markup; use the `money()` helper from
 * usePriceVisibility() for string props such as <StatCard value={...}>.
 *
 *   <Money value={product.selling_price} />
 *   <Money value={invoice.grand_total} decimals={0} />
 *   <Money value={row.unit_price} showSymbol={false} />
 */
export default function Money({
  value,
  symbol = '₹',
  decimals = 2,
  className = '',
  masked = null,
  showSymbol = true,
}) {
  const { canSeePrices, maskedValue, reason } = usePriceVisibility();

  if (!canSeePrices) {
    return (
      <span className={className} title={reason} aria-label="Price hidden">
        {masked || maskedValue}
      </span>
    );
  }

  const formatted = formatMoney(value, { decimals, symbol: showSymbol ? symbol : '' });
  return <span className={className}>{formatted || '-'}</span>;
}
