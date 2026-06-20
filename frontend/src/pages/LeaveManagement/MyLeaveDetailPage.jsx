import React, { useEffect, useState } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { ArrowLeft, XCircle, Calendar, Clock, FileText, User } from 'lucide-react';
import { leaveRequestsService } from '../../services/leaveRequestsService';

export default function MyLeaveDetailPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [leave, setLeave] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetch = async () => {
      try {
        const res = await leaveRequestsService.getById(id);
        setLeave(res?.data?.leave_request || res?.data?.data?.leave_request || res?.data);
      } catch {
        alert('Leave request not found');
        navigate('/dashboard/my-leaves');
      } finally {
        setLoading(false);
      }
    };
    fetch();
  }, [id, navigate]);

  const handleCancel = async () => {
    if (!confirm('Are you sure you want to cancel this leave request?')) return;
    const comments = prompt('Please provide a reason for cancellation:');
    if (!comments) return;
    try {
      const res = await leaveRequestsService.cancel(id, { comments });
      setLeave(res?.data?.leave_request || res?.data?.data?.leave_request);
    } catch (error) {
      alert('Failed to cancel: ' + (error.response?.data?.message || error.message));
    }
  };

  const canCancel = leave && (leave.status === 'Submitted' || leave.status === 'Draft');

  if (loading) {
    return <div className="text-center py-10 text-gray-500">Loading...</div>;
  }

  if (!leave) {
    return <div className="text-center py-10 text-gray-500">Leave request not found.</div>;
  }

  const statusBadgeClass = leave.status === 'Approved' ? 'bg-green-100 text-green-800' :
    leave.status === 'Rejected' ? 'bg-red-100 text-red-800' :
    leave.status === 'Submitted' ? 'bg-blue-100 text-blue-800' :
    leave.status === 'Cancelled' ? 'bg-gray-100 text-gray-500' :
    'bg-gray-100 text-gray-800';

  return (
    <div className="max-w-2xl mx-auto space-y-6">
      <div className="flex items-center justify-between">
        <button
          onClick={() => navigate('/dashboard/my-leaves')}
          className="inline-flex items-center text-sm text-gray-500 hover:text-gray-700"
        >
          <ArrowLeft className="w-4 h-4 mr-1" />
          Back to My Leaves
        </button>
        <span className={`inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${statusBadgeClass}`}>
          {leave.status}
        </span>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div className="p-6 space-y-5">
          <div className="flex items-center gap-3 pb-4 border-b border-gray-100">
            <div className="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
              <Calendar className="w-5 h-5 text-blue-600" />
            </div>
            <div>
              <h2 className="text-lg font-semibold text-gray-900">{leave.leave_type?.name || 'Leave'}</h2>
              <p className="text-sm text-gray-500">{leave.total_days} day(s)</p>
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div className="flex items-start gap-3">
              <Calendar className="w-4 h-4 text-gray-400 mt-0.5" />
              <div>
                <p className="text-xs text-gray-500 uppercase tracking-wider font-medium">Start Date</p>
                <p className="text-sm font-medium text-gray-900">{leave.start_date ? new Date(leave.start_date).toLocaleDateString() : '--'}</p>
              </div>
            </div>
            <div className="flex items-start gap-3">
              <Calendar className="w-4 h-4 text-gray-400 mt-0.5" />
              <div>
                <p className="text-xs text-gray-500 uppercase tracking-wider font-medium">End Date</p>
                <p className="text-sm font-medium text-gray-900">{leave.end_date ? new Date(leave.end_date).toLocaleDateString() : '--'}</p>
              </div>
            </div>
            {leave.is_half_day && (
              <div className="flex items-start gap-3">
                <Clock className="w-4 h-4 text-gray-400 mt-0.5" />
                <div>
                  <p className="text-xs text-gray-500 uppercase tracking-wider font-medium">Half Day</p>
                  <p className="text-sm font-medium text-gray-900">Yes</p>
                </div>
              </div>
            )}
          </div>

          <div className="flex items-start gap-3">
            <FileText className="w-4 h-4 text-gray-400 mt-0.5" />
            <div>
              <p className="text-xs text-gray-500 uppercase tracking-wider font-medium">Reason</p>
              <p className="text-sm text-gray-700">{leave.reason || '--'}</p>
            </div>
          </div>

          {leave.comments && (
            <div className="flex items-start gap-3">
              <FileText className="w-4 h-4 text-gray-400 mt-0.5" />
              <div>
                <p className="text-xs text-gray-500 uppercase tracking-wider font-medium">Comments / Resolution Notes</p>
                <p className="text-sm text-gray-700">{leave.comments}</p>
              </div>
            </div>
          )}

          <div className="flex items-start gap-3">
            <User className="w-4 h-4 text-gray-400 mt-0.5" />
            <div>
              <p className="text-xs text-gray-500 uppercase tracking-wider font-medium">Submitted By</p>
              <p className="text-sm font-medium text-gray-900">{leave.employee?.user?.name || leave.employee?.name || '--'}</p>
            </div>
          </div>
        </div>
      </div>

      {canCancel && (
        <div className="flex justify-end">
          <button
            onClick={handleCancel}
            className="inline-flex items-center px-4 py-2 border border-red-300 rounded-lg text-sm font-semibold text-red-600 hover:bg-red-50"
          >
            <XCircle className="w-4 h-4 mr-2" />
            Cancel Leave Request
          </button>
        </div>
      )}
    </div>
  );
}
