import React, { useEffect, useMemo, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Calendar, CheckCircle2, XCircle, Clock, Users, ClipboardList, ArrowRight, Send } from 'lucide-react';
import { useLeaveRequestStore } from '../../store/leaveRequestStore';
import { useLeaveTypeStore } from '../../store/leaveTypeStore';
import PermissionGate from '../../components/Guards/PermissionGate';

// `Submitted` is what the app writes; `Pending` is the column default on older rows.
const PENDING_STATUSES = ['Submitted', 'Pending'];

const MONTH_NAMES = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

// Dates come back as 'YYYY-MM-DD'; splitting the string avoids the UTC shift
// that `new Date('YYYY-MM-DD')` applies in non-UTC timezones.
const formatDate = (value) => {
  if (!value) return '-';
  const [year, month, day] = String(value).slice(0, 10).split('-');
  if (!year || !month || !day) return String(value);
  return `${day} ${MONTH_NAMES[Number(month) - 1]} ${year}`;
};

const employeeName = (employee) =>
  employee?.full_name
  || [employee?.first_name, employee?.last_name].filter(Boolean).join(' ')
  || employee?.name
  || 'Unknown employee';

const statusBadgeClass = (status) => {
  if (status === 'Approved') return 'bg-green-100 text-green-800';
  if (status === 'Rejected') return 'bg-red-100 text-red-800';
  if (PENDING_STATUSES.includes(status)) return 'bg-yellow-100 text-yellow-800';
  return 'bg-gray-100 text-gray-800';
};

function MetricCard({ to, icon: Icon, iconBg, label, value }) {
  return (
    <Link
      to={to}
      className="group bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center hover:shadow-md hover:border-gray-200 transition-all"
    >
      <div className={`w-12 h-12 rounded-lg ${iconBg} flex justify-center items-center mr-4 shrink-0`}>
        <Icon className="w-6 h-6" />
      </div>
      <div className="min-w-0">
        <p className="text-sm font-medium text-gray-500">{label}</p>
        <h3 className="text-2xl font-bold text-gray-800">{value}</h3>
      </div>
      <ArrowRight className="w-4 h-4 ml-auto text-gray-300 opacity-0 group-hover:opacity-100 transition-opacity" />
    </Link>
  );
}

function PendingRequestRow({ request, onDecide, onDone }) {
  const [mode, setMode] = useState(null); // 'approve' | 'reject' | null
  const [comments, setComments] = useState('');
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState(null);

  const start = (nextMode) => {
    setMode(nextMode);
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
      onDone(mode, employeeName(request.employee));
      setMode(null);
      setComments('');
    } catch (err) {
      setError(err?.response?.data?.message || err.message || 'Could not update this request.');
    } finally {
      setBusy(false);
    }
  };

  return (
    <li className="border border-gray-100 rounded-lg p-4">
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div className="min-w-0">
          <p className="font-semibold text-gray-900 text-sm">{employeeName(request.employee)}</p>
          <p className="text-xs text-gray-500 mt-0.5 flex items-center gap-1.5 flex-wrap">
            <Calendar className="w-3.5 h-3.5" />
            {request.leave_type?.name || 'Leave'}
            <span className="text-gray-300">|</span>
            {formatDate(request.start_date)} &ndash; {formatDate(request.end_date)}
            <span className="text-gray-300">|</span>
            {request.total_days ?? '-'} day(s)
          </p>
          {request.reason && (
            <p className="text-xs text-gray-500 mt-1 line-clamp-2">{request.reason}</p>
          )}
        </div>

        <div className="flex items-center gap-2 shrink-0">
          <Link
            to={`/dashboard/leave-management/requests/${request.id}`}
            className="inline-flex items-center rounded-md border border-gray-200 px-2 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
          >
            View
          </Link>

          {mode === null && (
            <>
              <PermissionGate module="leaves" action="approve" subject="this leave">
                <button
                  type="button"
                  onClick={() => start('approve')}
                  className="inline-flex items-center rounded-md bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-700"
                >
                  <CheckCircle2 className="w-3.5 h-3.5 mr-1" />
                  Approve
                </button>
              </PermissionGate>

              <PermissionGate module="leaves" action="reject" subject="this leave">
                <button
                  type="button"
                  onClick={() => start('reject')}
                  className="inline-flex items-center rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700"
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
        <div className="mt-3 border-t border-gray-100 pt-3">
          <label className="block text-xs font-medium text-gray-600 mb-1">
            Comments {mode === 'reject' ? '(required)' : '(optional)'}
          </label>
          <textarea
            value={comments}
            onChange={(e) => setComments(e.target.value)}
            rows="2"
            autoFocus
            placeholder={mode === 'reject' ? 'Reason for rejecting...' : 'Optional approval note...'}
            className="w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-ring focus:border-ring"
          />

          {error && <p className="text-xs text-red-600 mt-1">{error}</p>}

          <div className="flex items-center gap-2 mt-2">
            <button
              type="button"
              onClick={submit}
              disabled={busy}
              className={`inline-flex items-center rounded-md px-3 py-1.5 text-xs font-semibold text-white disabled:opacity-50 ${mode === 'approve' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'}`}
            >
              <Send className="w-3.5 h-3.5 mr-1" />
              {busy ? 'Saving...' : mode === 'approve' ? 'Confirm Approval' : 'Confirm Rejection'}
            </button>
            <button
              type="button"
              onClick={() => { setMode(null); setComments(''); setError(null); }}
              disabled={busy}
              className="rounded-md border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
            >
              Cancel
            </button>
          </div>
        </div>
      )}
    </li>
  );
}

