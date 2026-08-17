import { useState } from 'react';
import ChangePasswordModal from '../../components/Settings/ChangePasswordModal';
import CompanySettingsPage from './CompanySettingsPage';
import {
  Lock, Building2, ChevronRight, Shield, User, Globe, Smartphone
} from 'lucide-react';

const tabs = [
  { id: 'account', label: 'Account', icon: Lock, desc: 'Password, security' },
  { id: 'company', label: 'Company', icon: Building2, desc: 'Business info, bank details, signature' },
];

export default function SettingsPage() {
  const [activeTab, setActiveTab] = useState('account');
  const [showPasswordModal, setShowPasswordModal] = useState(false);

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-foreground">Settings</h1>
        <p className="text-sm text-muted-foreground mt-1">Manage your application preferences and company configuration.</p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-[240px_1fr] gap-6">
        {/* Sidebar tabs */}
        <div className="space-y-1">
          {tabs.map(tab => {
            const Icon = tab.icon;
            const isActive = activeTab === tab.id;
            return (
              <button
                key={tab.id}
                onClick={() => setActiveTab(tab.id)}
                className={`w-full text-left px-4 py-3 rounded-xl transition-colors ${
                  isActive
                    ? 'bg-primary/10 border border-primary/30'
                    : 'hover:bg-muted border border-transparent'
                }`}
              >
                <div className="flex items-center gap-3">
                  <div className={`shrink-0 w-9 h-9 rounded-lg flex items-center justify-center ${
                    isActive ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'
                  }`}>
                    <Icon className="w-4 h-4" />
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className={`text-sm font-semibold ${isActive ? 'text-primary' : 'text-foreground'}`}>
                      {tab.label}
                    </p>
                    <p className="text-xs text-muted-foreground truncate">{tab.desc}</p>
                  </div>
                  <ChevronRight className={`w-4 h-4 shrink-0 ${isActive ? 'text-primary' : 'text-border'}`} />
                </div>
              </button>
            );
          })}
        </div>

        {/* Content */}
        <div>
          {activeTab === 'account' && (
            <div className="space-y-4">
              <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h2 className="text-lg font-bold text-gray-900">Account Security</h2>
                <p className="text-sm text-gray-500 mt-1">Manage your password and account settings.</p>
              </div>

              <div className="bg-white rounded-xl border border-gray-200 shadow-sm divide-y divide-gray-100">
                <div className="p-6 flex items-center justify-between">
                  <div className="flex items-start gap-3">
                    <div className="shrink-0 w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center">
                      <Lock className="w-5 h-5 text-amber-600" />
                    </div>
                    <div>
                      <p className="text-sm font-semibold text-gray-900">Password</p>
                      <p className="text-xs text-gray-400 mt-0.5">Update your login password</p>
                    </div>
                  </div>
                  <button
                    onClick={() => setShowPasswordModal(true)}
                    className="shrink-0 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                  >
                    Change Password
                  </button>
                </div>

                <div className="p-6 flex items-center justify-between">
                  <div className="flex items-start gap-3">
                    <div className="shrink-0 w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                      <User className="w-5 h-5 text-blue-600" />
                    </div>
                    <div>
                      <p className="text-sm font-semibold text-gray-900">Profile Information</p>
                      <p className="text-xs text-gray-400 mt-0.5">Name, email, and other profile details</p>
                    </div>
                  </div>
                  <button className="shrink-0 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                    Edit Profile
                  </button>
                </div>

                <div className="p-6 flex items-center justify-between opacity-50 pointer-events-none">
                  <div className="flex items-start gap-3">
                    <div className="shrink-0 w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center">
                      <Shield className="w-5 h-5 text-purple-600" />
                    </div>
                    <div>
                      <p className="text-sm font-semibold text-gray-900">Two-Factor Authentication</p>
                      <p className="text-xs text-gray-400 mt-0.5">Add an extra layer of security</p>
                    </div>
                  </div>
                  <span className="text-[10px] font-medium px-2 py-1 rounded-full bg-gray-100 text-gray-500">Coming Soon</span>
                </div>

                <div className="p-6 flex items-center justify-between opacity-50 pointer-events-none">
                  <div className="flex items-start gap-3">
                    <div className="shrink-0 w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                      <Globe className="w-5 h-5 text-green-600" />
                    </div>
                    <div>
                      <p className="text-sm font-semibold text-gray-900">Sessions</p>
                      <p className="text-xs text-gray-400 mt-0.5">Manage active login sessions</p>
                    </div>
                  </div>
                  <span className="text-[10px] font-medium px-2 py-1 rounded-full bg-gray-100 text-gray-500">Coming Soon</span>
                </div>
              </div>
            </div>
          )}

          {activeTab === 'company' && <CompanySettingsPage nested />}
        </div>
      </div>

      <ChangePasswordModal open={showPasswordModal} onClose={() => setShowPasswordModal(false)} />
    </div>
  );
}
