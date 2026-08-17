import { useState, useEffect } from 'react';
import api from '../../services/api';
import { useAuthStore } from '../../store/authStore';
import { Loader, User, Key, Save } from 'lucide-react';

export default function UserSettingsPage() {
  const { user, setUser } = useAuthStore();

  // Profile Form State
  const [profileForm, setProfileForm] = useState({
    name: user?.name || '',
    email: user?.email || '',
  });
  const [profileSaving, setProfileSaving] = useState(false);

  // Password Form State
  const [passwordForm, setPasswordForm] = useState({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
  });
  const [passwordSaving, setPasswordSaving] = useState(false);

  const handleProfileChange = (e) => {
    setProfileForm(prev => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handlePasswordChange = (e) => {
    setPasswordForm(prev => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handleProfileSubmit = async (e) => {
    e.preventDefault();
    try {
      setProfileSaving(true);
      const { data } = await api.put('/profile', profileForm);
      setUser(data.user); // Update auth store with new user data
      alert('Profile updated successfully!');
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to update profile');
    } finally {
      setProfileSaving(false);
    }
  };

  const handlePasswordSubmit = async (e) => {
    e.preventDefault();
    if (passwordForm.new_password !== passwordForm.new_password_confirmation) {
      alert('New passwords do not match!');
      return;
    }
    try {
      setPasswordSaving(true);
      await api.post('/change-password', passwordForm);
      alert('Password changed successfully!');
      setPasswordForm({
        current_password: '',
        new_password: '',
        new_password_confirmation: '',
      });
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to change password');
    } finally {
      setPasswordSaving(false);
    }
  };

  const inputClass = 'w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none';
  const labelClass = 'block text-xs font-medium text-gray-600 mb-1';

  return (
    <div className="p-6 max-w-4xl mx-auto">
      <h1 className="text-xl font-bold text-gray-800 mb-6">User Settings</h1>
      <p className="text-sm text-gray-500 mb-8">
        Manage your profile settings, change your password, and customize your app experience.
      </p>

      <div className="space-y-8">
        {/* Profile Settings */}
        <section className="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
          <div className="flex items-center gap-2 mb-4">
            <User className="w-5 h-5 text-gray-500" />
            <h2 className="text-sm font-bold text-gray-700 uppercase tracking-wider">Profile Information</h2>
          </div>
          <form onSubmit={handleProfileSubmit}>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div>
                <label className={labelClass}>Full Name</label>
                <input
                  type="text"
                  name="name"
                  value={profileForm.name}
                  onChange={handleProfileChange}
                  className={inputClass}
                  required
                />
              </div>
              <div>
                <label className={labelClass}>Email Address</label>
                <input
                  type="email"
                  name="email"
                  value={profileForm.email}
                  onChange={handleProfileChange}
                  className={inputClass}
                  required
                />
              </div>
            </div>
            <div className="flex justify-end">
              <button
                type="submit"
                disabled={profileSaving}
                className="flex items-center gap-2 px-6 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary/90 disabled:opacity-50 transition-colors"
              >
                {profileSaving ? <Loader className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />}
                {profileSaving ? 'Saving...' : 'Save Profile'}
              </button>
            </div>
          </form>
        </section>

        {/* Change Password */}
        <section className="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
          <div className="flex items-center gap-2 mb-4">
            <Key className="w-5 h-5 text-gray-500" />
            <h2 className="text-sm font-bold text-gray-700 uppercase tracking-wider">Change Password</h2>
          </div>
          <form onSubmit={handlePasswordSubmit}>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div className="md:col-span-2">
                <label className={labelClass}>Current Password</label>
                <input
                  type="password"
                  name="current_password"
                  value={passwordForm.current_password}
                  onChange={handlePasswordChange}
                  className={inputClass}
                  required
                />
              </div>
              <div>
                <label className={labelClass}>New Password</label>
                <input
                  type="password"
                  name="new_password"
                  value={passwordForm.new_password}
                  onChange={handlePasswordChange}
                  className={inputClass}
                  minLength="8"
                  required
                />
              </div>
              <div>
                <label className={labelClass}>Confirm New Password</label>
                <input
                  type="password"
                  name="new_password_confirmation"
                  value={passwordForm.new_password_confirmation}
                  onChange={handlePasswordChange}
                  className={inputClass}
                  minLength="8"
                  required
                />
              </div>
            </div>
            <div className="flex justify-end">
              <button
                type="submit"
                disabled={passwordSaving}
                className="flex items-center gap-2 px-6 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary/90 disabled:opacity-50 transition-colors"
              >
                {passwordSaving ? <Loader className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />}
                {passwordSaving ? 'Changing...' : 'Change Password'}
              </button>
            </div>
          </form>
        </section>
      </div>
    </div>
  );
}
