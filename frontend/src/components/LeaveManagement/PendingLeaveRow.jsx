import { useState } from 'react';
import { Link } from 'react-router-dom';
import { Calendar, CheckCircle2, Send, XCircle } from 'lucide-react';
import PermissionGate from '../Guards/PermissionGate';
import { employeeName, formatLeaveDate } from '../../utils/leaveRequests';

/**
 * One pending leave request with inline Approve / Reject controls.
 *
 * `onDecide(id, 'approve' | 'reject', comments)` performs the call and rejects
 * on failure so the error can be shown next to the buttons; `onDone` is called
 * after a successful decision so the host page can flash a confirmation.
 *
 * A comment is required to reject (the API validates `required`), optional to
 * approve - the same asymmetry the backend enforces.
 */
export default function PendingLeaveRow({
  request,
  onDecide,
  onDone,
  detailBasePath = '/dashboard/leave-management/requests',
}) {
  const [mode, setMode] = useState(null); // 'approve' | 'reject' | null
  const [comments, setComments] = useState('');
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState(null);

  const start = (nextMode) => {
    setMode(nextMode);
    setComments('');
    setError(null);
  };

  const cancel = () => {
    setMode(null);
    setComments('');
    setError(null);
  };

  const submit = async () => {
    if (mode === 'reject' && !comments.trim()) {
      setError('A comment is required to reject a request.');
      return;
    }

    setBusy(true);
    setError(null);

    try {
      await onDecide(request.id, mode, comments.trim());
      onDone?.(mode, employeeName(request.employee));
      setMode(null);
      setComments('');
    } catch (err) {
      setError(err?.response?.data?.message || err.message || 'Could not update this request.');
    } finally {
      setBusy(false);
    }
  };

  return (
    <li className="border border-border rounded-lg p-4">
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div className="min-w-0">
          <p className="font-semibold text-foreground text-sm">{employeeName(request.employee)}</p>
          <p className="text-xs text-muted-foreground mt-0.5 flex items-center gap-1.5 flex-wrap">
            <Calendar className="w-3.5 h-3.5" />
            {request.leave_type?.name || 'Leave'}
            <span className="text-muted-foreground/40">|</span>
            {formatLeaveDate(request.start_date)} &ndash; {formatLeaveDate(request.end_date)}
            <span className="text-muted-foreground/40">|</span>
            {request.total_days ?? '-'} day(s)
          </p>
          {request.reason && (
            <p className="text-xs text-muted-foreground mt-1 line-clamp-2">{request.reason}</p>
          )}
        </div>

        <div className="flex items-center gap-2 shrink-0">
          <Link
            to={`${detailBasePath}/${request.id}`}
            className="inline-flex items-center rounded-md border border-border px-2 py-1.5 text-xs font-medium text-foreground hover:bg-muted transition-colors"
          >
            View
          </Link>

          {mode === null && (
            <>
              <PermissionGate module="leaves" action="approve" subject="this leave">
                <button
                  type="button"
                  onClick={() => start('approve')}
                  className="inline-flex items-center rounded-md bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-700 transition-colors"
                >
                  <CheckCircle2 className="w-3.5 h-3.5 mr-1" />
                  Approve
                </button>
              </PermissionGate>

              <PermissionGate module="leaves" action="reject" subject="this leave">
                <button
                  type="button"
                  onClick={() => start('reject')}
                  className="inline-flex items-center rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700 transition-colors"
                >
                  <XCircle className="w-3.5 h-3.5 mr-1" />
                  Reject
                </button>
              </PermissionGate>
            </>
          )}
        </div>
      </div>

      {mode && (
        <div className="mt-3 border-t border-border pt-3">
          <label className="block text-xs font-medium text-muted-foreground mb-1">
            Comments {mode === 'reject' ? '(required)' : '(optional)'}
          </label>
          <textarea
            value={comments}
            onChange={(e) => setComments(e.target.value)}
            rows="2"
            autoFocus
            placeholder={mode === 'reject' ? 'Reason for rejecting...' : 'Optional approval note...'}
            className="w-full border-border rounded-lg shadow-sm text-sm focus:ring-ring focus:border-ring bg-background text-foreground"
          />

          {error && <p className="text-xs text-destructive mt-1">{error}</p>}

          <div className="flex items-center gap-2 mt-2">
            <button
              type="button"
              onClick={submit}
              disabled={busy}
              className={`inline-flex items-center rounded-md px-3 py-1.5 text-xs font-semibold text-white disabled:opacity-50 transition-colors ${mode === 'approve' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'}`}
            >
              <Send className="w-3.5 h-3.5 mr-1" />
              {busy ? 'Saving...' : mode === 'approve' ? 'Confirm Approval' : 'Confirm Rejection'}
            </button>
            <button
              type="button"
              onClick={cancel}
              disabled={busy}
              className="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-foreground hover:bg-muted disabled:opacity-50 transition-colors"
            >
              Cancel
            </button>
          </div>
        </div>
      )}
    </li>
  );
}
