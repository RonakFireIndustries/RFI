/**
 * Resolve a human-readable unit label from whatever a payload carries.
 *
 * APIs in this app mix a few shapes: `unit` as a loaded relation object, the
 * same name on a product (`product.unit`), or a plain string on old rows.
 * Callers can pass several sources; the first one with a value wins.
 */
export function unitLabel(...sources) {
  for (const source of sources) {
    if (!source) continue;
    if (typeof source === 'string') return source;
    if (typeof source.name === 'string' && source.name.trim()) return source.name;
    if (typeof source.code === 'string' && source.code.trim()) return source.code;
  }
  return '';
}

/** `12.5 KG` when a unit is known, otherwise just `12.5`. */
export function formatQuantity(value, ...sources) {
  const number = Number(value);
  const quantity = Number.isFinite(number) ? String(Number(number.toFixed(3))) : '0';
  const unit = unitLabel(...sources);
  return unit ? `${quantity} ${unit}` : quantity;
}
