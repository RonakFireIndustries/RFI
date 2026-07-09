import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import {
  Calendar, FileText, User, CheckCircle2, XCircle, Building2,
  Clock, Paperclip, ChevronRight, ShieldCheck, ShieldX, History,
} from 'lucide-react';
import { useLeaveRequestStore } from '../../store/leaveRequestStore';

const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-GB') : '--';
const fmtDateTime = (d) => d ? new Date(d).toLocaleString('en-GB') : '--';

const statusBadgeClass = (status) => {
  switch (status) {
    case 'Approved': return 'bg-green-100 text-green-800';
    case 'Rejected': return 'bg-red-100 text-red-800';
    case 'Cancelled': return 'bg-gray-100 text-gray-500';
    case 'Submitted': return 'bg-blue-100 text-blue-800';
    default: return 'bg-yellow-100 text-yellow-800';
  }
};

export default function LeaveApprovalPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { selected, loading, fetchItem, approveLeave, rejectLeave } = useLeaveRequestStore();
  const [comments, setComments] = useState('');
  const [actionLoading, setActionLoading] = useState(false);
  const [fetchError, setFetchError] = useState(null);

  useEffect(() => {
    setFetchError(null);
    fetchItem(id).catch((err) => {
      setFetchError(err?.response?.data?.message || err?.message || 'Failed to load leave request');
    });
  }, [id, fetchItem]);

  const handleAction = async (action) => {
    setActionLoading(true);
    try {
      if (action === 'approve') {
        await approveLeave(id, { comments });
      } else if (action === 'reject') {
        if (!comments) return alert('Comments are required for rejection.');
        await rejectLeave(id, { comments });
      }
      navigate('/dashboard/leave-management/requests');
    } catch (error) {
      alert('Action failed: ' + error.message);
    } finally {
      setActionLoading(false);
    }
  };

  if (fetchError) return <div className="p-8 text-center text-red-600 font-medium">{fetchError}</div>;
  if (loading || !selected) return <div className="p-8 text-center text-gray-500">Loading request details...</div>;

  // Handle if selected is wrapped in { leave_request: {...} }
  const leave = selected?.id ? selected : (selected?.leave_request || selected);
  if (!leave?.id) return <div className="p-8 text-center text-gray-500">Leave request data not found.</div>;

  const histories = leave.histories || [];

  return (
    <div className="max-w-4xl mx-auto space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Leave Request Details</h1>
          <p className="text-sm text-gray-500 mt-1">Reference #{leave.id}</p>
        </div>
        <span className={`inline-flex self-start items-center px-3 py-1.5 rounded-full text-sm font-semibold ${statusBadgeClass(leave.status)}`}>
          {leave.status}
        </span>
      </div>

      {/* Main Details Card */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div className="p-6 space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div className="space-y-1">
              <label className="text-xs text-gray-400 uppercase tracking-wider font-semibold flex items-center">
                <User className="w-3.5 h-3.5 mr-1.5" /> Employee
              </label>
              <p className="font-semibold text-gray-900">{leave.employee?.full_name || leave.employee?.name || 'N/A'}</p>
              {leave.employee?.department && (
                <p className="text-sm text-gray-500 flex items-center">
                  <Building2 className="w-3.5 h-3.5 mr-1" />
                  {leave.employee.department.name}
                </p>
              )}
            </div>

            <div className="space-y-1">
              <label className="text-xs text-gray-400 uppercase tracking-wider font-semibold flex items-center">
                <FileText className="w-3.5 h-3.5 mr-1.5" /> Leave Type
              </label>
              <p className="font-semibold text-gray-900">{leave.leave_type?.name || 'N/A'}</p>
              {leave.leave_type?.code && (
                <p className="text-sm text-gray-500">{leave.leave_type.code}</p>
              )}
            </div>

            <div className="space-y-1">
              <label className="text-xs text-gray-400 uppercase tracking-wider font-semibold flex items-center">
                <Calendar className="w-3.5 h-3.5 mr-1.5" /> Start Date
              </label>
              <p className="font-semibold text-gray-900">{fmtDate(leave.start_date)}</p>
            </div>

            <div className="space-y-1">
              <label className="text-xs text-gray-400 uppercase tracking-wider font-semibold flex items-center">
                <Calendar className="w-3.5 h-3.5 mr-1.5" /> End Date
              </label>
              <p className="font-semibold text-gray-900">{fmtDate(leave.end_date)}</p>
            </div>

            <div className="space-y-1">
              <label className="text-xs text-gray-400 uppercase tracking-wider font-semibold flex items-center">
                <Clock className="w-3.5 h-3.5 mr-1.5" /> Duration
              </label>
              <p className="font-semibold text-gray-900">
                {leave.total_days ?? '-'} day(s)
                {leave.is_half_day ? <span className="text-sm text-gray-500 ml-1">(Half Day)</span> : ''}
              </p>
            </div>

            <div className="space-y-1">
              <label className="text-xs text-gray-400 uppercase tracking-wider font-semibold flex items-center">
                <Clock className="w-3.5 h-3.5 mr-1.5" /> Applied On
              </label>
              <p className="font-semibold text-gray-900">{fmtDateTime(leave.applied_at)}</p>
            </div>
          </div>

          {/* Reason */}
          <div className="space-y-2 pt-4 border-t border-gray-100">
            <label className="text-xs text-gray-400 uppercase tracking-wider font-semibold">Reason</label>
            <div className="bg-gray-50 p-4 rounded-lg text-sm text-gray-700 whitespace-pre-wrap">
              {leave.reason || '-'}
            </div>
          </div>

          {/* Comments / Resolution Notes */}
          {leave.comments && (
            <div className="space-y-2 pt-2">
              <label className="text-xs text-gray-400 uppercase tracking-wider font-semibold">Resolution Notes</label>
              <div className="bg-blue-50 border border-blue-100 p-4 rounded-lg text-sm text-gray-700 whitespace-pre-wrap">
                {leave.comments}
              </div>
            </div>
          )}

          {/* Attachment */}
          {leave.attachment_path && (
            <div className="pt-2">
              <a
                href={leave.attachment_path}
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-700"
              >
                <Paperclip className="w-4 h-4" />
                View Attachment
              </a>
            </div>
          )}

          {/* Approver Info */}
          {leave.approver && (
            <div className="pt-4 border-t border-gray-100">
              <label className="text-xs text-gray-400 uppercase tracking-wider font-semibold flex items-center mb-2">
                {leave.status === 'Approved' ? <ShieldCheck className="w-3.5 h-3.5 mr-1.5 text-green-500" /> : <ShieldX className="w-3.5 h-3.5 mr-1.5 text-red-500" />}
                Reviewed By
              </label>
              <div className="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                <div className="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center text-sm font-bold text-gray-600">
                  {leave.approver.name?.substring(0, 2).toUpperCase() || 'NA'}
                </div>
                <div>
                  <p className="font-medium text-gray-900">{leave.approver.name}</p>
                  <p className="text-xs text-gray-500">{leave.approver.email}</p>
                </div>
              </div>
            </div>
          )}

          {/* Manager Review Actions */}
          {leave.status === 'Submitted' && (
            <div className="border-t border-gray-200 pt-6 space-y-4">
              <h3 className="font-semibold text-gray-800 flex items-center gap-2">
                <CheckCircle2 className="w-4 h-4 text-gray-400" />
                Manager Review
              </h3>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Comments</label>
                <textarea
                  value={comments}
                  onChange={(e) => setComments(e.target.value)}
                  className="w-full border-gray-300 rounded-lg shadow-sm focus:ring-ring focus:border-ring"
                  rows="3"
                  placeholder="Enter approval or rejection comments..."
                />
              </div>
              <div className="flex flex-wrap gap-3">
                <button
                  onClick={() => handleAction('approve')}
                  disabled={actionLoading}
                  className="inline-flex items-center px-5 py-2.5 bg-green-600 text-white rounded-lg font-semibold text-sm hover:bg-green-700 disabled:opacity-50 transition-colors"
                >
                  <CheckCircle2 className="w-4 h-4 mr-2" />
                  {actionLoading ? 'Processing...' : 'Approve Leave'}
                </button>
                <button
                  onClick={() => handleAction('reject')}
                  disabled={actionLoading}
                  className="inline-flex items-center px-5 py-2.5 bg-red-600 text-white rounded-lg font-semibold text-sm hover:bg-red-700 disabled:opacity-50 transition-colors"
                >
                  <XCircle className="w-4 h-4 mr-2" />
                  {actionLoading ? 'Processing...' : 'Reject'}
                </button>
              </div>
            </div>
          )}
        </div>
      </div>

      {/* Approval History Timeline */}
      {histories.length > 0 && (
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h3 className="font-semibold text-gray-800 flex items-center gap-2 mb-5">
            <History className="w-4 h-4 text-gray-400" />
            Approval History
          </h3>
          <div className="space-y-0">
            {histories.map((h, idx) => (
              <div key={h.id || idx} className="relative flex gap-4 pb-5 last:pb-0">
                {idx < histories.length - 1 && (
                  <div className="absolute left-[11px] top-6 bottom-0 w-0.5 bg-gray-200" />
                )}
                <div className={`flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center ${
                  h.action === 'Approved' ? 'bg-green-100' :
                  h.action === 'Rejected' ? 'bg-red-100' :
                  h.action === 'Cancelled' ? 'bg-gray-100' :
                  'bg-blue-100'
                }`}>
                  <div className={`w-2.5 h-2.5 rounded-full ${
                    h.action === 'Approved' ? 'bg-green-500' :
                    h.action === 'Rejected' ? 'bg-red-500' :
                    h.action === 'Cancelled' ? 'bg-gray-400' :
                    'bg-blue-500'
                  }`} />
                </div>
                <div className="flex-1 min-w-0 pt-0.5">
                  <div className="flex items-center gap-2">
                    <span className="font-semibold text-sm text-gray-900">{h.action}</span>
                    <span className="text-xs text-gray-400">
                      {h.created_at ? fmtDateTime(h.created_at) : ''}
                    </span>
                  </div>
                  {h.user && (
                    <p className="text-xs text-gray-500 mt-0.5">by {h.user.name}</p>
                  )}
                  {h.comments && (
                    <p className="text-sm text-gray-600 mt-1 bg-gray-50 p-2.5 rounded-md">{h.comments}</p>
                  )}
                </div>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
