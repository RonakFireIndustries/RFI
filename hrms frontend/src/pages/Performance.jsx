import React from 'react';
import { 
  AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer 
} from 'recharts';
import { 
  ShieldCheck, Target, BadgeCheck, Download, CheckCircle2, 
  CircleDashed, PlayCircle, SlidersHorizontal
} from 'lucide-react';

const performanceData = [
  { name: 'Jan', score: 85 },
  { name: 'Feb', score: 86 },
  { name: 'Mar', score: 88 },
  { name: 'Apr', score: 87 },
  { name: 'May', score: 92 },
  { name: 'Jun', score: 94 },
];

const employees = [
  {
    id: 'EMP-8821',
    name: 'Jameson Dunn',
    initials: 'JD',
    role: 'Lead Electrician',
    trade: 'Electrical',
    cycle: 'Annual 2024',
    status: 'Self-Assessment',
    statusColor: 'text-orange-600',
    dotColor: 'bg-orange-500',
  },
  {
    id: 'EMP-9045',
    name: 'Elena Rodriguez',
    img: '/static/images/avatar/2.jpg',
    role: 'Site Foreman',
    trade: 'Civil Eng',
    cycle: 'Annual 2024',
    status: 'Manager Review',
    statusColor: 'text-slate-600',
    dotColor: 'bg-slate-500',
  },
  {
    id: 'EMP-7712',
    name: 'Michael Kalu',
    initials: 'MK',
    role: 'Project Coordinator',
    trade: 'Mgmt',
    cycle: 'Annual 2024',
    status: 'Completed',
    statusColor: 'text-blue-600',
    icon: <CheckCircle2 className="w-4 h-4 text-blue-500 mr-1.5" />,
  },
  {
    id: 'EMP-8844',
    name: 'Sarah Thompson',
    initials: 'ST',
    role: 'Safety Officer',
    trade: 'HSE',
    cycle: 'Mid-Year Review',
    status: 'Manager Review',
    statusColor: 'text-slate-600',
    dotColor: 'bg-slate-500',
  },
];

