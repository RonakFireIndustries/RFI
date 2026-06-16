import React, { useState } from 'react';
import { 
  Settings as SettingsIcon, Users, Shield, Bell, 
  Link, Clock, Globe, Briefcase, MapPin, Search
} from 'lucide-react';

const settingTabs = [
  { id: 'general', title: 'General Settings', subtitle: 'Company & Language', icon: SettingsIcon },
  { id: 'roles', title: 'Roles & Permissions', subtitle: 'Access Control', icon: Users },
  { id: 'security', title: 'Security', subtitle: '2FA & Auth Policy', icon: Shield },
  { id: 'notifications', title: 'Notifications', subtitle: 'Alert Preferences', icon: Bell },
  { id: 'integrations', title: 'Integrations', subtitle: 'GPS & Payroll APIs', icon: Link },
  { id: 'audit', title: 'Audit Logs', subtitle: 'System Activity', icon: Clock },
];

export default function Settings() {
  const [activeTab, setActiveTab] = useState('general');

  return (
    <div className="w-full h-[calc(100vh-100px)] flex flex-col">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-extrabold text-[#0B1B36] mb-1">
          System Settings
        </h1>
        <p className="text-[#718096] text-sm">
          Manage organizational preferences, security, and enterprise integrations.
        </p>
      </div>

      <div className="flex flex-col md:flex-row gap-8 flex-grow">
        {/* Left Settings Sidebar */}
        <div className="w-full md:w-72 flex-shrink-0 space-y-1">
          {settingTabs.map((tab) => (
            <button
              key={tab.id}
              onClick={() => setActiveTab(tab.id)}
              className={`w-full flex items-center p-4 rounded-xl transition-all relative ${
                activeTab === tab.id 
                  ? 'bg-[#EDF2F7] text-[#0B1B36]' 
                  : 'bg-transparent text-[#4A5568] hover:bg-gray-50'
              }`}
            >
              {activeTab === tab.id && (
                <div className="absolute left-0 top-0 bottom-0 w-1 bg-[#0B1B36] rounded-l-xl"></div>
              )}
              <div className="mr-4">
                <tab.icon className={`w-5 h-5 ${activeTab === tab.id ? 'text-[#0B1B36]' : 'text-[#718096]'}`} />
              </div>
              <div className="text-left">
                <div className={`text-sm font-bold ${activeTab === tab.id ? 'text-[#0B1B36]' : 'text-[#4A5568]'}`}>
                  {tab.title}
                </div>
                <div className={`text-[10px] font-semibold ${activeTab === tab.id ? 'text-[#4A5568]' : 'text-[#A0AEC0]'}`}>
                  {tab.subtitle}
                </div>
              </div>
            </button>
          ))}
        </div>

        {/* Right Content Area */}
        <div className="flex-grow flex flex-col">
          {activeTab === 'general' && (
            <div className="space-y-6 flex-grow">
              
              {/* Company Profile Card */}
              <div className="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                <h2 className="text-xl font-bold text-[#0B1B36] mb-6">Company Profile</h2>
                
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                  <div>
                    <label className="block text-xs font-bold text-[#718096] uppercase tracking-wider mb-2">Company Name</label>
                    <div className="relative">
                      <input 
                        type="text" 
                        defaultValue="Ronak Fire Industries" 
                        className="w-full p-3 border border-gray-300 rounded-lg text-sm font-semibold text-[#1A202C] bg-[#F7FAFC] focus:outline-none focus:ring-2 focus:ring-[#0B1B36] focus:bg-white transition-colors"
                      />
                    </div>
                  </div>
                  <div>
                    <label className="block text-xs font-bold text-[#718096] uppercase tracking-wider mb-2">Business ID</label>
                    <div className="relative">
                      <input 
                        type="text" 
                        defaultValue="RF-IND-2023-990" 
                        className="w-full p-3 border border-gray-300 rounded-lg text-sm font-semibold text-[#1A202C] bg-[#F7FAFC] focus:outline-none focus:ring-2 focus:ring-[#0B1B36] focus:bg-white transition-colors"
                      />
                    </div>
                  </div>
                </div>

                <div>
                  <label className="block text-xs font-bold text-[#718096] uppercase tracking-wider mb-2">Registered Address</label>
                  <textarea 
                    rows="3"
                    defaultValue="Industrial Area Phase II, Sector 18, Ahmedabad, Gujarat, India"
                    className="w-full p-3 border border-gray-300 rounded-lg text-sm font-semibold text-[#1A202C] bg-[#F7FAFC] focus:outline-none focus:ring-2 focus:ring-[#0B1B36] focus:bg-white transition-colors resize-none"
                  ></textarea>
                </div>
              </div>

              {/* Localization Card */}
              <div className="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                <h2 className="text-xl font-bold text-[#0B1B36] mb-6">Localization</h2>
                
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label className="block text-xs font-bold text-[#718096] uppercase tracking-wider mb-2">Default Language</label>
                    <select className="w-full p-3 border border-gray-300 rounded-lg text-sm font-semibold text-[#1A202C] bg-white focus:outline-none focus:ring-2 focus:ring-[#0B1B36] appearance-none cursor-pointer">
                      <option>English (US)</option>
                      <option>English (UK)</option>
                      <option>Hindi</option>
                    </select>
                  </div>
                  <div>
                    <label className="block text-xs font-bold text-[#718096] uppercase tracking-wider mb-2">Timezone</label>
                    <select className="w-full p-3 border border-gray-300 rounded-lg text-sm font-semibold text-[#1A202C] bg-white focus:outline-none focus:ring-2 focus:ring-[#0B1B36] appearance-none cursor-pointer">
                      <option>(GMT+05:30) India Standard Time (IST)</option>
                      <option>(GMT+00:00) UTC</option>
                      <option>(GMT-05:00) Eastern Time (US & Canada)</option>
                    </select>
                  </div>
                </div>
              </div>

            </div>
          )}

          {activeTab !== 'general' && (
            <div className="bg-white border border-gray-200 rounded-2xl p-16 shadow-sm flex flex-col items-center justify-center text-center flex-grow">
              <SettingsIcon className="w-16 h-16 text-[#CBD5E0] mb-4" />
              <h2 className="text-xl font-bold text-[#0B1B36] mb-2">{settingTabs.find(t => t.id === activeTab)?.title}</h2>
              <p className="text-[#718096] max-w-md">This settings module is currently under development. Additional configuration options will be available here soon.</p>
            </div>
          )}

          {/* Action Buttons Footer */}
          <div className="mt-8 pt-6 border-t border-gray-200 flex justify-end gap-4">
            <button className="px-6 py-2.5 bg-white border border-[#CBD5E0] text-[#4A5568] rounded-full text-sm font-bold hover:bg-gray-50 transition-colors">
              Discard Changes
            </button>
            <button className="px-6 py-2.5 bg-[#0B1B36] text-white rounded-full text-sm font-bold hover:bg-[#081428] transition-colors shadow-sm">
              Save System Settings
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
