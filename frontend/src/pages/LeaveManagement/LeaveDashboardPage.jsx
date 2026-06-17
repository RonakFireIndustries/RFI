import React, { useEffect, useState } from 'react';
import { Calendar, CheckCircle2, XCircle, Clock, Users, FileText } from 'lucide-react';
import { useLeaveRequestStore } from '../../store/leaveRequestStore';
import { useLeaveTypeStore } from '../../store/leaveTypeStore';

export default function LeaveDashboardPage() {
  const { items: requests, fetchItems: fetchRequests, loading: reqLoading } = useLeaveRequestStore();
  const { items: types, fetchItems: fetchTypes } = useLeaveTypeStore();
  
  useEffect(() => {
    fetchRequests({ per_page: 500 });
    fetchTypes({ per_page: 50 });
  }, [fetchRequests, fetchTypes]);

  const pending = requests.filter(r => r.status === 'Submitted').length;
  const approved = requests.filter(r => r.status === 'Approved').length;
  const rejected = requests.filter(r => r.status === 'Rejected').length;

  const today = new Date().toISOString().split('T')[0];
  const onLeaveToday = requests.filter(r => r.status === 'Approved' && r.start_date <= today && r.end_date >= today).length;

  if (reqLoading) return <div className="p-8 text-center text-gray-500">Loading Dashboard...</div>;

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-800">Leave Dashboard</h1>
          <p className="text-gray-500 text-sm mt-1">Overview of organizational leave metrics.</p>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
          <div className="w-12 h-12 rounded-lg bg-blue-100 text-blue-600 flex justify-center items-center mr-4">
            <Clock className="w-6 h-6" />
          </div>
          <div>
            <p className="text-sm font-medium text-gray-500">Pending Requests</p>
            <h3 className="text-2xl font-bold text-gray-800">{pending}</h3>
          </div>
        </div>
        
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
          <div className="w-12 h-12 rounded-lg bg-green-100 text-green-600 flex justify-center items-center mr-4">
            <CheckCircle2 className="w-6 h-6" />
          </div>
          <div>
            <p className="text-sm font-medium text-gray-500">Approved Leaves</p>
            <h3 className="text-2xl font-bold text-gray-800">{approved}</h3>
          </div>
        </div>
        
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
          <div className="w-12 h-12 rounded-lg bg-red-100 text-red-600 flex justify-center items-center mr-4">
            <XCircle className="w-6 h-6" />
          </div>
          <div>
            <p className="text-sm font-medium text-gray-500">Rejected Leaves</p>
            <h3 className="text-2xl font-bold text-gray-800">{rejected}</h3>
          </div>
        </div>

        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
          <div className="w-12 h-12 rounded-lg bg-orange-100 text-orange-600 flex justify-center items-center mr-4">
            <Users className="w-6 h-6" />
          </div>
          <div>
            <p className="text-sm font-medium text-gray-500">On Leave Today</p>
            <h3 className="text-2xl font-bold text-gray-800">{onLeaveToday}</h3>
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <h2 className="font-bold text-gray-800 mb-4">Leave Types Overview</h2>
          <div className="space-y-4">
            {types.map(type => (
              <div key={type.id} className="flex justify-between items-center border-b border-gray-50 pb-2 last:border-0 last:pb-0">
                <span className="text-gray-700 font-medium">{type.name} ({type.code})</span>
                <span className="text-sm text-gray-500">{type.annual_allocation} Days/Yr</span>
              </div>
            ))}
          </div>
        </div>

        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <h2 className="font-bold text-gray-800 mb-4">Recent Activity</h2>
          <div className="space-y-4">
            {requests.slice(0, 5).map(req => (
              <div key={req.id} className="flex justify-between items-center border-b border-gray-50 pb-2 last:border-0 last:pb-0">
                <div>
                  <p className="text-gray-800 font-medium text-sm">{req.employee?.first_name} {req.employee?.last_name}</p>
                  <p className="text-xs text-gray-500">{req.leave_type?.name}</p>
                </div>
                <span className={`px-2 py-1 text-xs rounded-full ${
                  req.status === 'Approved' ? 'bg-green-100 text-green-800' : 
                  req.status === 'Rejected' ? 'bg-red-100 text-red-800' :
                  'bg-yellow-100 text-yellow-800'
                }`}>
                  {req.status}
                </span>
              </div>
            ))}
            {requests.length === 0 && <p className="text-sm text-gray-500 text-center">No recent activity.</p>}
          </div>
        </div>
      </div>
    </div>
  );
}