export default function Performance() {
  return (
    <div className="w-full">
      {/* Header */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
          <h1 className="text-3xl font-extrabold text-[#0B1B36] mb-1">
            Performance Insights
          </h1>
          <p className="text-[#718096]">
            Annual Appraisal Period: Q3 2023 - Q2 2024
          </p>
        </div>
        <div className="flex gap-3">
          <button className="flex items-center px-4 py-2 bg-[#0B1B36] text-white rounded-xl font-medium hover:bg-[#081428] transition-colors shadow-sm">
            <CheckCircle2 className="w-4 h-4 mr-2" />
            New Appraisal
          </button>
          <button className="flex items-center px-4 py-2 bg-white border border-[#CBD5E0] text-[#4A5568] rounded-xl font-medium hover:bg-gray-50 transition-colors">
            <Download className="w-4 h-4 mr-2" />
            Export Report
          </button>
        </div>
      </div>

      {/* KPI Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        {/* Safety */}
        <div className="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
          <div className="flex justify-between items-start mb-6">
            <div className="p-2.5 bg-[#FFF5F5] rounded-xl text-[#E53E3E]">
              <ShieldCheck className="w-6 h-6" />
            </div>
            <span className="text-xs font-bold text-[#DD6B20] bg-[#FFFAF0] px-2 py-1 rounded-full">
              +4.2%
            </span>
          </div>
          <div>
            <span className="text-xs font-bold text-[#718096] uppercase tracking-wider mb-2 block">
              Safety Compliance
            </span>
            <div className="flex items-baseline gap-2">
              <span className="text-4xl font-extrabold text-[#0B1B36]">98.4</span>
              <span className="text-sm font-semibold text-[#718096]">/ 100</span>
            </div>
          </div>
          <div className="w-full h-1.5 bg-gray-100 rounded-full mt-4 overflow-hidden">
            <div className="h-full bg-orange-500 rounded-full" style={{ width: '98%' }} />
          </div>
        </div>

        {/* Productivity */}
        <div className="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
          <div className="flex justify-between items-start mb-6">
            <div className="p-2.5 bg-[#EBF8FF] rounded-xl text-[#3182CE]">
              <Target className="w-6 h-6" />
            </div>
            <span className="text-xs font-bold text-[#3182CE] bg-[#EBF8FF] px-2 py-1 rounded-full">
              -1.5%
            </span>
          </div>
          <div>
            <span className="text-xs font-bold text-[#718096] uppercase tracking-wider mb-2 block">
              Site Productivity
            </span>
            <div className="flex items-baseline gap-2">
              <span className="text-4xl font-extrabold text-[#0B1B36]">82.1</span>
              <span className="text-sm font-semibold text-[#718096]">/ 100</span>
            </div>
          </div>
          <div className="w-full h-1.5 bg-gray-100 rounded-full mt-4 overflow-hidden">
            <div className="h-full bg-[#4A5568] rounded-full" style={{ width: '82%' }} />
          </div>
        </div>

        {/* Quality */}
        <div className="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
          <div className="flex justify-between items-start mb-6">
            <div className="p-2.5 bg-[#E6FFFA] rounded-xl text-[#319795]">
              <BadgeCheck className="w-6 h-6" />
            </div>
            <span className="text-xs font-bold text-[#3182CE] bg-[#EBF8FF] px-2 py-1 rounded-full">
              +12.6%
            </span>
          </div>
          <div>
            <span className="text-xs font-bold text-[#718096] uppercase tracking-wider mb-2 block">
              Quality Assurance
            </span>
            <div className="flex items-baseline gap-2">
              <span className="text-4xl font-extrabold text-[#0B1B36]">94.7</span>
              <span className="text-sm font-semibold text-[#718096]">/ 100</span>
            </div>
          </div>
          <div className="w-full h-1.5 bg-gray-100 rounded-full mt-4 overflow-hidden">
            <div className="h-full bg-[#0B1B36] rounded-full" style={{ width: '94%' }} />
          </div>
        </div>
      </div>

      {/* Middle Section */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div className="lg:col-span-2 bg-white border border-gray-200 rounded-2xl p-6 shadow-sm h-96 flex flex-col">
          <div className="flex justify-between items-center mb-6">
            <div>
              <h2 className="text-lg font-bold text-[#0B1B36]">Performance Trends</h2>
              <p className="text-sm text-[#718096]">Average scorecard score over last 6 months</p>
            </div>
            <button className="bg-white border border-[#CBD5E0] px-3 py-1.5 rounded-lg text-xs font-semibold text-[#4A5568]">
              Last 6 Months ▾
            </button>
          </div>
          <div className="flex-grow w-full -ml-4">
            <ResponsiveContainer width="100%" height="100%">
              <AreaChart data={performanceData}>
                <defs>
                  <linearGradient id="colorScore" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="5%" stopColor="#0B1B36" stopOpacity={0.1}/>
                    <stop offset="95%" stopColor="#0B1B36" stopOpacity={0}/>
                  </linearGradient>
                </defs>
                <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#E2E8F0" />
                <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{fill: '#718096', fontSize: 12}} dy={10} />
                <YAxis hide domain={['dataMin - 10', 'dataMax + 10']} />
                <Tooltip contentStyle={{ borderRadius: '8px', border: 'none', boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)' }} />
                <Area type="monotone" dataKey="score" stroke="#0B1B36" strokeWidth={3} fillOpacity={1} fill="url(#colorScore)" />
              </AreaChart>
            </ResponsiveContainer>
          </div>
        </div>

        <div className="bg-[#1A263E] rounded-2xl p-8 text-white shadow-sm h-96 flex flex-col justify-between border border-[#2D3748]">
          <div>
            <h2 className="text-xl font-bold mb-6">Workforce Health</h2>
            <div className="space-y-4">
              <div className="flex justify-between items-center pb-4 border-b border-[#2D3748]">
                <span className="text-[#A0AEC0]">Top Performers</span>
                <span className="text-xl font-semibold">124</span>
              </div>
              <div className="flex justify-between items-center pb-4 border-b border-[#2D3748]">
                <span className="text-[#A0AEC0]">Under Review</span>
                <span className="text-xl font-semibold">18</span>
              </div>
              <div className="flex justify-between items-center pb-4">
                <span className="text-[#A0AEC0]">Average Tenure</span>
                <span className="text-xl font-semibold">4.2 yrs</span>
              </div>
            </div>
          </div>
          <div>
            <p className="text-sm text-[#718096] italic leading-relaxed">
              "Safety culture has improved by 14% since the implementation of daily tool-box talks in Q1."
            </p>
          </div>
        </div>
      </div>

      {/* Bottom Section: Table */}
      <div className="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden mb-8">
        <div className="p-6 border-b border-gray-200 flex justify-between items-center">
          <div>
            <h2 className="text-lg font-bold text-[#0B1B36]">Appraisal Review Workflow</h2>
            <p className="text-sm text-[#718096]">Manage and track active performance cycles</p>
          </div>
          <button className="text-[#4A5568] hover:text-[#1A202C]">
            <SlidersHorizontal className="w-5 h-5" />
          </button>
        </div>
        
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="bg-[#F7FAFC] border-b border-gray-200 text-xs font-bold text-[#4A5568] uppercase tracking-wider">
                <th className="p-4 pl-6">Employee</th>
                <th className="p-4">Role & Trade</th>
                <th className="p-4">Appraisal Cycle</th>
                <th className="p-4">Workflow Status</th>
                <th className="p-4 text-right pr-6">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {employees.map((emp) => (
                <tr key={emp.id} className="hover:bg-gray-50 transition-colors">
                  <td className="p-4 pl-6">
                    <div className="flex items-center gap-3">
                      {emp.img ? (
                        <img src={emp.img} alt={emp.name} className="w-10 h-10 rounded-full object-cover" />
                      ) : (
                        <div className="w-10 h-10 rounded-full bg-[#EDF2F7] flex items-center justify-center text-[#1A202C] font-bold text-sm">
                          {emp.initials}
                        </div>
                      )}
                      <div>
                        <div className="font-bold text-[#1A202C]">{emp.name}</div>
                        <div className="text-xs text-[#718096]">{emp.id}</div>
                      </div>
                    </div>
                  </td>
                  <td className="p-4">
                    <div className="text-sm font-semibold text-[#1A202C]">{emp.role}</div>
                    <span className="inline-block mt-1 px-2 py-0.5 bg-[#EDF2F7] text-[#4A5568] text-xs font-semibold rounded">
                      {emp.trade}
                    </span>
                  </td>
                  <td className="p-4">
                    <div className="text-sm text-[#1A202C]">{emp.cycle}</div>
                  </td>
                  <td className="p-4">
                    <div className={`flex items-center text-sm font-semibold ${emp.statusColor}`}>
                      {emp.icon ? emp.icon : (
                        <div className={`w-1.5 h-1.5 rounded-full ${emp.dotColor} mr-2`} />
                      )}
                      {emp.status}
                    </div>
                  </td>
                  <td className="p-4 text-right pr-6">
                    {emp.status === 'Completed' ? (
                      <button className="text-sm font-semibold text-[#4A5568] hover:text-[#1A202C]">
                        Download PDF
                      </button>
                    ) : emp.status === 'Self-Assessment' ? (
                      <button className="text-sm font-semibold text-[#4A5568] hover:text-[#1A202C]">
                        View Details
                      </button>
                    ) : (
                      <button className="bg-[#0B1B36] text-white px-4 py-1.5 rounded-lg text-sm font-semibold hover:bg-[#081428] transition-colors">
                        Review Now
                      </button>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        
        <div className="bg-[#F7FAFC] p-4 flex justify-between items-center text-sm text-[#718096] border-t border-gray-200">
          <span>Showing 1-4 of 124 employees</span>
          <div className="flex gap-1">
            <button className="p-1 rounded bg-white border border-gray-200 hover:bg-gray-50 text-gray-400">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="m15 18-6-6 6-6"/></svg>
            </button>
            <button className="p-1 rounded bg-white border border-gray-200 hover:bg-gray-50 text-gray-600">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="m9 18 6-6-6-6"/></svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
