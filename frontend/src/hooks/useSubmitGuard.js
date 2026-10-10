import { useCallback, useRef, useState } from 'react';

/**
 * Prevents duplicate form submissions.
 *
 * Wrap the async work of a submit handler in `guard(fn)`. While the wrapped
 * promise is pending, further calls are ignored and `submitting` is true, so a
 * submit button can be disabled and show a loading label:
 *
 *   const { submitting, guard } = useSubmitGuard();
 *   const handleSubmit = (e) => { e.preventDefault(); return guard(async () => { ... }); };
 *   <button type="submit" disabled={submitting}>{submitting ? 'Saving...' : 'Save'}</button>
 *
 * The `inFlight` ref blocks a second invocation immediately (before React
 * re-renders), so even a fast double-click cannot slip through.
 */
export function useSubmitGuard() {
  const [submitting, setSubmitting] = useState(false);
  const inFlight = useRef(false);

  const guard = useCallback(async (fn) => {
    if (inFlight.current) return undefined;
    inFlight.current = true;
    setSubmitting(true);
    try {
      return await fn();
    } finally {
      inFlight.current = false;
      setSubmitting(false);
    }
  }, []);

  return { submitting, guard };
}

export default useSubmitGuard;
