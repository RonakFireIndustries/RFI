import { useEffect } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import { usePermission } from './usePermission';
import { VOUCHER_TYPES } from '../config/vouchers';
import {
  VOUCHER_SHORTCUTS,
  isTypingTarget,
  newVoucherPath,
  shortcutsLiveFor,
} from '../config/voucherShortcuts';

/**
 * Tally-style voucher keys: F4…F9 (Alt+F8/F9 for the notes) open a new voucher
 * of that type.
 *
 * Rules of engagement — all of them in `shortcutsLiveFor`:
 *   - only on `/dashboard/accounting/*`: elsewhere the keys keep their
 *     browser meaning (F5 must still refresh the page);
 *   - never on a voucher entry screen, so a half-typed draft is never thrown
 *     away by a stray keystroke;
 *   - never while focus sits in an input, textarea, select or contenteditable;
 *   - never with Ctrl / Meta / Shift held, and never on key repeat;
 *   - gated by the voucher type's own access module on the `create` action,
 *     exactly like the "New …" button it stands in for — the backend rejects
 *     anything unauthorised regardless.
 *
 * The key list itself lives in config/voucherShortcuts.js next to the hints
 * that advertise it; the sidebar and this handler read the same predicate, so
 * a key can only ever do what the hint promised.
 */
export const useVoucherShortcuts = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const { canModule } = usePermission();

  useEffect(() => {
    if (!shortcutsLiveFor(location.pathname)) return undefined;

    const onKeyDown = (event) => {
      if (event.defaultPrevented || event.repeat) return;
      if (event.ctrlKey || event.metaKey || event.shiftKey) return;
      if (isTypingTarget(event.target)) return;

      const shortcut = VOUCHER_SHORTCUTS.find(
        (s) => s.key === event.key && s.alt === event.altKey
      );
      if (!shortcut) return;

      const type = VOUCHER_TYPES[shortcut.type];
      if (!type || !canModule(type.module, 'create')) return;

      event.preventDefault();

      const path = newVoucherPath(shortcut.type);
      if (location.pathname !== path) navigate(path);
    };

    window.addEventListener('keydown', onKeyDown);
    return () => window.removeEventListener('keydown', onKeyDown);
  }, [location.pathname, navigate, canModule]);
};
