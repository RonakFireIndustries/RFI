import React from 'react';
import {
  BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer,
  PieChart, Pie, Cell
} from 'recharts';
import {
  Users, CheckCircle2, XCircle, CalendarDays, Building,
  Download, Calendar, MoreHorizontal, DollarSign
} from 'lucide-react';



export default function Dashboard() {
  const [stats, setStats] = React.useState(null);

  React.useEffect(() => {
    const token = localStorage.getItem("token");

    if (!token) {
      window.location.href = "/login";
      return;
    }

    fetch("http://localhost:8000/api/dashboard", {
      headers: { Authorization: `Bearer ${token}` }
    })
      .then(res => res.json())
      .then(data => {
        console.log("API Data:", data);
        if (data.status === "success" && data.data) {
          setStats(data.data);
        }
      })
      .catch(err => console.error("Error fetching dashboard:", err));
  }, []);

  if (!stats) {
    return <div className="p-8 text-center">Loading dashboard...</div>;
  }

  // Destructure stats for convenience
  const {
    total_employees,
    present_employees,
    absent_employees,
    on_leave_employees,
    active_sites,
    attendance_data,
    department_data,
    upcoming_birthdays
  } = stats;

  return (
    <div className="w-full">
      {/* Header */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
          <h1 className="text-3xl font-extrabold text-[#0B1B36] mb-1">
            Executive Overview
          </h1>
          <p className="text-[#718096]">
            Real-time construction workforce performance and logistics.
          </p>
        </div>
        <div className="flex gap-3">
          <button className="flex items-center px-4 py-2 bg-[#F8FAFC] border border-[#CBD5E0] text-[#4A5568] rounded-xl font-medium hover:bg-gray-100 transition-colors">
            <Calendar className="w-4 h-4 mr-2" />
            Jun 15, 2026
          </button>
          <button className="flex items-center px-4 py-2 bg-[#0B1B36] text-white rounded-xl font-medium hover:bg-[#081428] transition-colors shadow-sm">
            <Download className="w-4 h-4 mr-2" />
            Export Report
          </button>
        </div>
      </div>

      {/* KPI Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        {/* Total Employees */}
        <div className="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm flex flex-col">
          <div className="flex justify-between items-start mb-3">
            <div className="p-2 bg-[#EDF2F7] rounded-lg text-[#4A5568]">
              <Users className="w-5 h-5" />
            </div>
            <span className="text-xs font-bold text-[#38A169] flex items-center">
              ↗ +3%
            </span>
          </div>
          <span className="text-xs font-semibold text-[#718096] mb-1">Total Employees</span>
          <span className="text-3xl font-bold text-[#0B1B36]">{total_employees}</span>
        </div>

        {/* Present Today */}
        <div className="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm flex flex-col">
          <div className="flex justify-between items-start mb-3">
            <div className="p-2 bg-[#EBF8FF] rounded-lg text-[#3182CE]">
              <CheckCircle2 className="w-5 h-5" />
            </div>
            <span className="text-xs font-bold text-[#1A202C]">79.4%</span>
          </div>
          <span className="text-xs font-semibold text-[#718096] mb-1">Present Today</span>
          <span className="text-3xl font-bold text-[#0B1B36]">{present_employees}</span>
        </div>

        {/* Absent */}
        <div className="bg-white border border-red-200 rounded-2xl p-5 shadow-sm flex flex-col">
          <div className="flex justify-between items-start mb-3">
            <div className="p-2 bg-[#FFF5F5] rounded-lg text-[#E53E3E]">
              <XCircle className="w-5 h-5" />
            </div>
            <span className="text-xs font-bold text-[#E53E3E]">Critical</span>
          </div>
          <span className="text-xs font-semibold text-[#718096] mb-1">Absent</span>
          <span className="text-3xl font-bold text-[#E53E3E]">{absent_employees}</span>
        </div>

        {/* On Leave */}
        <div className="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm flex flex-col">
          <div className="flex justify-between items-start mb-3">
            <div className="p-2 bg-[#FFFAF0] rounded-lg text-[#DD6B20]">
              <CalendarDays className="w-5 h-5" />
            </div>
            <span className="text-xs font-bold text-[#718096]">Scheduled</span>
          </div>
          <span className="text-xs font-semibold text-[#718096] mb-1">On Leave</span>
          <span className="text-3xl font-bold text-[#0B1B36]">{on_leave_employees}</span>
        </div>

        {/* Active Sites */}
        <div className="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm flex flex-col">
          <div className="flex justify-between items-start mb-3">
            <div className="p-2 bg-[#E6FFFA] rounded-lg text-[#319795]">
              <Building className="w-5 h-5" />
            </div>
            <span className="text-xs font-bold text-[#718096]">On track</span>
          </div>
          <span className="text-xs font-semibold text-[#718096] mb-1">Active Sites</span>
          <span className="text-3xl font-bold text-[#0B1B36]">{active_sites}</span>
        </div>
      </div>

      {/* Middle Section */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div className="lg:col-span-2 bg-white border border-gray-200 rounded-2xl p-6 shadow-sm h-96 flex flex-col">
          <div className="flex justify-between items-center mb-6">
            <h2 className="text-lg font-bold text-[#0B1B36]">Attendance Trends</h2>
            <button className="bg-[#EDF2F7] px-3 py-1 rounded-full text-xs font-semibold text-[#4A5568]">
              Last 30 Days ▾
            </button>
          </div>
          <div className="flex-grow w-full">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={attendance_data} barSize={40}>
                <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#E2E8F0" />
                <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fill: '#718096', fontSize: 12 }} dy={10} />
                <YAxis hide />
                <Tooltip cursor={{ fill: '#F7FAFC' }} contentStyle={{ borderRadius: '8px', border: 'none', boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)' }} />
                <Bar dataKey="present" fill="#CBD5E0" radius={[6, 6, 0, 0]} />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>

        <div className="bg-[#0B1B36] rounded-2xl p-6 text-white shadow-sm h-96 flex flex-col justify-between">
          <div>
            <div className="flex justify-between items-center mb-1">
              <h2 className="text-lg font-semibold">Payroll Summary</h2>
              <MoreHorizontal className="text-gray-400" />
            </div>
            <p className="text-xs text-[#A0AEC0] mb-6">Month-to-Date Expenditure</p>
            <h3 className="text-5xl font-extrabold tracking-tight">$428.5k</h3>
            <div className="flex items-center text-[#48BB78] mt-3 text-sm font-medium">
              <CheckCircle2 className="w-4 h-4 mr-1.5" />
              On budget for October
            </div>
          </div>
          <div className="flex gap-3">
            <button className="flex-1 bg-white text-[#0B1B36] py-2.5 rounded-xl font-semibold hover:bg-gray-100 transition-colors">
              Pay All
            </button>
            <button className="flex-1 bg-transparent border border-gray-500 text-white py-2.5 rounded-xl font-medium hover:bg-white/10 transition-colors">
              Details
            </button>
          </div>
        </div>
      </div>

      {/* Bottom Section */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {/* Department Distribution */}
        <div className="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
          <h2 className="text-lg font-bold text-[#0B1B36] mb-6">Department Distribution</h2>
          <div className="relative h-48 flex justify-center items-center mb-6">
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie data={department_data} innerRadius={65} outerRadius={85} paddingAngle={2} dataKey="value" stroke="none">
                  {department_data.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={entry.color} />
                  ))}
                </Pie>
              </PieChart>
            </ResponsiveContainer>
            <div className="absolute text-center">
              <h4 className="text-2xl font-extrabold text-[#0B1B36]">1.2k</h4>
              <p className="text-xs text-[#718096] font-medium">Total</p>
            </div>
          </div>
          <div className="space-y-3">
            {department_data.map(dep => (
              <div key={dep.name} className="flex justify-between items-center">
                <div className="flex items-center gap-2">
                  <div className="w-3 h-3 rounded-full" style={{ backgroundColor: dep.color }} />
                  <span className="text-sm font-medium text-[#4A5568]">{dep.name}</span>
                </div>
                <span className="text-sm font-bold text-[#1A202C]">{dep.value}</span>
              </div>
            ))}
          </div>
        </div>



        {/* Upcoming Birthdays */}
        <div className="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
          <h2 className="text-lg font-bold text-[#0B1B36] mb-6">Upcoming Birthdays</h2>

          <div className="space-y-5 mb-6">
            {upcoming_birthdays.map((bday, i) => (
              <div key={i} className="flex items-center gap-3">
                <img src={bday.img} alt="User" className="w-10 h-10 rounded-full object-cover bg-gray-200 flex-shrink-0" />
                <div>
                  <p className="text-sm font-bold text-[#1A202C]">{bday.name} {i === 0 && '🎂'}</p>
                  <p className="text-xs text-[#718096]">{bday.role} • {bday.date}</p>
                </div>
              </div>
            ))}
          </div>

          <div className="bg-[#F7FAFC] p-4 rounded-xl">
            <h4 className="text-xs font-bold text-[#4A5568] flex items-center mb-2">
              <span className="mr-1">💡</span> ADMIN TIP
            </h4>
            <p className="text-xs text-[#4A5568] leading-relaxed">
              Team engagement is up 12% this month. Consider announcing the 'Safety First' bonus in today's blast.
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
