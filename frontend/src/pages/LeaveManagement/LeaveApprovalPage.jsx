import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Calendar, FileText, User, CheckCircle2, XCircle } from 'lucide-react';
import { useLeaveRequestStore } from '../../store/leaveRequestStore';

export default function LeaveApprovalPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { selected, loading, fetchItem, approveLeave, rejectLeave } = useLeaveRequestStore();
  const [comments, setComments] = useState('');
  const [actionLoading, setActionLoading] = useState(false);

  useEffect(() => {
    fetchItem(id);
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

  if (loading || !selected) return <div className="p-8 text-center">Loading request details...</div>;

  return (
    <div className="max-w-4xl mx-auto space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-bold text-gray-800">Leave Request Details</h1>
        <span className={`px-3 py-1 rounded-full text-sm font-medium ${
          selected.status === 'Approved' ? 'bg-green-100 text-green-800' :
          selected.status === 'Rejected' ? 'bg-red-100 text-red-800' :
          'bg-yellow-100 text-yellow-800'
        }`}>
          {selected.status}
        </span>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-8">
        <div className="grid grid-cols-2 gap-6">
          <div className="space-y-1">
            <label className="text-sm text-gray-500 flex items-center"><User className="w-4 h-4 mr-2" /> Employee</label>
            <p className="font-medium">{selected.employee?.first_name} {selected.employee?.last_name}</p>
          </div>
          <div className="space-y-1">
            <label className="text-sm text-gray-500 flex items-center"><FileText className="w-4 h-4 mr-2" /> Leave Type</label>
            <p className="font-medium">{selected.leave_type?.name}</p>
          </div>
          <div className="space-y-1">
            <label className="text-sm text-gray-500 flex items-center"><Calendar className="w-4 h-4 mr-2" /> Duration</label>
            <p className="font-medium">{selected.start_date} to {selected.end_date}</p>
          </div>
          <div className="space-y-1">
            <label className="text-sm text-gray-500 flex items-center">Total Days</label>
            <p className="font-medium">{selected.total_days} {selected.is_half_day ? '(Half Day)' : ''}</p>
          </div>
        </div>

        <div className="space-y-2">
          <label className="text-sm text-gray-500">Reason</label>
          <div className="bg-gray-50 p-4 rounded-lg text-sm text-gray-700">
            {selected.reason}
          </div>
        </div>

        {selected.status === 'Submitted' && (
          <div className="border-t border-gray-100 pt-6 space-y-4">
            <h3 className="font-semibold text-gray-800">Manager Review</h3>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Comments</label>
              <textarea
                value={comments}
                onChange={(e) => setComments(e.target.value)}
                className="w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#1a56db] focus:border-[#1a56db]"
                rows="3"
                placeholder="Enter approval or rejection comments..."
              />
            </div>
            <div className="flex space-x-3">
              <button
                onClick={() => handleAction('approve')}
                disabled={actionLoading}
                className="flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50"
              >
                <CheckCircle2 className="w-4 h-4 mr-2" />
                Approve Leave
              </button>
              <button
                onClick={() => handleAction('reject')}
                disabled={actionLoading}
                className="flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
              >
                <XCircle className="w-4 h-4 mr-2" />
                Reject
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
