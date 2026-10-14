import { useEffect, useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import { ChevronDown, ChevronRight, ClipboardCheck, ClipboardList } from 'lucide-react';
import { useLeaveRequestStore } from '../../store/leaveRequestStore';
import PendingLeaveRow from '../LeaveManagement/PendingLeaveRow';
import { PENDING_STATUSES } from '../../utils/leaveRequests';

// Only rows awaiting a decision are fetched; the API accepts a comma-separated
// status list so `Pending`-era rows are included alongside `Submitted`.
const PENDING_FILTER = PENDING_STATUSES.join(',');

/**
 * Dashboard widget for reviewers: every leave request waiting on a decision,
 * with inline Approve / Reject. Rendered on the role dashboards (admin and any
 * other type) for accounts holding the leave approve/reject permission.
 */
export default function LeaveApprovalsPanel() {
  const { items, fetchItems, approveLeave, rejectLeave } = useLeaveRequestStore();
  const [expanded, setExpanded] = useState(true);
  const [loaded, setLoaded] = useState(false);
  const [hidden, setHidden] = useState(false);
  const [error, setError] = useState(null);
  const [flash, setFlash] = useState(null);

  useEffect(() => {
    let active = true;

    (async () => {
      try {
        // Ask for just the undecided rows - one small page. A backend that only
        // understands a single status matches nothing for "Submitted,Pending",
        // so if that comes back empty fall back to filtering a recent page on
        // the client (exactly what the Leave Dashboard counters do). This keeps
        // the widget correct whether or not the API supports status lists yet.
        const rows = await fetchItems({ status: PENDING_FILTER, per_page: 20 });

        if (active && (rows?.length ?? 0) === 0) {
          await fetchItems({ per_page: 500 });
        }

        if (active) setLoaded(true);
      } catch (err) {
        if (!active) return;
        const status = err?.response?.status;
        // 403 means the account can review but not list; 404/405 means this
        // backend predates the endpoint. Stay hidden rather than showing a
        // broken widget on the dashboard.
        if (status === 403 || status === 404 || status === 405) {
          setHidden(true);
        } else {
          setError(err?.response?.data?.message || err.message || 'Could not load leave requests.');
        }
        setLoaded(true);
      }
    })();

    return () => { active = false; };
  }, [fetchItems]);

  useEffect(() => {
    if (!flash) return undefined;
    const timer = setTimeout(() => setFlash(null), 6000);
    return () => clearTimeout(timer);
  }, [flash]);

  const pending = useMemo(
    () => items.filter((row) => PENDING_STATUSES.includes(row.status)),
    [items]
  );

  if (hidden) return null;

  const decide = async (id, action, comments) => {
    if (action === 'approve') {
      await approveLeave(id, comments ? { comments } : {});
    } else {
      await rejectLeave(id, { comments });
    }
  };

  const handleDone = (action, name) => {
    setFlash({
      type: action === 'approve' ? 'success' : 'info',
      text: `${name}'s leave was ${action === 'approve' ? 'approved' : 'rejected'}.`,
    });
  };

  const isLoading = !loaded;

  return (
    <div className="bg-card rounded-xl shadow-sm border border-border overflow-hidden">
      <div className="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-b border-border">
        <button
          type="button"
          onClick={() => setExpanded((open) => !open)}
          className="flex items-center gap-3 min-w-0 text-left group"
          aria-expanded={expanded}
        >
          {expanded
            ? <ChevronDown className="w-4 h-4 text-muted-foreground shrink-0" />
            : <ChevronRight className="w-4 h-4 text-muted-foreground shrink-0" />}
          <span className="p-2 rounded-lg bg-primary/10 text-primary shrink-0">
            <ClipboardCheck className="w-5 h-5" />
          </span>
          <span className="min-w-0">
            <span className="block text-sm font-bold text-foreground group-hover:text-primary transition-colors">
              Leave Requests to Approve
            </span>
            <span className="block text-xs text-muted-foreground">
              {isLoading
                ? 'Loading...'
                : (pending.length === 0
                  ? 'Nothing awaiting approval'
                  : `${pending.length} awaiting your approval`)}
            </span>
          </span>
        </button>

        <div className="flex items-center gap-2 shrink-0">
          <Link
            to="/dashboard/leave-management/requests?status=Submitted"
            className="hidden sm:inline-flex rounded-md border border-border px-3 py-1.5 text-xs font-medium text-foreground hover:bg-muted transition-colors"
          >
            View all{pending.length > 0 ? ` (${pending.length})` : ''}
          </Link>
          <Link
            to="/dashboard/leave-management"
            className="inline-flex items-center rounded-md bg-primary px-3 py-1.5 text-xs font-semibold text-white hover:bg-primary/90 transition-colors"
          >
            <ClipboardList className="w-3.5 h-3.5 mr-1" />
            Manage
          </Link>
        </div>
      </div>

      {expanded && (
        <div className="p-5">
          {flash && (
            <div className={`mb-3 rounded-lg border px-3 py-2 text-xs font-medium ${flash.type === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-blue-200 bg-blue-50 text-blue-800'}`}>
              {flash.text}
            </div>
          )}

          {error && <p className="text-sm text-destructive">{error}</p>}

          {isLoading && <p className="text-sm text-muted-foreground">Loading...</p>}

          {loaded && !error && pending.length === 0 && (
            <div className="flex items-center gap-3 rounded-lg border border-dashed border-border px-4 py-5">
              <ClipboardCheck className="w-5 h-5 text-muted-foreground shrink-0" />
              <div>
                <p className="text-sm font-medium text-foreground">No leave requests awaiting approval</p>
                <p className="text-xs text-muted-foreground mt-0.5">
                  Anything an employee submits will show up here until it is decided.
                </p>
              </div>
            </div>
          )}

          {pending.length > 0 && (
            <ul className="space-y-3">
              {pending.map((request) => (
                <PendingLeaveRow
                  key={request.id}
                  request={request}
                  onDecide={decide}
                  onDone={handleDone}
                />
              ))}
            </ul>
          )}
        </div>
      )}
    </div>
  );
}
