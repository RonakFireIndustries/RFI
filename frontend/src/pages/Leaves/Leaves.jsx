import React, { useState } from 'react';
import { 
  ClipboardList, CheckCircle, Users, ChevronLeft, ChevronRight, Plus
} from 'lucide-react';

// Generate days for October 2023 calendar
const daysInMonth = Array.from({ length: 31 }, (_, i) => i + 1);
// Oct 2023 started on a Sunday. Let's pad for Mon start.
// Sept had 30 days.
const paddedDays = [25, 26, 27, 28, 29, 30, ...daysInMonth];

const pendingApprovals = [
  {
    id: 1,
    name: 'Sarah Jenkins',
    role: 'SAFETY INSPECTOR',
    type: 'ANNUAL',
    img: '/static/images/avatar/2.jpg',
    dates: 'Oct 15 - Oct 20',
    reason: '"Family wedding, already coordinated with site lead."',
  },
  {
    id: 2,
    name: 'David Chen',
    role: 'FOREMAN',
    type: 'MEDICAL',
    img: '/static/images/avatar/4.jpg',
    dates: 'Oct 11 - Oct 12',
    reason: '"Scheduled dental procedure. Handing over to Sam."',
  },
  {
    id: 3,
    name: 'Robert Miller',
    role: 'ELECTRICIAN',
    type: 'UNPAID',
    img: '/static/images/avatar/3.jpg',
    dates: 'Oct 25 (Single Day)',
    reason: '"Personal matter."',
  }
];

