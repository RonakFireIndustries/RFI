import { useEffect, useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import { Calendar, ChevronDown, ChevronRight, Clock, Plus } from 'lucide-react';
import { useLeaveRequestStore } from '../../store/leaveRequestStore';
import { PENDING_STATUSES, formatLeaveDate } from '../../utils/leaveRequests';

export default function MyPendingLeaves() {
  const { myItems, myLoading, fetchMyLeaves } = useLeaveRequestStore();
  const [expanded, setExpanded] = useState(null); // null until the first load settles
  const [loaded, setLoaded] = useState(false);
  const [unavailable, setUnavailable] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    let active = true;

    (async () => {
      try {
        await fetchMyLeaves({ per_page: 100 });
        if (active) setLoaded(true);
      } catch (err) {
        if (!active) return;
        const status = err?.response?.status;
        // A 404/405 means this backend predates the endpoint - stay hidden
        // rather than showing an error box on the dashboard.
        if (status === 404 || status === 405) {
          setUnavailable(true);
        } else {
          setError(err?.response?.data?.message || err.message || 'Could not load your leave requests.');
        }
        setLoaded(true);
      }
    })();

    return () => { active = false; };
  }, [fetchMyLeaves]);

  const pending = useMemo(
    () => myItems.filter((row) => PENDING_STATUSES.includes(row.status)),
    [myItems]
  );

  // Open by default only when there is something to look at.
  useEffect(() => {
    if (expanded === null && loaded) {
      setExpanded(pending.length > 0);
    }
  }, [expanded, loaded, pending.length]);

  if (unavailable) return null;

  const isOpen = expanded !== false;

  return (
    <div className="bg-card rounded-xl shadow-sm border border-border overflow-hidden">
      <div className="flex items-center justify-between gap-3 px-5 py-4 border-b border-border">
        <button
          type="button"
          onClick={() => setExpanded(!isOpen)}
          className="flex items-center gap-3 min-w-0 text-left group"
          aria-expanded={isOpen}
        >
          {isOpen
            ? <ChevronDown className="w-4 h-4 text-muted-foreground shrink-0" />
            : <ChevronRight className="w-4 h-4 text-muted-foreground shrink-0" />}
          <span className="p-2 rounded-lg bg-primary/10 text-primary shrink-0">
            <Clock className="w-5 h-5" />
          </span>
          <span className="min-w-0">
            <span className="block text-sm font-bold text-foreground group-hover:text-primary transition-colors">
              Pending Leaves
            </span>
            <span className="block text-xs text-muted-foreground">
              {loaded && !myLoading
                ? (pending.length === 0 ? 'Nothing awaiting approval' : `${pending.length} awaiting approval`)
                : 'Loading...'}
            </span>
          </span>
        </button>

        <div className="flex items-center gap-2 shrink-0">
          <Link
            to="/dashboard/my-leaves"
            className="hidden sm:inline-flex rounded-md border border-border px-3 py-1.5 text-xs font-medium text-foreground hover:bg-muted transition-colors"
          >
            View all
          </Link>
          <Link
            to="/dashboard/leave-management/requests/new"
            className="inline-flex items-center rounded-md bg-primary px-3 py-1.5 text-xs font-semibold text-white hover:bg-primary/90 transition-colors"
          >
            <Plus className="w-3.5 h-3.5 mr-1" />
            Apply
          </Link>
        </div>
      </div>

      {isOpen && (
        <div className="p-5">
          {error && <p className="text-sm text-destructive">{error}</p>}

          {!loaded && <p className="text-sm text-muted-foreground">Loading...</p>}

          {loaded && !error && pending.length === 0 && (
            <div className="flex items-center gap-3 rounded-lg border border-dashed border-border px-4 py-5">
              <Calendar className="w-5 h-5 text-muted-foreground shrink-0" />
              <div>
                <p className="text-sm font-medium text-foreground">No pending leave requests</p>
                <p className="text-xs text-muted-foreground mt-0.5">
                  Anything you apply for will show up here until it is approved.
                </p>
              </div>
            </div>
          )}

          {pending.length > 0 && (
            <ul className="space-y-2">
              {pending.map((row) => (
                <li key={row.id}>
                  <Link
                    to={`/dashboard/my-leaves/${row.id}`}
                    className="flex items-center justify-between gap-3 rounded-lg border border-border px-4 py-3 hover:bg-muted hover:border-primary/40 transition-colors"
                  >
                    <div className="min-w-0">
                      <p className="text-sm font-semibold text-foreground truncate">
                        {row.leave_type?.name || 'Leave'}
                      </p>
                      <p className="text-xs text-muted-foreground mt-0.5">
                        {formatLeaveDate(row.start_date)} &ndash; {formatLeaveDate(row.end_date)}
                        {row.total_days != null && <span> &middot; {row.total_days} day(s)</span>}
                      </p>
                    </div>
                    <span className="shrink-0 px-2.5 py-1 rounded text-[11px] font-bold bg-yellow-100 text-yellow-800">
                      {row.status}
                    </span>
                  </Link>
                </li>
              ))}
            </ul>
          )}
        </div>
      )}
    </div>
  );
}
