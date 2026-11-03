import { useEffect, useState } from 'react';
import { unitConversionService } from '../../services/unitConversionService';
import { formatQuantity } from '../../utils/units';

/**
 * Live "this equals X in the product's unit" line under a quantity field.
 *
 * The backend converts on save anyway (stock is always kept in the product's
 * own unit), so this only reports what will happen while the user types. When
 * no conversion path exists the save still goes through — the quantity is
 * treated as 1:1 — and we flag that here so the fallback is never silent.
 */
export default function UnitConversionHint({ quantity, fromUnitId, toUnitId, className = '' }) {
  const [state, setState] = useState(null);
  const from = Number(fromUnitId) || null;
  const to = Number(toUnitId) || null;
  const qty = Number(quantity);

  useEffect(() => {
    if (!from || !to || from === to || !Number.isFinite(qty) || qty <= 0) {
      setState(null);
      return undefined;
    }

    let cancelled = false;
    const timer = setTimeout(() => {
      unitConversionService
        .convert(from, to, qty)
        .then((data) => {
          if (cancelled) return;
          setState({ ok: true, ...data });
        })
        .catch((error) => {
          if (cancelled) return;
          const status = error?.response?.status;
          // 404 = no path between the units; anything else (e.g. a missing
          // permission) just hides the hint rather than shouting about it.
          setState(status === 404 ? { ok: false } : null);
        });
    }, 250);

    return () => {
      cancelled = true;
      clearTimeout(timer);
    };
  }, [from, to, qty]);

  if (!state) return null;

  if (!state.ok) {
    return (
      <p className={`mt-1 text-xs text-amber-600 ${className}`}>
        No conversion defined between these units — the quantity is treated as 1:1.
        Add the rate in Inventory › Conversions.
      </p>
    );
  }

  const fromLabel = state.from_unit ? `${formatQuantity(state.quantity, state.from_unit)}` : formatQuantity(state.quantity);
  const toLabel = formatQuantity(state.result, state.to_unit);

  return (
    <p className={`mt-1 text-xs text-gray-500 ${className}`}>
      ≈ {fromLabel} = {toLabel}
    </p>
  );
}