export default function LeaveDashboardPage() {
  const {
    items: requests,
    fetchItems: fetchRequests,
    approveLeave,
    rejectLeave,
    loading: reqLoading,
  } = useLeaveRequestStore();
  const { items: types, fetchItems: fetchTypes } = useLeaveTypeStore();
  const navigate = useNavigate();
  const [flash, setFlash] = useState(null);

  useEffect(() => {
    fetchRequests({ per_page: 500 });
    fetchTypes({ per_page: 50 });
  }, [fetchRequests, fetchTypes]);

  useEffect(() => {
    if (!flash) return undefined;
    const timer = setTimeout(() => setFlash(null), 6000);
    return () => clearTimeout(timer);
  }, [flash]);

  const pending = requests.filter((r) => PENDING_STATUSES.includes(r.status)).length;
  const approved = requests.filter((r) => r.status === 'Approved').length;
  const rejected = requests.filter((r) => r.status === 'Rejected').length;

  const today = new Date().toISOString().split('T')[0];
  const onLeaveToday = requests.filter((r) => r.status === 'Approved' && r.start_date <= today && r.end_date >= today).length;

  const pendingItems = useMemo(
    () => requests.filter((r) => PENDING_STATUSES.includes(r.status)).slice(0, 8),
    [requests]
  );

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

  if (reqLoading && requests.length === 0) {
    return <div className="p-8 text-center text-gray-500">Loading Dashboard...</div>;
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div>
          <h1 className="text-2xl font-bold text-gray-800">Leave Dashboard</h1>
          <p className="text-gray-500 text-sm mt-1">Overview of organizational leave metrics.</p>
        </div>
        <div className="flex flex-wrap items-center gap-2">
          <Link
            to="/dashboard/leave-management/requests/new"
            className="inline-flex items-center rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-primary/90 transition-colors"
          >
            <Calendar className="w-4 h-4 mr-2" />
            Apply Leave
          </Link>
          <button
            type="button"
            onClick={() => navigate('/dashboard/leave-management/requests')}
            className="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors"
          >
            <ClipboardList className="w-4 h-4 mr-2" />
            Manage Requests
          </button>
        </div>
      </div>

      {flash && (
        <div className={`rounded-xl border px-4 py-3 text-sm font-medium ${flash.type === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-blue-200 bg-blue-50 text-blue-800'}`}>
          {flash.text}
        </div>
      )}

      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        <MetricCard
          to="/dashboard/leave-management/requests?status=Submitted"
          icon={Clock}
          iconBg="bg-blue-100 text-blue-600"
          label="Pending Requests"
          value={pending}
        />
        <MetricCard
          to="/dashboard/leave-management/requests?status=Approved"
          icon={CheckCircle2}
          iconBg="bg-green-100 text-green-600"
          label="Approved Leaves"
          value={approved}
        />
        <MetricCard
          to="/dashboard/leave-management/requests?status=Rejected"
          icon={XCircle}
          iconBg="bg-red-100 text-red-600"
          label="Rejected Leaves"
          value={rejected}
        />
        <MetricCard
          to="/dashboard/leave-management/requests?status=Approved"
          icon={Users}
          iconBg="bg-orange-100 text-orange-600"
          label="On Leave Today"
          value={onLeaveToday}
        />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <div className="flex items-center justify-between mb-4">
            <h2 className="font-bold text-gray-800">Awaiting Approval</h2>
            <Link
              to="/dashboard/leave-management/requests?status=Submitted"
              className="text-sm font-medium text-primary hover:text-primary/80"
            >
              View all ({pending})
            </Link>
          </div>

          {pendingItems.length === 0 ? (
            <p className="text-sm text-gray-500 text-center py-8">No leave requests are awaiting approval.</p>
          ) : (
            <ul className="space-y-3">
              {pendingItems.map((request) => (
                <PendingRequestRow
                  key={request.id}
                  request={request}
                  onDecide={decide}
                  onDone={handleDone}
                />
              ))}
            </ul>
          )}
        </div>

        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <div className="flex items-center justify-between mb-4">
            <h2 className="font-bold text-gray-800">Recent Activity</h2>
            <Link
              to="/dashboard/leave-management/requests"
              className="text-sm font-medium text-primary hover:text-primary/80"
            >
              View all
            </Link>
          </div>

          <div className="space-y-1">
            {requests.slice(0, 6).map((req) => (
              <button
                key={req.id}
                type="button"
                onClick={() => navigate(`/dashboard/leave-management/requests/${req.id}`)}
                className="w-full flex items-center justify-between gap-3 rounded-lg border-b border-gray-50 px-2 py-2 last:border-0 last:pb-0 text-left hover:bg-gray-50 transition-colors"
              >
                <div className="min-w-0">
                  <p className="text-gray-800 font-medium text-sm truncate">{employeeName(req.employee)}</p>
                  <p className="text-xs text-gray-500">{req.leave_type?.name} &middot; {formatDate(req.start_date)}</p>
                </div>
                <span className={`px-2 py-1 text-xs rounded-full shrink-0 ${statusBadgeClass(req.status)}`}>
                  {req.status}
                </span>
              </button>
            ))}
            {requests.length === 0 && <p className="text-sm text-gray-500 text-center py-8">No recent activity.</p>}
          </div>
        </div>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div className="flex items-center justify-between mb-4">
          <h2 className="font-bold text-gray-800">Leave Types Overview</h2>
          <Link
            to="/dashboard/leave-management/types"
            className="text-sm font-medium text-primary hover:text-primary/80"
          >
            Manage types
          </Link>
        </div>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          {types.map((type) => (
            <div key={type.id} className="flex items-center justify-between gap-3 rounded-lg border border-gray-100 px-4 py-3">
              <div className="min-w-0">
                <p className="text-gray-800 font-medium text-sm truncate">{type.name} ({type.code})</p>
                {type.gender_restriction && (
                  <p className="text-xs text-gray-500">{type.gender_restriction} only</p>
                )}
              </div>
              <span className="text-sm text-gray-500 shrink-0">{type.annual_allocation} Days/Yr</span>
            </div>
          ))}
          {types.length === 0 && <p className="text-sm text-gray-500">No leave types configured.</p>}
        </div>
      </div>
    </div>
  );
}
