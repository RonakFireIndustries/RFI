import React from 'react';
import { 
  BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer 
} from 'recharts';
import { Plus, SlidersHorizontal, CheckCircle2, AlertCircle } from 'lucide-react';

const siteStats = [
  { name: 'Jan', manpower: 60, budget: 40 },
  { name: 'Feb', manpower: 65, budget: 45 },
  { name: 'Mar', manpower: 80, budget: 55 },
  { name: 'Apr', manpower: 70, budget: 60 },
  { name: 'May', manpower: 85, budget: 75 },
];

const sites = [
  {
    id: 1,
    name: 'Metro Line 4 Phase 1',
    location: 'Downtown Central Hub',
    status: 'On-Track',
    statusBg: 'bg-[#ED8936]', // Orange
    workers: 452,
    manager: 'Elena Rodriguez',
    img: 'https://images.unsplash.com/photo-1541888086225-f64041e2612a?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
  },
  {
    id: 2,
    name: 'Sky-Rise Apartments',
    location: 'North Harbor District',
    status: 'Delayed',
    statusBg: 'bg-[#E53E3E]', // Red
    borderColor: 'border-[#E53E3E]',
    workers: 128,
    manager: 'Marcus Chen',
    img: 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
  },
  {
    id: 3,
    name: 'East River Bridge',
    location: 'Bridgeport Bypass',
    status: 'On-Track',
    statusBg: 'bg-[#ED8936]',
    workers: 315,
    manager: 'Sarah Jenkins',
    img: 'https://images.unsplash.com/photo-1545465330-802c6b41cb02?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
  },
  {
    id: 4,
    name: 'Green Tech Facility',
    location: 'Industrial Zone B',
    status: 'On-Track',
    statusBg: 'bg-[#ED8936]',
    workers: 289,
    manager: 'David Wilson',
    img: 'https://images.unsplash.com/photo-1581094794329-c8112a89af12?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
  }
];

export default function Sites() {
  return (
    <div className="w-full">
      {/* Top Section */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {/* Chart */}
        <div className="lg:col-span-2 bg-white border border-gray-200 rounded-2xl p-6 shadow-sm flex flex-col h-80">
          <div className="flex justify-between items-center mb-6">
            <h2 className="text-lg font-bold text-[#0B1B36]">Site Statistics: Manpower vs Budget</h2>
            <div className="flex items-center gap-4 text-xs font-semibold text-[#4A5568]">
              <div className="flex items-center"><div className="w-2.5 h-2.5 rounded-full bg-[#0B1B36] mr-2"/> Manpower</div>
              <div className="flex items-center"><div className="w-2.5 h-2.5 rounded-full bg-[#ED8936] mr-2"/> Budget</div>
            </div>
          </div>
          <div className="flex-grow w-full -ml-4">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={siteStats} barSize={12} barGap={4}>
                <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#E2E8F0" />
                <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{fill: '#718096', fontSize: 12}} dy={10} />
                <YAxis hide />
                <Tooltip cursor={{fill: '#F7FAFC'}} contentStyle={{ borderRadius: '8px', border: 'none', boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)' }} />
                <Bar dataKey="manpower" fill="#0B1B36" radius={[4, 4, 0, 0]} />
                <Bar dataKey="budget" fill="#ED8936" radius={[4, 4, 0, 0]} />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>

        {/* Right KPI Cards */}
        <div className="flex flex-col gap-6 h-80">
          <div className="bg-white border-t-4 border-[#0B1B36] rounded-2xl p-6 shadow-sm flex-1 flex flex-col justify-center">
            <h3 className="text-xs font-bold text-[#718096] tracking-widest uppercase mb-3">Total Active Personnel</h3>
            <div className="text-4xl font-extrabold text-[#0B1B36] mb-2">2,481</div>
            <div className="text-sm font-bold text-[#DD6B20] flex items-center">
              ↗ +12% from last month
            </div>
          </div>
          
          <div className="bg-white border-l-4 border-[#ED8936] rounded-2xl p-6 shadow-sm flex-1 flex flex-col justify-center">
            <h3 className="text-xs font-bold text-[#718096] tracking-widest uppercase mb-3">Active Project Sites</h3>
            <div className="text-4xl font-extrabold text-[#0B1B36] mb-2">18</div>
            <div className="text-sm font-semibold text-[#718096] flex items-center">
              <CheckCircle2 className="w-4 h-4 mr-1.5" />
              14 On-Track, 4 Delayed
            </div>
          </div>
        </div>
      </div>

      {/* Sites Grid */}
      <div className="mb-8">
        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
          <div>
            <h2 className="text-xl font-bold text-[#0B1B36]">Active Construction Sites</h2>
            <p className="text-sm text-[#718096]">Overview of all ongoing infrastructure and residential projects</p>
          </div>
          <div className="flex gap-3">
            <button className="flex items-center px-4 py-2 bg-[#0B1B36] text-white rounded-xl font-semibold hover:bg-[#081428] transition-colors shadow-sm">
              <Plus className="w-4 h-4 mr-2" />
              Add New Site
            </button>
            <button className="flex items-center px-4 py-2 bg-white border border-[#CBD5E0] text-[#4A5568] rounded-xl font-semibold hover:bg-gray-50 transition-colors">
              <SlidersHorizontal className="w-4 h-4 mr-2" />
              Filter
            </button>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {sites.map(site => (
            <div key={site.id} className={`bg-white rounded-2xl overflow-hidden shadow-sm border ${site.borderColor || 'border-[#0B1B36]'}`}>
              {/* Image Header */}
              <div className="h-48 relative overflow-hidden">
                <img src={site.img} alt={site.name} className="w-full h-full object-cover" />
                <div className={`absolute top-4 right-4 ${site.statusBg} text-white text-xs font-bold px-3 py-1.5 rounded-full flex items-center shadow-md`}>
                  {site.status === 'Delayed' ? <AlertCircle className="w-3 h-3 mr-1.5" /> : <div className="w-1.5 h-1.5 bg-white rounded-full mr-1.5" />}
                  {site.status}
                </div>
              </div>
              
              {/* Content */}
              <div className="p-6">
                <div className="border-b border-gray-100 pb-4 mb-4">
                  <h3 className="text-lg font-bold text-[#0B1B36] mb-1">{site.name}</h3>
                  <div className="text-sm font-medium text-[#718096] flex items-center">
                    <svg className="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {site.location}
                  </div>
                </div>
                
                <div className="flex justify-between">
                  <div>
                    <div className="text-xs font-bold text-[#718096] mb-1">Workers</div>
                    <div className="text-sm font-bold text-[#0B1B36]">{site.workers} Assigned</div>
                  </div>
                  <div>
                    <div className="text-xs font-bold text-[#718096] mb-1">Manager</div>
                    <div className="text-sm font-bold text-[#0B1B36]">{site.manager}</div>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
