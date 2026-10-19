/**
 * One key cap, Tally-style.
 *
 * Shared by the sidebar's per-voucher hints and the legend on the voucher
 * registers so both render the same thing. Only the neutral shape lives here;
 * each caller passes the colours for its own surface (the sidebar sits on
 * white *and* on the blue active row), so there are no baked-in defaults to
 * fight with.
 */
const ShortcutKey = ({ children, className = '' }) => (
  <kbd className={`px-1.5 py-0.5 rounded border text-[10px] font-semibold ${className}`}>
    {children}
  </kbd>
);

export default ShortcutKey;
