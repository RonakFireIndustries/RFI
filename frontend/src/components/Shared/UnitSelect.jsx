import { useEffect } from 'react';
import { useUnitStore } from '../../store/unitStore';

/**
 * Unit picker shared by the inventory, voucher and quotation forms.
 *
 * Quantities are always stored against the product's own unit. Picking another
 * unit here means "the number I typed is in this unit"; the backend converts it
 * and keeps both the entered and the base figure.
 *
 * The list comes from the shared unit store; pass `units` when the caller has
 * already loaded a filtered list.
 */
export default function UnitSelect({
  value,
  onChange,
  units,
  placeholder = 'Product unit',
  className = 'w-full rounded-md border border-gray-300 px-3 py-2 text-sm',
  disabled = false,
  allowEmpty = true,
  id,
}) {
  const { items, fetchItems } = useUnitStore();
  const list = units ?? items;

  useEffect(() => {
    if (!units && items.length === 0) {
      fetchItems().catch(() => {});
    }
  }, [units, items.length, fetchItems]);

  const handleChange = (event) => {
    const nextValue = event.target.value;
    const selected = list.find((unit) => String(unit.id) === String(nextValue)) || null;
    onChange(nextValue, selected);
  };

  return (
    <select
      id={id}
      value={value ?? ''}
      onChange={handleChange}
      className={className}
      disabled={disabled}
    >
      {allowEmpty && <option value="">{placeholder}</option>}
      {list.map((unit) => (
        <option key={unit.id} value={unit.id}>
          {unit.name}{unit.code ? ` (${unit.code})` : ''}
        </option>
      ))}
    </select>
  );
}
