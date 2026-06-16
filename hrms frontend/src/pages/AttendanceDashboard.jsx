import React, { useState } from 'react';
import { 
  Users, CheckCircle, AlertTriangle, Clock, 
  Calendar, Filter, Download, ChevronRight, SlidersHorizontal 
} from 'lucide-react';

// Mock data for heatmap
const generateHeatmapDays = () => {
  const days = [];
  for (let i = 1; i <= 31; i++) {
    let status = 'present'; // default
    if ([6, 7, 13, 14, 20, 21, 27, 28].includes(i)) status = 'weekend'; // light gray
    else if ([12, 24].includes(i)) status = 'absent'; // red/orange
    else if ([5, 18].includes(i)) status = 'leave'; // light blue
    
    days.push({ day: i, status });
  }
  return days;
};

const dailyLogs = [
  {
    id: 1,
    name: 'James Dunn',
    role: 'Lead Electrician',
    checkIn: '08:55 AM',
    checkOut: '06:30 PM',
    hours: '9h 35m',
    status: 'Present',
    statusBg: 'bg-[#C6F6D5]',
    statusText: 'text-[#22543D]',
    img: '/static/images/avatar/1.jpg',
  },
  {
    id: 2,
    name: 'Michael Kalu',
    role: 'Site Engineer',
    checkIn: '09:15 AM',
    checkOut: '--',
    hours: '--',
    status: 'Late',
    statusBg: 'bg-[#FEEBC8]',
    statusText: 'text-[#DD6B20]',
    img: '/static/images/avatar/2.jpg',
  },
  {
    id: 3,
    name: 'Sarah Thompson',
    role: 'Safety Officer',
    checkIn: '--',
    checkOut: '--',
    hours: '--',
    status: 'Absent',
    statusBg: 'bg-[#FED7D7]',
    statusText: 'text-[#C53030]',
    img: '/static/images/avatar/3.jpg',
  }
];

