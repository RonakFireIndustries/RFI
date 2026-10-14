import React, { useEffect, useMemo, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { ArrowRight, Calendar, CheckCircle2, ClipboardList, Clock, Users, XCircle } from 'lucide-react';
import { useLeaveRequestStore } from '../../store/leaveRequestStore';
import { useLeaveTypeStore } from '../../store/leaveTypeStore';
import PendingLeaveRow from '../../components/LeaveManagement/PendingLeaveRow';
import { PENDING_STATUSES, employeeName, formatLeaveDate, statusBadgeClass } from '../../utils/leaveRequests';

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
                  <p className="text-xs text-gray-500">{req.leave_type?.name} &middot; {formatLeaveDate(req.start_date)}</p>
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
