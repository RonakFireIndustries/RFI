import ShortcutKey from './ShortcutKey';
import { usePermission } from '../../hooks/usePermission';
import { VOUCHER_SHORTCUTS } from '../../config/voucherShortcuts';

/**
 * The Finance section's F-key legend, shown on the voucher registers.
 *
 * Lists only the shortcuts the signed-in user is actually allowed to key, so
 * the hint never advertises a screen that would open and then refuse to save.
 * The handler itself (hooks/useVoucherShortcuts.js) applies the same
 * permission check.
 */
export const VoucherShortcutLegend = () => {
  const { canModule } = usePermission();

  const shortcuts = VOUCHER_SHORTCUTS.filter((s) => s.module && canModule(s.module, 'create'));
  if (shortcuts.length === 0) return null;

  return (
    <div className="mb-5 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-gray-500">
      <span className="text-[10px] font-bold uppercase tracking-wider text-gray-400">Shortcuts</span>
      {shortcuts.map((s) => (
        <span key={s.type} className="inline-flex items-center gap-1.5">
          <ShortcutKey className="border-gray-300 bg-gray-50 text-gray-600">{s.combo}</ShortcutKey>
          {s.label}
        </span>
      ))}
    </div>
  );
};

export default VoucherShortcutLegend;
