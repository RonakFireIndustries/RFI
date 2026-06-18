import React, { useState, useEffect, useMemo } from 'react';
import { useAttendanceStore } from '../../store/attendanceStore';
import { useAuthStore } from '../../store/authStore';
import { Calendar, Search, MapPin, Clock, Filter, Download } from 'lucide-react';

export default function EmployeeAttendanceHistory() {
  const { user } = useAuthStore();
  const { items, loading, fetchItems } = useAttendanceStore();

  const [dateRange, setDateRange] = useState({
    start_date: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
    end_date: new Date().toISOString().split('T')[0],
  });
  const [statusFilter, setStatusFilter] = useState('');

  useEffect(() => {
    loadHistory();
  }, []);

  const loadHistory = (params = {}) => {
    fetchItems({
      employee_id: user?.employee?.id,
      start_date: dateRange.start_date,
      end_date: dateRange.end_date,
      ...(statusFilter ? { status: statusFilter } : {}),
      per_page: 100,
      ...params,
    });
  };

  const handleFilter = (e) => {
    e.preventDefault();
    loadHistory();
  };

  const kpis = useMemo(() => {
    if (!items?.length) return { present: 0, absent: 0, late: 0, total: 0, avg: 0 };
    const present = items.filter(r => r.status?.toLowerCase() === 'present').length;
    const late = items.filter(r => r.status?.toLowerCase() === 'late').length;
    const absent = items.filter(r => ['absent', 'leave'].includes(r.status?.toLowerCase())).length;
    return {
      total: items.length,
      present,
      late,
      absent,
      avg: items.length ? Math.round(((present + late) / items.length) * 100) : 0,
    };
  }, [items]);

  const formatTime = (dt) => {
    if (!dt) return '--';
    return new Date(dt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  };

  const formatDate = (dt) => {
    if (!dt) return '--';
    return new Date(dt).toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' });
  };

  const getStatusBadge = (status) => {
    const s = status?.toLowerCase() || '';
    if (s === 'present') return 'bg-green-100 text-green-800';
    if (s === 'late') return 'bg-orange-100 text-orange-800';
    if (s === 'absent') return 'bg-red-100 text-red-800';
    if (s === 'half day' || s === 'half-day') return 'bg-yellow-100 text-yellow-800';
    if (s === 'leave') return 'bg-blue-100 text-blue-800';
    return 'bg-gray-100 text-gray-800';
  };

  return (
    <div className="w-full space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-extrabold text-gray-900">My Attendance History</h1>
          <p className="text-sm text-gray-500 mt-1">View your attendance records with GPS verification details</p>
        </div>
      </div>

      <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div className="bg-white border border-gray-200 rounded-xl p-4">
          <p className="text-xs text-gray-500 font-semibold uppercase">Total</p>
          <p className="text-2xl font-extrabold text-gray-900 mt-1">{kpis.total}</p>
        </div>
        <div className="bg-white border border-green-200 rounded-xl p-4">
          <p className="text-xs text-green-600 font-semibold uppercase">Present</p>
          <p className="text-2xl font-extrabold text-green-700 mt-1">{kpis.present}</p>
        </div>
        <div className="bg-white border border-orange-200 rounded-xl p-4">
          <p className="text-xs text-orange-600 font-semibold uppercase">Late</p>
          <p className="text-2xl font-extrabold text-orange-700 mt-1">{kpis.late}</p>
        </div>
        <div className="bg-white border border-red-200 rounded-xl p-4">
          <p className="text-xs text-red-600 font-semibold uppercase">Absent/Leave</p>
          <p className="text-2xl font-extrabold text-red-700 mt-1">{kpis.absent}</p>
        </div>
        <div className="bg-white border border-blue-200 rounded-xl p-4">
          <p className="text-xs text-blue-600 font-semibold uppercase">Avg Attendance</p>
          <p className="text-2xl font-extrabold text-blue-700 mt-1">{kpis.avg}%</p>
        </div>
      </div>

      <div className="bg-white border border-gray-200 rounded-xl p-4">
        <form onSubmit={handleFilter} className="flex flex-wrap items-end gap-3">
          <div>
            <label className="block text-xs font-semibold text-gray-500 mb-1">From</label>
            <input
              type="date"
              value={dateRange.start_date}
              onChange={(e) => setDateRange(prev => ({ ...prev, start_date: e.target.value }))}
              className="px-3 py-2 border border-gray-300 rounded-lg text-sm"
            />
          </div>
          <div>
            <label className="block text-xs font-semibold text-gray-500 mb-1">To</label>
            <input
              type="date"
              value={dateRange.end_date}
              onChange={(e) => setDateRange(prev => ({ ...prev, end_date: e.target.value }))}
              className="px-3 py-2 border border-gray-300 rounded-lg text-sm"
            />
          </div>
          <div>
            <label className="block text-xs font-semibold text-gray-500 mb-1">Status</label>
            <select
              value={statusFilter}
              onChange={(e) => setStatusFilter(e.target.value)}
              className="px-3 py-2 border border-gray-300 rounded-lg text-sm"
            >
              <option value="">All</option>
              <option value="Present">Present</option>
              <option value="Late">Late</option>
              <option value="Absent">Absent</option>
              <option value="Half Day">Half Day</option>
              <option value="Leave">Leave</option>
            </select>
          </div>
          <button
            type="submit"
            className="px-4 py-2 bg-gray-900 text-white text-sm font-bold rounded-lg hover:bg-gray-800 transition-colors flex items-center gap-2"
          >
            <Filter className="w-4 h-4" />
            Apply
          </button>
        </form>
      </div>

      <div className="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left">
            <thead>
              <tr className="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                <th className="p-4 pl-6">Date</th>
                <th className="p-4">Check In</th>
                <th className="p-4">Check Out</th>
                <th className="p-4">Hours</th>
                <th className="p-4">Status</th>
                <th className="p-4">Distance</th>
                <th className="p-4">Location</th>
                <th className="p-4 pr-6">Device</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {loading ? (
                <tr><td colSpan="8" className="p-8 text-center text-sm text-gray-500">Loading records...</td></tr>
              ) : !items?.length ? (
                <tr><td colSpan="8" className="p-8 text-center text-sm text-gray-500">No attendance records found.</td></tr>
              ) : items.map((record) => (
                <tr key={record.id} className="hover:bg-gray-50 transition-colors">
                  <td className="p-4 pl-6 font-semibold text-sm text-gray-900">{formatDate(record.date)}</td>
                  <td className="p-4 text-sm font-semibold text-gray-700">{formatTime(record.check_in)}</td>
                  <td className="p-4 text-sm font-semibold text-gray-700">{formatTime(record.check_out)}</td>
                  <td className="p-4 text-sm font-semibold text-gray-700">
                    {record.working_hours ? `${record.working_hours}h` : '--'}
                  </td>
                  <td className="p-4">
                    <span className={`inline-block px-2.5 py-1 rounded text-xs font-bold ${getStatusBadge(record.status)}`}>
                      {record.status || 'Unknown'}
                    </span>
                  </td>
                  <td className="p-4 text-sm text-gray-600">
                    {record.checkin_distance ? (
                      <span className="flex items-center gap-1">
                        <MapPin className="w-3 h-3" />
                        {record.checkin_distance}m
                      </span>
                    ) : '--'}
                  </td>
                  <td className="p-4">
                    {record.location_verified ? (
                      <span className="text-xs font-bold text-green-600 flex items-center gap-1">
                        <span className="w-1.5 h-1.5 rounded-full bg-green-500" />
                        Verified
                      </span>
                    ) : record.checkin_latitude ? (
                      <span className="text-xs font-bold text-orange-600 flex items-center gap-1">
                        <span className="w-1.5 h-1.5 rounded-full bg-orange-500" />
                        Not Verified
                      </span>
                    ) : (
                      <span className="text-xs text-gray-400">N/A</span>
                    )}
                  </td>
                  <td className="p-4 pr-6 text-xs text-gray-500 max-w-[120px] truncate">
                    {record.device_info || record.browser_info || '--'}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
