import React, { useState, useEffect, useMemo } from 'react';
import { 
  Users, CheckCircle, AlertTriangle, Clock, 
  Calendar, Filter, Download, SlidersHorizontal 
} from 'lucide-react';
import { useAttendanceStore } from '../../store/attendanceStore';
import { useDepartmentStore } from '../../store/departmentStore';
import { useSiteStore } from '../../store/siteStore';
import { useShiftsStore } from '../../store/shiftStore';

export default function Attendance() {
  const { logs, report, loading, fetchLogs } = useAttendanceStore();
  
  // Reference stores for filters
  const { items: departments, fetchItems: fetchDepartments } = useDepartmentStore();
  const { items: sites, fetchItems: fetchSites } = useSiteStore();
  const { items: shifts, fetchItems: fetchShifts } = useShiftsStore();

  const [currentDate, setCurrentDate] = useState(new Date());
  
  // Filter States
  const [departmentId, setDepartmentId] = useState('');
  const [siteId, setSiteId] = useState('');
  const [shiftId, setShiftId] = useState('');

  // Initial load for dropdown data
  useEffect(() => {
    fetchDepartments({ per_page: 100 });
    fetchSites({ status: 'Active', per_page: 100 });
    fetchShifts({ per_page: 100 });
  }, [fetchDepartments, fetchSites, fetchShifts]);

  // Fetch Attendance logs for the selected month and filters
  const applyFilters = () => {
    const year = currentDate.getFullYear();
    const month = String(currentDate.getMonth() + 1).padStart(2, '0');
    // Start of month to End of month
    const start_date = `${year}-${month}-01`;
    const end_date = new Date(year, currentDate.getMonth() + 1, 0).toISOString().split('T')[0];
    
    fetchLogs({ 
      start_date, 
      end_date, 
      department_id: departmentId || undefined,
      site_id: siteId || undefined,
      shift_id: shiftId || undefined
    });
  };

  useEffect(() => {
    applyFilters();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [currentDate]);

  const handleFilterSubmit = (e) => {
    e.preventDefault();
    applyFilters();
  };

  const selectedDateString = `${currentDate.getFullYear()}-${String(currentDate.getMonth() + 1).padStart(2, '0')}-${String(currentDate.getDate()).padStart(2, '0')}`;

  // Filter logs for the currently selected DAY for the table and KPIs
  const todaysRecords = useMemo(() => {
    return (report?.attendance_records || []).filter(r => r.date === selectedDateString);
  }, [report, selectedDateString]);

  const kpis = useMemo(() => {
    const records = todaysRecords;
    if (records.length === 0) return { present: 0, absent: 0, late: 0, total: 0, avg: 0 };
    
    const present = records.filter(r => r.status.toLowerCase() === 'present').length;
    const late = records.filter(r => r.status.toLowerCase() === 'late').length;
    const absent = records.filter(r => ['absent', 'leave', 'half day'].includes(r.status.toLowerCase())).length;
    const total = records.length;
    
    return {
      total,
      present,
      late,
      absent,
      avg: Math.round(((present + late) / total) * 100)
    };
  }, [todaysRecords]);

  const days = useMemo(() => {
    const records = report?.attendance_records || [];
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const now = new Date();
    
    const daysArray = [];
    
    for (let i = 1; i <= daysInMonth; i++) {
      const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
      const recordsForDay = records.filter(r => r.date === dateStr);
      const d = new Date(dateStr);
      let status = 'weekend';
      
      // Assume Sunday (0) is a default weekend. Can be configured later based on shifts.
      if (d.getDay() !== 0) {
        if (recordsForDay.length === 0) {
           status = d > now ? 'off' : 'weekend'; // No records and not future => weekend or off
        } else {
           const absents = recordsForDay.filter(r => ['absent', 'leave'].includes(r.status.toLowerCase())).length;
           status = absents > 0 ? 'absent' : 'present';
        }
      }
      daysArray.push({ 
        day: i, 
        status, 
        fullDate: d,
        isSelected: dateStr === selectedDateString 
      });
    }
    return daysArray;
  }, [report, currentDate, selectedDateString]);

  const formatDate = (dateString) => {
    if (!dateString) return '--';
    return new Date(dateString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  };

  const calculateHours = (inStr, outStr) => {
    if (!inStr || !outStr) return '--';
    const diff = new Date(outStr) - new Date(inStr);
    const hrs = Math.floor(diff / 3600000);
    const mins = Math.floor((diff % 3600000) / 60000);
    return `${hrs}h ${mins}m`;
  };

  return (
    <div className="w-full">
      {/* Header */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
          <h1 className="text-3xl font-extrabold text-[#0B1B36] mb-1">
            Attendance Dashboard
          </h1>
          <p className="text-[#718096]">
            Real-time tracking for {currentDate.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })}
          </p>
        </div>
        <div className="flex gap-3">
          <div className="flex bg-[#EDF2F7] rounded-lg p-1">
            <button 
              onClick={() => setCurrentDate(new Date())} 
              className="px-4 py-1.5 bg-white shadow-sm rounded text-sm font-semibold text-[#1A202C]"
            >
              Today
            </button>
          </div>
          <button className="flex items-center justify-center px-3 py-2 bg-white border border-[#CBD5E0] text-[#4A5568] rounded-xl hover:bg-gray-50 transition-colors">
            <Download className="w-4 h-4" />
          </button>
        </div>
      </div>

      {/* KPI Cards */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div className="bg-white border-l-4 border-[#0B1B36] rounded-2xl p-6 shadow-sm">
          <div className="flex justify-between items-start mb-2">
            <span className="text-[10px] font-bold text-[#718096] uppercase tracking-wider">Average Attendance</span>
            <Users className="w-4 h-4 text-[#A0AEC0]" />
          </div>
          <div className="text-3xl font-extrabold text-[#0B1B36] mb-1">{kpis.avg}%</div>
          <div className="text-xs font-semibold text-[#38A169]">Based on selected day</div>
        </div>
        
        <div className="bg-white border-l-4 border-[#38A169] rounded-2xl p-6 shadow-sm">
          <div className="flex justify-between items-start mb-2">
            <span className="text-[10px] font-bold text-[#718096] uppercase tracking-wider">Present</span>
            <CheckCircle className="w-4 h-4 text-[#38A169]" />
          </div>
          <div className="text-3xl font-extrabold text-[#0B1B36] mb-1">{kpis.present + kpis.late}</div>
          <div className="text-xs font-semibold text-[#718096]">Out of {kpis.total} total</div>
        </div>

        <div className="bg-white border-l-4 border-[#E53E3E] rounded-2xl p-6 shadow-sm">
          <div className="flex justify-between items-start mb-2">
            <span className="text-[10px] font-bold text-[#718096] uppercase tracking-wider">Absent / Leave</span>
            <AlertTriangle className="w-4 h-4 text-[#E53E3E]" />
          </div>
          <div className="text-3xl font-extrabold text-[#0B1B36] mb-1">{kpis.absent}</div>
          <div className="text-xs font-semibold text-[#E53E3E]">Requires attention</div>
        </div>

        <div className="bg-white border-l-4 border-[#DD6B20] rounded-2xl p-6 shadow-sm">
          <div className="flex justify-between items-start mb-2">
            <span className="text-[10px] font-bold text-[#718096] uppercase tracking-wider">Late Arrivals</span>
            <Clock className="w-4 h-4 text-[#DD6B20]" />
          </div>
          <div className="text-3xl font-extrabold text-[#0B1B36] mb-1">{kpis.late}</div>
          <div className="text-xs font-semibold text-[#718096]">Recorded</div>
        </div>
      </div>

      <div className="grid grid-cols-1 xl:grid-cols-4 gap-8 mb-8">
        {/* Heatmap Area */}
        <div className="xl:col-span-3 bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
          <div className="flex justify-between items-center mb-6">
            <div>
              <h2 className="text-lg font-bold text-[#0B1B36]">Monthly Attendance Heatmap</h2>
              <p className="text-sm text-[#718096]">{currentDate.toLocaleString('default', { month: 'long', year: 'numeric' })} Overview</p>
            </div>
            <div className="flex items-center gap-3 text-xs font-semibold">
              <div className="flex items-center"><div className="w-3 h-3 bg-[#2D3748] rounded mr-1.5"/> Present</div>
              <div className="flex items-center"><div className="w-3 h-3 bg-[#E53E3E] rounded mr-1.5"/> Absent</div>
              <div className="flex items-center"><div className="w-3 h-3 bg-[#3182CE] rounded mr-1.5"/> Leave</div>
              <div className="flex items-center"><div className="w-3 h-3 bg-[#EDF2F7] rounded mr-1.5"/> Off</div>
            </div>
          </div>

          <div className="grid grid-cols-7 gap-2 h-80">
            {days.map((d, i) => {
              let bgClass = 'bg-[#2D3748] text-white'; // present (dark blue)
              if (d.status === 'weekend') bgClass = 'bg-[#EDF2F7] text-[#A0AEC0] border border-dashed border-[#CBD5E0]';
              if (d.status === 'absent') bgClass = 'bg-[#E53E3E] text-white';
              if (d.status === 'leave') bgClass = 'bg-[#3182CE] text-white';
              if (d.status === 'off') bgClass = 'bg-white border border-gray-200 text-gray-400';

              const selectedRing = d.isSelected ? 'ring-2 ring-offset-2 ring-blue-500' : '';

              return (
                <div 
                  key={i} 
                  onClick={() => setCurrentDate(d.fullDate)}
                  className={`${bgClass} ${selectedRing} rounded flex items-center justify-center font-bold text-sm hover:opacity-80 transition-opacity cursor-pointer`}
                  title={`View records for ${d.fullDate.toLocaleDateString()}`}
                >
                  {d.day}
                </div>
              );
            })}
          </div>
        </div>

        {/* Filters Sidebar */}
        <div className="bg-[#F7FAFC] border border-gray-200 rounded-2xl p-6 shadow-sm">
          <h2 className="text-lg font-bold text-[#0B1B36] mb-6 flex items-center">
            <Filter className="w-5 h-5 mr-2 text-[#718096]" />
            Attendance Filters
          </h2>
          
          <form onSubmit={handleFilterSubmit} className="space-y-5">
            <div>
              <label className="block text-xs font-bold text-[#718096] mb-1.5">View Month For</label>
              <div className="relative">
                <Calendar className="w-4 h-4 absolute left-3 top-2.5 text-[#A0AEC0]" />
                <input 
                  type="date" 
                  value={selectedDateString} 
                  onChange={(e) => {
                    if (e.target.value) setCurrentDate(new Date(e.target.value));
                  }}
                  className="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm font-semibold bg-white text-[#1A202C]" 
                />
              </div>
            </div>

            <div>
              <label className="block text-xs font-bold text-[#718096] mb-1.5">Department</label>
              <select 
                value={departmentId}
                onChange={(e) => setDepartmentId(e.target.value)}
                className="w-full p-2 border border-gray-300 rounded-lg text-sm font-semibold bg-white text-[#1A202C]"
              >
                <option value="">All Departments</option>
                {departments.map(dept => (
                  <option key={dept.id} value={dept.id}>{dept.name}</option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-xs font-bold text-[#718096] mb-1.5">Project Site</label>
              <select 
                value={siteId}
                onChange={(e) => setSiteId(e.target.value)}
                className="w-full p-2 border border-gray-300 rounded-lg text-sm font-semibold bg-white text-[#1A202C]"
              >
                <option value="">All Sites</option>
                {sites.map(site => (
                  <option key={site.id} value={site.id}>{site.name} ({site.code})</option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-xs font-bold text-[#718096] mb-1.5">Shift</label>
              <select 
                value={shiftId}
                onChange={(e) => setShiftId(e.target.value)}
                className="w-full p-2 border border-gray-300 rounded-lg text-sm font-semibold bg-white text-[#1A202C]"
              >
                <option value="">All Shifts</option>
                {shifts.map(shift => (
                  <option key={shift.id} value={shift.id}>{shift.name} ({shift.start_time} - {shift.end_time})</option>
                ))}
              </select>
            </div>

            <button type="submit" className="w-full py-2.5 mt-2 bg-[#0B1B36] text-white font-bold rounded-lg hover:bg-[#081428] transition-colors flex items-center justify-center">
              {loading ? 'Filtering...' : 'Apply Filters'}
            </button>
          </form>
        </div>
      </div>

      {/* Daily Logs Table */}
      <div className="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div className="p-6 border-b border-gray-200 flex justify-between items-center">
          <div>
            <h2 className="text-lg font-bold text-[#0B1B36]">Daily Attendance Log</h2>
            <p className="text-sm text-[#718096]">Detailed check-in records for {currentDate.toLocaleDateString()}</p>
          </div>
        </div>
        
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="bg-[#F7FAFC] border-b border-gray-200 text-[10px] font-bold text-[#A0AEC0] uppercase tracking-widest">
                <th className="p-4 pl-6">Employee</th>
                <th className="p-4">Check In</th>
                <th className="p-4">Check Out</th>
                <th className="p-4">Total Hours</th>
                <th className="p-4 text-right pr-6">Status</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {loading ? (
                <tr><td colSpan="5" className="p-4 text-center text-sm text-gray-500 py-8">Loading records...</td></tr>
              ) : todaysRecords.length === 0 ? (
                <tr><td colSpan="5" className="p-4 text-center text-sm text-gray-500 py-8">No attendance records found for this date.</td></tr>
              ) : todaysRecords.map(log => {
                const name = log.employee?.full_name || 'Unknown';
                const role = log.employee?.designation?.name || 'Employee';
                const checkIn = formatDate(log.check_in);
                const checkOut = formatDate(log.check_out);
                const hours = calculateHours(log.check_in, log.check_out);
                
                let statusBg = 'bg-[#EDF2F7]';
                let statusText = 'text-[#4A5568]';
                if (log.status.toLowerCase() === 'present') {
                  statusBg = 'bg-[#C6F6D5]';
                  statusText = 'text-[#22543D]';
                } else if (log.status.toLowerCase() === 'late') {
                  statusBg = 'bg-[#FEEBC8]';
                  statusText = 'text-[#DD6B20]';
                } else if (['absent', 'leave'].includes(log.status.toLowerCase())) {
                  statusBg = 'bg-[#FED7D7]';
                  statusText = 'text-[#C53030]';
                }

                return (
                <tr key={log.id} className="hover:bg-gray-50 transition-colors">
                  <td className="p-4 pl-6">
                    <div className="flex items-center gap-3">
                      <div className="w-8 h-8 rounded-full bg-[#EDF2F7] flex items-center justify-center font-bold text-xs text-[#1A202C]">
                        {name.charAt(0)}
                      </div>
                      <div>
                        <div className="font-bold text-[#1A202C] text-sm">{name}</div>
                        <div className="text-xs text-[#718096]">{role}</div>
                      </div>
                    </div>
                  </td>
                  <td className="p-4 text-sm font-bold text-[#1A202C]">{checkIn}</td>
                  <td className="p-4 text-sm font-bold text-[#1A202C]">{checkOut}</td>
                  <td className="p-4 text-sm font-bold text-[#1A202C]">{hours}</td>
                  <td className="p-4 text-right pr-6">
                    <span className={`inline-block px-3 py-1 rounded text-xs font-bold capitalize ${statusBg} ${statusText}`}>
                      {log.status || 'unknown'}
                    </span>
                  </td>
                </tr>
              )})}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
