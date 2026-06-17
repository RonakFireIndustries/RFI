import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { CheckCircle, XCircle, AlertTriangle, ArrowLeft, Send } from 'lucide-react';
import { useDailyReportsStore } from '../../store/dailyReportStore';

export default function DailyReportDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { current: report, loading, fetchItem, approve, reject, rework } = useDailyReportsStore();
  const [comment, setComment] = useState('');
  const [actionLoading, setActionLoading] = useState('');

  useEffect(() => {
    if (id) {
      fetchItem(id);
    }
  }, [id, fetchItem]);

  if (loading || !report) {
    return <div className="p-8 text-center text-gray-500">Loading report details...</div>;
  }

  const handleAction = async (actionFn, actionName) => {
    if ((actionName === 'Reject' || actionName === 'Rework') && !comment.trim()) {
      alert(`Please provide a comment for ${actionName}.`);
      return;
    }
    setActionLoading(actionName);
    try {
      await actionFn(report.id, comment);
      setComment('');
      fetchItem(report.id);
    } catch (error) {
      alert(error.message);
    } finally {
      setActionLoading('');
    }
  };

  return (
    <div className="space-y-6 max-w-5xl mx-auto pb-12">
      <div className="flex items-center gap-4 mb-4">
        <button onClick={() => navigate(-1)} className="p-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
          <ArrowLeft className="w-5 h-5 text-gray-600" />
        </button>
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Daily Progress Report #{report.id}</h1>
          <p className="text-sm text-gray-500">Submitted by {report.employee?.full_name} on {new Date(report.date).toLocaleDateString()}</p>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2 space-y-6">
          <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div className="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 className="font-semibold text-gray-900">Work Details</h3>
            </div>
            <div className="p-6 space-y-6">
              <div>
                <h4 className="text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Work Description</h4>
                <div className="text-gray-900 whitespace-pre-wrap">{report.work_description || 'N/A'}</div>
              </div>
              <div className="grid grid-cols-2 gap-6">
                <div>
                  <h4 className="text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Tasks Completed</h4>
                  <div className="text-gray-900 whitespace-pre-wrap">{report.tasks_completed || 'None specified'}</div>
                </div>
                <div>
                  <h4 className="text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Issues Faced</h4>
                  <div className="text-gray-900 whitespace-pre-wrap">{report.issues_faced || 'None reported'}</div>
                </div>
              </div>
            </div>
          </div>

          <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div className="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 className="font-semibold text-gray-900">Resources Used</h3>
            </div>
            <div className="p-6 grid grid-cols-2 gap-6">
              <div>
                <h4 className="text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Materials</h4>
                <div className="text-gray-900 whitespace-pre-wrap">{report.materials_used || 'None specified'}</div>
              </div>
              <div>
                <h4 className="text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Equipment</h4>
                <div className="text-gray-900 whitespace-pre-wrap">{report.equipment_used || 'None specified'}</div>
              </div>
            </div>
          </div>
        </div>

        <div className="space-y-6">
          <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div className="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 className="font-semibold text-gray-900">Summary</h3>
            </div>
            <div className="p-6 space-y-4">
              <div className="flex justify-between items-center py-2 border-b border-gray-100">
                <span className="text-gray-500">Status</span>
                <span className="font-bold text-gray-900">{report.status}</span>
              </div>
              <div className="flex justify-between items-center py-2 border-b border-gray-100">
                <span className="text-gray-500">Hours Worked</span>
                <span className="font-bold text-gray-900">{report.hours_worked} hrs</span>
              </div>
              <div className="flex justify-between items-center py-2 border-b border-gray-100">
                <span className="text-gray-500">Site</span>
                <span className="font-bold text-gray-900">{report.site?.name || 'N/A'}</span>
              </div>
            </div>
          </div>

          {report.status === 'Submitted' && (
            <div className="bg-white rounded-xl shadow-sm border border-blue-200 overflow-hidden">
              <div className="px-6 py-4 border-b border-blue-100 bg-blue-50">
                <h3 className="font-semibold text-blue-900">Manager Review</h3>
              </div>
              <div className="p-6 space-y-4">
                <textarea
                  value={comment}
                  onChange={(e) => setComment(e.target.value)}
                  placeholder="Add review comments (required for Reject/Rework)..."
                  className="w-full rounded-lg border border-gray-300 p-3 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                  rows={3}
                />
                <div className="flex flex-col gap-2">
                  <button 
                    onClick={() => handleAction(approve, 'Approve')}
                    disabled={!!actionLoading}
                    className="w-full flex items-center justify-center gap-2 bg-green-600 text-white py-2.5 rounded-lg font-bold hover:bg-green-700 transition"
                  >
                    <CheckCircle className="w-4 h-4" />
                    {actionLoading === 'Approve' ? 'Processing...' : 'Approve Report'}
                  </button>
                  <div className="grid grid-cols-2 gap-2">
                    <button 
                      onClick={() => handleAction(rework, 'Rework')}
                      disabled={!!actionLoading}
                      className="w-full flex items-center justify-center gap-2 bg-orange-100 text-orange-700 py-2.5 rounded-lg font-bold hover:bg-orange-200 transition"
                    >
                      <AlertTriangle className="w-4 h-4" />
                      {actionLoading === 'Rework' ? '...' : 'Request Rework'}
                    </button>
                    <button 
                      onClick={() => handleAction(reject, 'Reject')}
                      disabled={!!actionLoading}
                      className="w-full flex items-center justify-center gap-2 bg-red-100 text-red-700 py-2.5 rounded-lg font-bold hover:bg-red-200 transition"
                    >
                      <XCircle className="w-4 h-4" />
                      {actionLoading === 'Reject' ? '...' : 'Reject'}
                    </button>
                  </div>
                </div>
              </div>
            </div>
          )}

          <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div className="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <h3 className="font-semibold text-gray-900">History Log</h3>
            </div>
            <div className="p-6 space-y-4">
              {report.histories?.length === 0 ? (
                <p className="text-sm text-gray-500">No history available.</p>
              ) : (
                report.histories?.map((history, idx) => (
                  <div key={idx} className="flex gap-3 text-sm">
                    <div className="flex flex-col items-center">
                      <div className="w-2 h-2 rounded-full bg-blue-500 mt-1.5" />
                      {idx !== report.histories.length - 1 && <div className="w-0.5 h-full bg-gray-200 my-1" />}
                    </div>
                    <div className="pb-4">
                      <div className="font-bold text-gray-900">{history.action}</div>
                      <div className="text-gray-500 text-xs">{new Date(history.created_at).toLocaleString()} by {history.user?.name || 'System'}</div>
                      {history.comments && <div className="mt-1 text-gray-700 italic bg-gray-50 p-2 rounded">"{history.comments}"</div>}
                    </div>
                  </div>
                ))
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
