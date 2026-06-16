import React from 'react';
import { 
  Bell, AlertTriangle, FileText, Calendar, 
  Paperclip, Pin, ChevronRight, Filter, Download
} from 'lucide-react';

export default function Announcements() {
  return (
    <div className="w-full">
      {/* Header Search */}
      <div className="mb-8 flex items-center bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm w-full md:w-2/3">
        <div className="text-[#A0AEC0] mr-3">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <input 
          type="text" 
          placeholder="Search announcements or documents..." 
          className="w-full bg-transparent border-none outline-none text-sm font-semibold text-[#1A202C] placeholder-[#A0AEC0]"
        />
      </div>

      <div className="grid grid-cols-1 xl:grid-cols-3 gap-8">
        
        {/* Main Content Area */}
        <div className="xl:col-span-2 space-y-8">
          
          {/* Flash Update Hero */}
          <div className="bg-[#1A263E] rounded-3xl p-8 text-white relative overflow-hidden shadow-md">
            <div className="absolute right-0 top-0 opacity-10 transform translate-x-1/4 -translate-y-1/4">
              <svg width="300" height="300" viewBox="0 0 24 24" fill="currentColor"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2zm-2 1H8v-6c0-2.48 1.51-4.5 4-4.5s4 2.02 4 4.5v6z"/></svg>
            </div>
            <div className="relative z-10">
              <span className="inline-block px-3 py-1 bg-[#DD6B20] text-white text-[10px] font-extrabold uppercase tracking-widest rounded-md mb-6">
                FLASH UPDATE
              </span>
              <h1 className="text-3xl md:text-4xl font-extrabold mb-4 leading-tight">Site Q3 Efficiency Target Reached</h1>
              <p className="text-[#A0AEC0] text-sm md:text-base max-w-lg mb-8 leading-relaxed font-medium">
                Congratulations to all Site Management teams. BuildForce HR has recorded a 15% increase in manpower allocation precision this quarter.
              </p>
              <div className="flex gap-4">
                <button className="px-6 py-2.5 bg-white text-[#0B1B36] rounded-xl font-bold hover:bg-gray-100 transition-colors">
                  Read Full Report
                </button>
                <button className="px-6 py-2.5 bg-transparent border border-[#4A5568] text-white rounded-xl font-bold hover:bg-[#2D3748] transition-colors">
                  Dismiss
                </button>
              </div>
            </div>
          </div>

          {/* Latest Announcements Header */}
          <div className="flex justify-between items-end border-b border-gray-200 pb-4">
            <h2 className="text-2xl font-extrabold text-[#0B1B36]">Latest Announcements</h2>
            <div className="flex gap-2">
              <button className="flex items-center text-xs font-bold text-[#4A5568] bg-white border border-gray-200 px-3 py-1.5 rounded-lg">
                <Filter className="w-3 h-3 mr-1.5" /> Filter
              </button>
              <button className="flex items-center text-xs font-bold text-[#4A5568] bg-white border border-gray-200 px-3 py-1.5 rounded-lg">
                Sort
              </button>
            </div>
          </div>

          {/* Feed Items */}
          <div className="space-y-4">
            
            {/* Item 1 (Image) */}
            <div className="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row gap-6 hover:border-gray-300 transition-colors">
              <img 
                src="https://images.unsplash.com/photo-1541888086225-f64041e2612a?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" 
                alt="Construction Site" 
                className="w-full md:w-48 h-32 object-cover rounded-xl"
              />
              <div className="flex-1 flex flex-col justify-between">
                <div>
                  <div className="flex justify-between items-start mb-2">
                    <span className="text-[10px] font-bold text-[#3182CE] bg-[#EBF8FF] px-2 py-0.5 rounded uppercase tracking-wider">COMPANY NEWS</span>
                    <button className="text-[#A0AEC0] hover:text-[#4A5568]"><Paperclip className="w-4 h-4" /></button>
                  </div>
                  <h3 className="text-lg font-bold text-[#0B1B36] mb-2">Mandatory Payroll Migration: Action Required</h3>
                  <p className="text-sm text-[#718096] mb-4">
                    All employees must verify their banking details in the new BuildForce HR Portal before Friday, 5:00 PM. This...
                  </p>
                </div>
                <div className="flex justify-between items-center text-xs font-bold text-[#A0AEC0]">
                  <div className="flex gap-4">
                    <span>🕒 2 hours ago</span>
                    <span>👁 452 views</span>
                  </div>
                  <span className="flex items-center text-[#4A5568] bg-gray-100 px-2 py-1 rounded"><FileText className="w-3 h-3 mr-1" /> instructions.pdf</span>
                </div>
              </div>
            </div>

            {/* Item 2 (Alert) */}
            <div className="bg-white border-l-4 border-l-[#E53E3E] border border-gray-200 rounded-2xl p-6 shadow-sm hover:border-gray-300 transition-colors">
              <div className="flex justify-between items-start mb-3">
                <span className="text-[10px] font-bold text-[#E53E3E] bg-[#FFF5F5] px-2 py-0.5 rounded uppercase tracking-wider flex items-center">
                  <AlertTriangle className="w-3 h-3 mr-1" /> SAFETY ALERT
                </span>
                <button className="text-[#A0AEC0] hover:text-[#4A5568]">⋮</button>
              </div>
              <h3 className="text-lg font-bold text-[#0B1B36] mb-2">Extreme Heat Advisory: Site Precautions</h3>
              <p className="text-sm text-[#718096] mb-4 leading-relaxed">
                Site managers at Zone B and D are instructed to implement 15-minute mandatory cooling breaks every hour due to the local heat index reaching 42°C. Hydration stations have been reinforced.
              </p>
              <div className="flex justify-between items-center text-xs font-bold text-[#A0AEC0]">
                <div className="flex gap-4">
                  <span>🕒 5 hours ago</span>
                  <span>👤 Safety Committee</span>
                </div>
                <button className="text-[#E53E3E] font-bold hover:underline">Confirm Receipt</button>
              </div>
            </div>

            {/* Item 3 (Date Block) */}
            <div className="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row gap-6 hover:border-gray-300 transition-colors">
              <div className="w-24 h-24 bg-[#7B341E] rounded-xl flex flex-col items-center justify-center text-white shrink-0">
                <span className="text-xs font-bold uppercase tracking-widest opacity-80">SEP</span>
                <span className="text-4xl font-extrabold leading-none mt-1">04</span>
              </div>
              <div className="flex-1 flex flex-col justify-between">
                <div>
                  <div className="flex justify-between items-start mb-2">
                    <span className="text-[10px] font-bold text-[#DD6B20] bg-[#FFFAF0] px-2 py-0.5 rounded uppercase tracking-wider">HOLIDAY NOTICE</span>
                    <button className="text-[#A0AEC0] hover:text-[#4A5568]"><Calendar className="w-4 h-4" /></button>
                  </div>
                  <h3 className="text-lg font-bold text-[#0B1B36] mb-2">Labor Day Office Closure & Site Schedule</h3>
                  <p className="text-sm text-[#718096] mb-4">
                    The central headquarters will be closed on Monday, Sep 4. Site shifts will operate on a half-day basis (07:00 - 12:00) with overtime pay active for all field staff.
                  </p>
                </div>
                <div className="flex justify-between items-center text-xs font-bold text-[#A0AEC0]">
                  <span>🕒 1 day ago</span>
                  <span className="flex items-center text-[#4A5568] bg-gray-100 px-2 py-1 rounded"><FileText className="w-3 h-3 mr-1" /> site_roster.jpg</span>
                </div>
              </div>
            </div>

          </div>

          <button className="w-full py-4 text-sm font-bold text-[#4A5568] bg-[#F7FAFC] border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
            Load Older Announcements
          </button>
          
        </div>

        {/* Right Sidebar */}
        <div className="space-y-8">
          
          {/* Categories */}
          <div className="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
            <div className="flex justify-between items-center mb-5">
              <h3 className="text-lg font-bold text-[#0B1B36]">Notice Categories</h3>
              <Filter className="w-4 h-4 text-[#A0AEC0]" />
            </div>
            <div className="space-y-3">
              <div className="bg-[#FFF5F5] border border-[#FED7D7] rounded-xl p-3 flex justify-between items-center cursor-pointer hover:bg-[#FEE2E2] transition-colors">
                <div className="flex items-center gap-3">
                  <div className="w-8 h-8 bg-white rounded flex items-center justify-center"><AlertTriangle className="w-4 h-4 text-[#E53E3E]" /></div>
                  <div>
                    <div className="text-sm font-bold text-[#C53030]">Safety Alerts</div>
                    <div className="text-[10px] font-bold text-[#E53E3E]">2 New Today</div>
                  </div>
                </div>
                <ChevronRight className="w-4 h-4 text-[#E53E3E]" />
              </div>

              <div className="bg-[#EBF8FF] border border-[#BEE3F8] rounded-xl p-3 flex justify-between items-center cursor-pointer hover:bg-[#E2E8F0] transition-colors">
                <div className="flex items-center gap-3">
                  <div className="w-8 h-8 bg-white rounded flex items-center justify-center"><FileText className="w-4 h-4 text-[#3182CE]" /></div>
                  <div>
                    <div className="text-sm font-bold text-[#2B6CB0]">Company News</div>
                    <div className="text-[10px] font-bold text-[#3182CE]">Weekly updates</div>
                  </div>
                </div>
                <ChevronRight className="w-4 h-4 text-[#3182CE]" />
              </div>

              <div className="bg-[#FFFAF0] border border-[#FEEBC8] rounded-xl p-3 flex justify-between items-center cursor-pointer hover:bg-[#FEE2E2] transition-colors">
                <div className="flex items-center gap-3">
                  <div className="w-8 h-8 bg-white rounded flex items-center justify-center"><Calendar className="w-4 h-4 text-[#DD6B20]" /></div>
                  <div>
                    <div className="text-sm font-bold text-[#C05621]">Holiday Notices</div>
                    <div className="text-[10px] font-bold text-[#DD6B20]">Upcoming: Labor Day</div>
                  </div>
                </div>
                <ChevronRight className="w-4 h-4 text-[#DD6B20]" />
              </div>
            </div>
            <button className="w-full mt-4 text-xs font-bold text-[#4A5568] hover:text-[#0B1B36]">+ Customize Feed</button>
          </div>

          {/* Notice Board (Sticky Notes) */}
          <div className="bg-[#FAF8F5] border border-[#E2E8F0] rounded-2xl p-6 shadow-inner relative">
            <div className="flex justify-between items-center mb-6">
              <h3 className="text-xl font-extrabold text-[#2D3748] tracking-tight">Notice Board</h3>
              <Pin className="w-5 h-5 text-[#4A5568]" />
            </div>

            <div className="space-y-6">
              {/* Note 1 */}
              <div className="bg-[#FEFCBF] p-4 rounded shadow-md transform -rotate-1 hover:rotate-0 transition-transform relative">
                <div className="absolute -top-3 left-1/2 -translate-x-1/2 w-4 h-4 bg-red-400 rounded-full shadow-sm" />
                <h4 className="text-sm font-bold text-gray-800 mb-2">Reminder: PPE Check</h4>
                <p className="text-xs text-gray-700 leading-relaxed mb-3">
                  Safety inspectors will be random this week. Ensure all vests and hard hats are Site Flow compliant.
                </p>
                <div className="text-right text-[10px] font-bold text-gray-500">- HR Team</div>
              </div>

              {/* Note 2 */}
              <div className="bg-white border border-gray-200 p-4 rounded shadow-md transform rotate-1 hover:rotate-0 transition-transform relative">
                <div className="absolute -top-3 left-1/2 -translate-x-1/2 w-4 h-4 bg-blue-400 rounded-full shadow-sm" />
                <h4 className="text-sm font-bold text-gray-800 mb-2">Lunch & Learn</h4>
                <p className="text-xs text-gray-700 leading-relaxed mb-3">
                  Join us for a session on "Sustainable Concrete Pouring" this Wednesday in the main hall.
                </p>
                <div className="text-right text-[10px] font-bold text-gray-500">- Eng Dept</div>
              </div>

              {/* Note 3 */}
              <div className="bg-[#E6FFFA] p-4 rounded shadow-md transform -rotate-2 hover:rotate-0 transition-transform relative border border-[#B2F5EA]">
                <div className="absolute -top-3 left-1/2 -translate-x-1/2 w-4 h-4 bg-yellow-400 rounded-full shadow-sm" />
                <h4 className="text-sm font-bold text-[#234E52] mb-2 flex items-center gap-2">
                  <span className="text-lg">⭐</span> Employee of the Month
                </h4>
                <p className="text-xs text-[#285E61] font-semibold flex items-center gap-2">
                  <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?ixlib=rb-4.0.3&auto=format&fit=crop&w=64&q=80" className="w-6 h-6 rounded-full" alt="Sarah" />
                  Sarah Jenkins
                </p>
                <div className="text-right text-[10px] font-bold text-[#319795] mt-2">- Site A Management</div>
              </div>
            </div>
            
            {/* Pinned Document */}
            <div className="mt-8 bg-[#0B1B36] text-white p-4 rounded-xl flex items-center gap-4 cursor-pointer hover:bg-[#1A263E] transition-colors shadow-lg">
              <div className="p-2 bg-white/10 rounded-lg">
                <FileText className="w-6 h-6 text-white" />
              </div>
              <div className="flex-1">
                <div className="text-sm font-bold">Employee Handbook</div>
                <div className="text-[10px] font-semibold text-gray-400">Updated July 2023</div>
              </div>
              <Download className="w-4 h-4 text-gray-400" />
            </div>

          </div>
          
        </div>
      </div>
    </div>
  );
}
