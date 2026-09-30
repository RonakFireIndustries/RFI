import { useCallback, useEffect, useState } from 'react';
import { createPortal } from 'react-dom';

const GAP = 8;
const EDGE = 8;

/**
 * Dependency-free tooltip rendered through a portal.
 *
 * Two constraints shaped this:
 *
 *  1. The hover target is the WRAPPER, never the child. A disabled <button>
 *     swallows pointer events, so a tooltip attached to it would never appear.
 *
 *  2. It is portalled to document.body and positioned `fixed`. In-table action
 *     buttons live inside DataTable's `overflow-hidden` + `overflow-x-auto`
 *     wrapper, and `overflow-x-auto` forces overflow-y to `auto` as well, so an
 *     absolutely-positioned tooltip would be clipped or cause a scrollbar.
 */
export default function Tooltip({ content, children, side = 'top' }) {
  const [anchor, setAnchor] = useState(null);
  const [position, setPosition] = useState(null);

  const hide = useCallback(() => setPosition(null), []);

  const show = useCallback(() => {
    if (!anchor) return;
    const rect = anchor.getBoundingClientRect();

    let placement = side;
    let top = placement === 'bottom' ? rect.bottom + GAP : rect.top - GAP;

    // Flip below the trigger when there is no room above it.
    if (placement !== 'bottom' && top < EDGE) {
      placement = 'bottom';
      top = rect.bottom + GAP;
    }

    setPosition({
      top,
      left: rect.left + rect.width / 2,
      placement,
    });
  }, [anchor, side]);

  useEffect(() => {
    if (!position) return undefined;

    const dismiss = () => hide();
    window.addEventListener('scroll', dismiss, true);
    window.addEventListener('resize', dismiss);
    return () => {
      window.removeEventListener('scroll', dismiss, true);
      window.removeEventListener('resize', dismiss);
    };
  }, [position, hide]);

  if (!content) return <>{children}</>;

  return (
    <>
      <span
        ref={setAnchor}
        onMouseEnter={show}
        onMouseLeave={hide}
        onFocus={show}
        onBlur={hide}
        className="inline-flex"
      >
        {children}
      </span>
      {position &&
        createPortal(
          <span
            role="tooltip"
            style={{ top: position.top, left: position.left }}
            className={`pointer-events-none fixed z-[100] w-max max-w-[15rem] -translate-x-1/2 rounded-md bg-gray-900 px-2 py-1 text-center text-xs font-normal leading-snug text-white shadow-lg ${
              position.placement === 'bottom' ? '' : '-translate-y-full'
            }`}
          >
            {content}
          </span>,
          document.body
        )}
    </>
  );
}
