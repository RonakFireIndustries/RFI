import React, { useCallback, useEffect, useState } from 'react';
import {
  Calendar,
  ChevronLeft,
  ChevronRight,
  Clock,
  UserCheck,
  UserMinus,
  UserX,
} from 'lucide-react';
import { attendancesService } from '../../services/attendancesService';

const pad = (value) => String(value).padStart(2, '0');

const currentMonthValue = () => {
  const now = new Date();
  return `${now.getFullYear()}-${pad(now.getMonth() + 1)}`;
};

const formatHours = (hours) => {
  if (hours == null || hours === '') return '--';
  const totalMinutes = Math.round(Number(hours) * 60);
  const h = Math.floor(totalMinutes / 60);
  const m = totalMinutes % 60;
  return `${pad(h)}:${pad(m)}`;
};

const formatTime = (value) => {
  if (!value) return '--';
  const date = new Date(value);
  return `${pad(date.getHours())}:${pad(date.getMinutes())}`;
};

const formatDay = (value) => {
  if (!value) return '--';
  return new Date(`${value}T00:00:00`).toLocaleDateString(undefined, {
    day: '2-digit',
    month: 'short',
    weekday: 'short',
  });
};

const statusBadgeClass = (status) => {
  const s = (status || '').toLowerCase();
  if (s === 'present') return 'bg-green-100 text-green-800';
  if (s === 'late') return 'bg-orange-100 text-orange-800';
  if (s === 'absent') return 'bg-red-100 text-red-800';
  if (s.includes('half')) return 'bg-yellow-100 text-yellow-800';
  if (s.includes('leave')) return 'bg-blue-100 text-blue-800';
  if (s === 'holiday') return 'bg-purple-100 text-purple-800';
  if (s === 'weekly off') return 'bg-gray-100 text-gray-600';
  if (s === 'upcoming') return 'bg-gray-50 text-gray-400';
  return 'bg-gray-100 text-gray-700';
};

export default function MonthlyAttendanceReport() {
  const [month, setMonth] = useState(currentMonthValue);
  const [report, setReport] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const loadReport = useCallback(async (targetMonth) => {
    setLoading(true);
    setError(null);
    try {
      const response = await attendancesService.myMonthlyReport({ month: targetMonth });
      const data = response?.data?.report || response?.report || response?.data || null;
      setReport(data);
    } catch (err) {
      setError(err?.response?.data?.message || err.message || 'Failed to load the monthly report.');
      setReport(null);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    loadReport(month);
  }, [month, loadReport]);

  const shiftMonth = (delta) => {
    const [year, monthPart] = month.split('-').map(Number);
    const date = new Date(year, monthPart - 1 + delta, 1);
    setMonth(`${date.getFullYear()}-${pad(date.getMonth() + 1)}`);
  };

  const summary = report?.summary;
  const days = report?.days || [];

  const kpis = summary
    ? [
        { label: 'Present', value: summary.present, className: 'text-green-700 border-green-200', icon: UserCheck },
        { label: 'Late', value: summary.late, className: 'text-orange-700 border-orange-200', icon: Clock },
        { label: 'Absent', value: summary.absent, className: 'text-red-700 border-red-200', icon: UserX },
        { label: 'On Leave', value: summary.leave, className: 'text-blue-700 border-blue-200', icon: UserMinus },
        { label: 'Half Day', value: summary.half_day, className: 'text-yellow-700 border-yellow-200', icon: Clock },
        { label: 'Working Days', value: summary.working_days, className: 'text-gray-700 border-gray-200', icon: Calendar },
        { label: 'Hours', value: formatHours(summary.total_working_hours), className: 'text-indigo-700 border-indigo-200', icon: Clock },
        { label: 'Attendance', value: `${summary.attendance_percentage}%`, className: 'text-teal-700 border-teal-200', icon: UserCheck },
      ]
    : [];

  return (
    <div className="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 py-4 border-b border-gray-100">
        <div>
          <h2 className="text-lg font-bold text-gray-900 flex items-center gap-2">
            <Calendar className="w-5 h-5 text-[#1a56db]" />
            Monthly Attendance Report
          </h2>
          <p className="text-xs text-gray-500 mt-1">
            {report?.month_label || 'Your attendance summary and day-by-day breakdown.'}
          </p>
        </div>

        <div className="flex items-center gap-2">
          <button
            type="button"
            onClick={() => shiftMonth(-1)}
            className="p-2 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50"
            aria-label="Previous month"
          >
            <ChevronLeft className="w-4 h-4" />
          </button>
          <input
            type="month"
            value={month}
            onChange={(e) => e.target.value && setMonth(e.target.value)}
            className="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
          />
          <button
            type="button"
            onClick={() => shiftMonth(1)}
            className="p-2 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50"
            aria-label="Next month"
          >
            <ChevronRight className="w-4 h-4" />
          </button>
        </div>
      </div>

      <div className="p-5 space-y-5">
        {error && (
          <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {error}
          </div>
        )}

        {loading ? (
          <div className="py-10 text-center text-sm text-gray-500">Loading monthly report...</div>
        ) : (
          <>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
              {kpis.map((kpi) => {
                const Icon = kpi.icon;
                return (
                  <div key={kpi.label} className={`rounded-xl border bg-white p-4 ${kpi.className}`}>
                    <div className="flex items-center justify-between">
                      <p className="text-xs font-semibold uppercase tracking-wider text-gray-500">{kpi.label}</p>
                      <Icon className="w-4 h-4" />
                    </div>
                    <p className="text-2xl font-extrabold mt-1">{kpi.value ?? '--'}</p>
                  </div>
                );
              })}
            </div>

            <div className="overflow-x-auto rounded-lg border border-gray-100">
              <table className="w-full text-left">
                <thead>
                  <tr className="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                    <th className="p-3 pl-4">Date</th>
                    <th className="p-3">Status</th>
                    <th className="p-3">Check In</th>
                    <th className="p-3">Check Out</th>
                    <th className="p-3">Hours</th>
                    <th className="p-3">Overtime</th>
                    <th className="p-3 pr-4">Site</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {days.length === 0 ? (
                    <tr>
                      <td colSpan="7" className="p-8 text-center text-sm text-gray-500">
                        No attendance data for this month.
                      </td>
                    </tr>
                  ) : (
                    days.map((day) => (
                      <tr key={day.date} className="hover:bg-gray-50 transition-colors">
                        <td className="p-3 pl-4 text-sm font-semibold text-gray-900 whitespace-nowrap">
                          {formatDay(day.date)}
                        </td>
                        <td className="p-3">
                          <span className={`inline-block px-2.5 py-1 rounded text-xs font-bold ${statusBadgeClass(day.status)}`}>
                            {day.status || 'Unknown'}
                          </span>
                        </td>
                        <td className="p-3 text-sm text-gray-700">{formatTime(day.check_in)}</td>
                        <td className="p-3 text-sm text-gray-700">{formatTime(day.check_out)}</td>
                        <td className="p-3 text-sm font-semibold text-gray-700">{formatHours(day.working_hours)}</td>
                        <td className="p-3 text-sm text-gray-700">{formatHours(day.overtime_hours)}</td>
                        <td className="p-3 pr-4 text-sm text-gray-600 truncate max-w-[140px]">{day.site || '--'}</td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
          </>
        )}
      </div>
    </div>
  );
}
