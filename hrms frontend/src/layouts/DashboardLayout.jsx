import React, { useState } from 'react';
import { Outlet, useNavigate, useLocation } from 'react-router-dom';
import { 
  Menu, LayoutDashboard, Users, Building2, Clock, 
  FileText, DollarSign, Settings, Bell, ChevronDown, Search,
  CalendarDays, Target
} from 'lucide-react';

const menuItems = [
  { text: 'Dashboard', icon: LayoutDashboard, path: '/dashboard' },
  { text: 'Employees', icon: Users, path: '/employees' },
  { text: 'Sites & Allocations', icon: Building2, path: '/sites' },
  { text: 'Time & Attendance', icon: Clock, path: '/attendance' },
  { text: 'Leave', icon: CalendarDays, path: '/leave' },
  { text: 'Daily Work Reports', icon: FileText, path: '/reports' },
  { text: 'Payroll', icon: DollarSign, path: '/payroll' },
  { text: 'Performance', icon: Target, path: '/performance' },
  { text: 'Announcements', icon: Bell, path: '/announcements' },
  { text: 'Settings', icon: Settings, path: '/settings' },
];

export default function DashboardLayout() {
  const navigate = useNavigate();
  const location = useLocation();
  const [mobileOpen, setMobileOpen] = useState(false);

  const handleDrawerToggle = () => {
    setMobileOpen(!mobileOpen);
  };

  const drawer = (
    <div className="flex flex-col h-full bg-[#F8FAFC]">
      <div className="p-6">
        <h2 className="text-xl font-extrabold text-[#0B1B36] leading-tight mb-1">
          Ronak Fire<br/>Industries
        </h2>
      </div>
      
      <div className="mx-4 mb-4 border-b border-gray-200" />
      
      <nav className="px-4 flex-grow overflow-y-auto">
        <ul className="space-y-1">
          {menuItems.map((item) => {
            const isSelected = location.pathname.startsWith(item.path);
            const Icon = item.icon;
            return (
              <li key={item.text}>
                <button
                  onClick={() => {
                    navigate(item.path);
                    setMobileOpen(false);
                  }}
                  className={`w-full flex items-center px-3 py-2.5 rounded-xl text-left transition-colors ${
                    isSelected 
                      ? 'bg-[#E2E8F0] text-[#0B1B36] border-r-4 border-[#0B1B36]' 
                      : 'text-[#4A5568] hover:bg-[#EDF2F7]'
                  }`}
                >
                  <Icon className={`h-5 w-5 mr-3 ${isSelected ? 'text-[#0B1B36]' : 'text-[#718096]'}`} />
                  <span className={`text-[0.9rem] ${isSelected ? 'font-semibold' : 'font-medium'}`}>
                    {item.text}
                  </span>
                </button>
              </li>
            );
          })}
        </ul>
      </nav>
      
      <div className="p-4">
        <div className="flex items-center gap-3 p-3 bg-[#EDF2F7] rounded-xl">
          <img src="/static/images/avatar/1.jpg" alt="User Avatar" className="w-10 h-10 rounded-full object-cover bg-gray-300" />
          <div className="flex flex-col">
            <span className="text-sm font-semibold text-[#1A202C]">Ronak</span>
            <span className="text-xs text-[#718096]">Admin</span>
          </div>
        </div>
      </div>
    </div>
  );

  return (
    <div className="flex min-h-screen bg-white">
      {/* Mobile Drawer Overlay */}
      {mobileOpen && (
        <div 
          className="fixed inset-0 bg-black/50 z-40 sm:hidden"
          onClick={handleDrawerToggle}
        />
      )}
      
      {/* Sidebar Navigation */}
      <aside className={`fixed inset-y-0 left-0 z-50 w-64 transform transition-transform duration-200 ease-in-out sm:translate-x-0 ${
        mobileOpen ? 'translate-x-0' : '-translate-x-full'
      } border-r border-[#E2E8F0]`}>
        {drawer}
      </aside>

      {/* Main Content Area */}
      <div className="flex-1 sm:ml-64 flex flex-col h-screen overflow-hidden">
        {/* Top Header */}
        <header className="flex-shrink-0 h-16 bg-white border-b border-[#E2E8F0] flex items-center justify-between px-4 sm:px-6">
          <div className="flex items-center w-1/2">
            <button
              onClick={handleDrawerToggle}
              className="mr-4 sm:hidden text-gray-500 hover:text-gray-700"
            >
              <Menu className="h-6 w-6" />
            </button>
            <div className="flex items-center bg-[#F7FAFC] px-4 py-2 rounded-xl w-full max-w-md">
              <Search className="h-4 w-4 text-gray-400 mr-2" />
              <input 
                type="text" 
                placeholder="Search employees, sites, or reports..." 
                className="bg-transparent border-none outline-none w-full text-sm text-[#4A5568]"
              />
            </div>
          </div>
          
          <div className="flex items-center gap-4">
            <button className="text-[#4A5568] hover:text-gray-900 relative">
              <Bell className="h-5 w-5" />
              <span className="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
            </button>
            <button className="text-[#4A5568] hover:text-gray-900">
              <Building2 className="h-5 w-5" />
            </button>
            <div className="h-6 w-px bg-gray-200 mx-2" />
          </div>
        </header>

        {/* Page Content */}
        <main className="flex-1 overflow-y-auto bg-white p-6">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