export default function AttendanceDashboard() {
  const days = generateHeatmapDays();

  return (
    <div className="w-full">
      {/* Header */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
          <h1 className="text-3xl font-extrabold text-[#0B1B36] mb-1">
            Attendance Dashboard
          </h1>
          <p className="text-[#718096]">
            Real-time tracking for Oct 24, 2023
          </p>
        </div>
        <div className="flex gap-3">
          <div className="flex bg-[#EDF2F7] rounded-lg p-1">
            <button className="px-4 py-1.5 bg-white shadow-sm rounded text-sm font-semibold text-[#1A202C]">Day</button>
            <button className="px-4 py-1.5 text-sm font-semibold text-[#718096]">Month</button>
            <button className="px-4 py-1.5 text-sm font-semibold text-[#718096]">Year</button>
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
          <div className="text-3xl font-extrabold text-[#0B1B36] mb-1">94.2%</div>
          <div className="text-xs font-semibold text-[#38A169]">↑ +1.2% this week</div>
        </div>
        
        <div className="bg-white border-l-4 border-[#38A169] rounded-2xl p-6 shadow-sm">
          <div className="flex justify-between items-start mb-2">
            <span className="text-[10px] font-bold text-[#718096] uppercase tracking-wider">Present Today</span>
            <CheckCircle className="w-4 h-4 text-[#38A169]" />
          </div>
          <div className="text-3xl font-extrabold text-[#0B1B36] mb-1">28</div>
          <div className="text-xs font-semibold text-[#718096]">Out of 34 total</div>
        </div>

        <div className="bg-white border-l-4 border-[#E53E3E] rounded-2xl p-6 shadow-sm">
          <div className="flex justify-between items-start mb-2">
            <span className="text-[10px] font-bold text-[#718096] uppercase tracking-wider">Absent / Leave</span>
            <AlertTriangle className="w-4 h-4 text-[#E53E3E]" />
          </div>
          <div className="text-3xl font-extrabold text-[#0B1B36] mb-1">4 / 2</div>
          <div className="text-xs font-semibold text-[#E53E3E]">Requires attention</div>
        </div>

        <div className="bg-white border-l-4 border-[#DD6B20] rounded-2xl p-6 shadow-sm">
          <div className="flex justify-between items-start mb-2">
            <span className="text-[10px] font-bold text-[#718096] uppercase tracking-wider">Late Arrivals</span>
            <Clock className="w-4 h-4 text-[#DD6B20]" />
          </div>
          <div className="text-3xl font-extrabold text-[#0B1B36] mb-1">12 / 12</div>
          <div className="text-xs font-semibold text-[#718096]">Average delay: 14m</div>
        </div>
      </div>

      <div className="grid grid-cols-1 xl:grid-cols-4 gap-8 mb-8">
        {/* Heatmap Area */}
        <div className="xl:col-span-3 bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
          <div className="flex justify-between items-center mb-6">
            <div>
              <h2 className="text-lg font-bold text-[#0B1B36]">Monthly Attendance Heatmap</h2>
              <p className="text-sm text-[#718096]">Overview of entire workforce for October 2023</p>
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
              let bgClass = 'bg-[#2D3748]'; // present (dark blue)
              if (d.status === 'weekend') bgClass = 'bg-[#EDF2F7] text-[#A0AEC0] border border-dashed border-[#CBD5E0]';
              if (d.status === 'absent') bgClass = 'bg-[#E53E3E] text-white';
              if (d.status === 'leave') bgClass = 'bg-[#3182CE] text-white';

              return (
                <div key={i} className={`${bgClass} rounded flex items-center justify-center font-bold text-sm ${d.status === 'present' ? 'text-white hover:bg-[#1A202C]' : ''} transition-colors cursor-pointer`}>
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
          
          <div className="space-y-5">
            <div>
              <label className="block text-xs font-bold text-[#718096] mb-1.5">Date Range</label>
              <div className="relative">
                <Calendar className="w-4 h-4 absolute left-3 top-2.5 text-[#A0AEC0]" />
                <input type="text" value="Oct 01 - Oct 31, 2023" readOnly className="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm font-semibold bg-white text-[#1A202C]" />
              </div>
            </div>

            <div>
              <label className="block text-xs font-bold text-[#718096] mb-1.5">Department</label>
              <select className="w-full p-2 border border-gray-300 rounded-lg text-sm font-semibold bg-white text-[#1A202C]">
                <option>All Departments</option>
                <option>Site Operations</option>
                <option>Engineering</option>
              </select>
            </div>

            <div>
              <label className="block text-xs font-bold text-[#718096] mb-1.5">Project Site</label>
              <select className="w-full p-2 border border-gray-300 rounded-lg text-sm font-semibold bg-white text-[#1A202C]">
                <option>Project Alpha, Site B</option>
                <option>Metro Line 4</option>
              </select>
            </div>

            <div>
              <label className="block text-xs font-bold text-[#718096] mb-1.5">Shift</label>
              <div className="flex gap-2">
                <button className="flex-1 py-1.5 bg-[#0B1B36] text-white text-xs font-bold rounded">Day</button>
                <button className="flex-1 py-1.5 bg-white border border-gray-300 text-[#4A5568] text-xs font-bold rounded">Night</button>
              </div>
            </div>

            <button className="w-full py-2.5 mt-2 bg-[#0B1B36] text-white font-bold rounded-lg hover:bg-[#081428] transition-colors">
              Apply Filters
            </button>
          </div>
        </div>
      </div>

      {/* Daily Logs Table */}
      <div className="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div className="p-6 border-b border-gray-200 flex justify-between items-center">
          <div>
            <h2 className="text-lg font-bold text-[#0B1B36]">Daily Attendance Log</h2>
            <p className="text-sm text-[#718096]">Detailed check-in records for Oct 24, 2023</p>
          </div>
          <button className="flex items-center text-sm font-semibold text-[#4A5568] bg-white border border-gray-300 px-3 py-1.5 rounded-lg">
            <SlidersHorizontal className="w-4 h-4 mr-2" /> View All
          </button>
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
              {dailyLogs.map(log => (
                <tr key={log.id} className="hover:bg-gray-50 transition-colors">
                  <td className="p-4 pl-6">
                    <div className="flex items-center gap-3">
                      <div className="w-8 h-8 rounded-full bg-[#EDF2F7] flex items-center justify-center font-bold text-xs text-[#1A202C]">
                        {log.name.charAt(0)}
                      </div>
                      <div>
                        <div className="font-bold text-[#1A202C] text-sm">{log.name}</div>
                        <div className="text-xs text-[#718096]">{log.role}</div>
                      </div>
                    </div>
                  </td>
                  <td className="p-4 text-sm font-bold text-[#1A202C]">{log.checkIn}</td>
                  <td className="p-4 text-sm font-bold text-[#1A202C]">{log.checkOut}</td>
                  <td className="p-4 text-sm font-bold text-[#1A202C]">{log.hours}</td>
                  <td className="p-4 text-right pr-6">
                    <span className={`inline-block px-3 py-1 rounded text-xs font-bold ${log.statusBg} ${log.statusText}`}>
                      {log.status}
                    </span>
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