export default function Leaves() {
  return (
    <div className="w-full">
      {/* Top action bar */}
      <div className="flex justify-end mb-6">
        <button className="flex items-center px-5 py-2.5 bg-[#0B1B36] text-white rounded-xl font-semibold hover:bg-[#081428] transition-colors shadow-sm">
          <Plus className="w-5 h-5 mr-2" />
          Apply for Leave
        </button>
      </div>

      {/* KPI Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        {/* Pending Requests */}
        <div className="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
          <div className="flex justify-between items-start mb-4">
            <span className="text-sm font-semibold text-[#718096] tracking-wider uppercase">Pending Requests</span>
            <div className="p-2.5 bg-[#F7FAFC] rounded-xl text-[#0B1B36]">
              <ClipboardList className="w-6 h-6" />
            </div>
          </div>
          <span className="text-5xl font-extrabold text-[#0B1B36] tracking-tight">12</span>
          <p className="text-sm font-bold text-[#E53E3E] mt-4 flex items-center">
            <span className="mr-1">↗</span> 4 from yesterday
          </p>
        </div>

        {/* Approved Today */}
        <div className="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
          <div className="flex justify-between items-start mb-4">
            <span className="text-sm font-semibold text-[#718096] tracking-wider uppercase">Approved Today</span>
            <div className="p-2.5 bg-[#FFF5F5] rounded-xl text-[#DD6B20]">
              <CheckCircle className="w-6 h-6" />
            </div>
          </div>
          <span className="text-5xl font-extrabold text-[#0B1B36] tracking-tight">08</span>
          <p className="text-sm font-medium text-[#718096] mt-4 flex items-center">
            <span className="mr-1.5">📅</span> Next update at 16:00
          </p>
        </div>

        {/* On Leave Today */}
        <div className="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
          <div className="flex justify-between items-start mb-4">
            <span className="text-sm font-semibold text-[#718096] tracking-wider uppercase">On Leave Today</span>
            <div className="p-2.5 bg-[#EDF2F7] rounded-xl text-[#4A5568]">
              <Users className="w-6 h-6" />
            </div>
          </div>
          <span className="text-5xl font-extrabold text-[#0B1B36] tracking-tight">15</span>
          <p className="text-sm font-medium text-[#718096] mt-4 flex items-center">
            <span className="mr-1.5">🕒</span> 4.5% of total workforce
          </p>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Left Column: Calendar */}
        <div className="lg:col-span-2 bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
          <div className="p-6 border-b border-gray-100 flex justify-between items-center bg-white">
            <h2 className="text-xl font-bold text-[#0B1B36]">Company Leave Calendar</h2>
            <div className="flex items-center gap-4">
              <div className="bg-[#EDF2F7] rounded-lg p-1 flex">
                <button className="px-4 py-1.5 bg-white shadow-sm rounded text-sm font-semibold text-[#1A202C]">Month</button>
                <button className="px-4 py-1.5 text-sm font-semibold text-[#718096]">Week</button>
              </div>
              <div className="flex items-center gap-3">
                <button className="text-[#4A5568] hover:text-[#1A202C]"><ChevronLeft className="w-5 h-5"/></button>
                <span className="text-sm font-bold text-[#1A202C]">October 2023</span>
                <button className="text-[#4A5568] hover:text-[#1A202C]"><ChevronRight className="w-5 h-5"/></button>
              </div>
            </div>
          </div>
          
          <div className="flex-grow p-6">
            <div className="grid grid-cols-7 gap-3 mb-2">
              {['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'].map(day => (
                <div key={day} className="text-center text-xs font-bold text-[#A0AEC0] tracking-wider">
                  {day}
                </div>
              ))}
            </div>
            
            <div className="grid grid-cols-7 gap-3 h-[500px]">
              {paddedDays.map((day, idx) => {
                const isCurrentMonth = idx > 5 && idx <= 36; // Rough check for October
                const isSelected = day === 10 && isCurrentMonth;
                const hasEvent1 = day === 3 && isCurrentMonth;
                
                return (
                  <div 
                    key={idx} 
                    className={`border rounded-xl p-2 relative flex flex-col transition-all ${
                      isSelected ? 'border-2 border-[#4A5568] bg-[#F7FAFC]' : 
                      isCurrentMonth ? 'border-gray-200 bg-white hover:border-gray-300' : 
                      'border-gray-100 bg-gray-50 opacity-50'
                    }`}
                  >
                    <span className={`text-sm font-semibold mb-2 ${isSelected || (day === 3 && isCurrentMonth) ? 'text-[#0B1B36]' : 'text-[#718096]'}`}>
                      {day}
                    </span>
                    
                    {/* Mock events */}
                    {hasEvent1 && (
                      <div className="bg-[#0B1B36] text-white text-[10px] font-semibold px-2 py-1 rounded truncate mt-auto">
                        8 Crane Op...
                      </div>
                    )}
                    {isSelected && (
                      <div className="space-y-1 mt-auto">
                        <div className="bg-[#ED8936] text-white text-[10px] font-semibold px-2 py-1 rounded truncate">
                          Site Visit
                        </div>
                        <div className="bg-[#4A5568] text-white text-[10px] font-semibold px-2 py-1 rounded truncate">
                          3 Leave
                        </div>
                      </div>
                    )}
                  </div>
                );
              })}
            </div>
          </div>
        </div>

        {/* Right Column: Pending Approvals */}
        <div className="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 flex flex-col h-full">
          <h2 className="text-xl font-bold text-[#0B1B36] mb-6">Pending Approvals</h2>
          
          <div className="flex-grow space-y-4">
            {pendingApprovals.map(approval => (
              <div key={approval.id} className="border border-gray-200 rounded-xl p-5 hover:border-gray-300 transition-colors">
                <div className="flex justify-between items-start mb-4">
                  <div className="flex gap-3">
                    <img src={approval.img} alt={approval.name} className="w-10 h-10 rounded-full object-cover bg-gray-200" />
                    <div>
                      <h4 className="text-sm font-bold text-[#1A202C] leading-tight">{approval.name}</h4>
                      <span className="text-[10px] font-bold text-[#A0AEC0] tracking-wider uppercase">{approval.role}</span>
                    </div>
                  </div>
                  <span className={`text-[10px] font-extrabold px-2 py-1 rounded tracking-wider uppercase ${
                    approval.type === 'ANNUAL' ? 'bg-[#EDF2F7] text-[#4A5568]' :
                    approval.type === 'MEDICAL' ? 'bg-[#FFF5F5] text-[#E53E3E]' :
                    'bg-[#F7FAFC] text-[#718096]'
                  }`}>
                    {approval.type}
                  </span>
                </div>
                
                <div className="mb-5">
                  <p className="text-xs font-semibold text-[#1A202C] flex items-center mb-1">
                    <span className="mr-1.5">📅</span> {approval.dates}
                  </p>
                  <p className="text-xs text-[#718096] italic leading-relaxed pl-5">
                    {approval.reason}
                  </p>
                </div>
                
                <div className="flex gap-3">
                  <button className="flex-1 py-2 text-sm font-semibold text-[#E53E3E] bg-white border border-[#FEB2B2] rounded-lg hover:bg-red-50 transition-colors">
                    Reject
                  </button>
                  <button className="flex-1 py-2 text-sm font-semibold text-white bg-[#0B1B36] rounded-lg hover:bg-[#081428] transition-colors">
                    Approve
                  </button>
                </div>
              </div>
            ))}
          </div>
          
          <button className="w-full text-center text-sm font-bold text-[#0B1B36] hover:underline mt-6">
            View All Requests
          </button>
        </div>
      </div>
    </div>
  );
}
