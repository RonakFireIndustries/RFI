import { useState } from 'react';
import { X, Eye, EyeOff, Lock, Loader, CheckCircle, AlertCircle } from 'lucide-react';
import api from '../../services/api';

export default function ChangePasswordModal({ open, onClose }) {
  const [form, setForm] = useState({ current_password: '', new_password: '', new_password_confirmation: '' });
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(false);
  const [show, setShow] = useState({ current: false, new: false, confirm: false });

  if (!open) return null;

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess(false);
    if (form.new_password.length < 8) { setError('Password must be at least 8 characters'); return; }
    if (form.new_password !== form.new_password_confirmation) { setError('Passwords do not match'); return; }
    setSaving(true);
    try {
      await api.post('/change-password', form);
      setSuccess(true);
      setForm({ current_password: '', new_password: '', new_password_confirmation: '' });
      setTimeout(() => onClose(), 2000);
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to change password');
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" onClick={(e) => e.target === e.currentTarget && onClose()}>
      <div className="bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-md overflow-hidden animate-slide-up">
        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100">
          <div className="flex items-center gap-2">
            <Lock className="w-5 h-5 text-blue-600" />
            <h2 className="text-lg font-bold text-gray-900">Change Password</h2>
          </div>
          <button onClick={onClose} className="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
            <X className="w-5 h-5" />
          </button>
        </div>

        <form onSubmit={handleSubmit} className="p-6 space-y-4">
          {error && (
            <div className="flex items-center gap-2 p-3 text-sm text-red-600 bg-red-50 rounded-lg border border-red-100">
              <AlertCircle className="w-4 h-4 shrink-0" />
              {error}
            </div>
          )}
          {success && (
            <div className="flex items-center gap-2 p-3 text-sm text-green-600 bg-green-50 rounded-lg border border-green-100">
              <CheckCircle className="w-4 h-4 shrink-0" />
              Password changed successfully
            </div>
          )}

          <div>
            <label className="block text-xs font-medium text-gray-600 mb-1.5">Current Password</label>
            <div className="relative">
              <input type={show.current ? 'text' : 'password'} required value={form.current_password}
                onChange={e => setForm(p => ({ ...p, current_password: e.target.value }))}
                className="w-full rounded-lg border border-gray-300 pl-3 pr-10 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none" />
              <button type="button" onClick={() => setShow(p => ({ ...p, current: !p.current }))} className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                {show.current ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
              </button>
            </div>
          </div>

          <div>
            <label className="block text-xs font-medium text-gray-600 mb-1.5">New Password</label>
            <div className="relative">
              <input type={show.new ? 'text' : 'password'} required value={form.new_password} minLength={8}
                onChange={e => setForm(p => ({ ...p, new_password: e.target.value }))}
                className="w-full rounded-lg border border-gray-300 pl-3 pr-10 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none" />
              <button type="button" onClick={() => setShow(p => ({ ...p, new: !p.new }))} className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                {show.new ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
              </button>
            </div>
            <p className="text-xs text-gray-400 mt-1">Minimum 8 characters</p>
          </div>

          <div>
            <label className="block text-xs font-medium text-gray-600 mb-1.5">Confirm New Password</label>
            <div className="relative">
              <input type={show.confirm ? 'text' : 'password'} required value={form.new_password_confirmation}
                onChange={e => setForm(p => ({ ...p, new_password_confirmation: e.target.value }))}
                className="w-full rounded-lg border border-gray-300 pl-3 pr-10 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none" />
              <button type="button" onClick={() => setShow(p => ({ ...p, confirm: !p.confirm }))} className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                {show.confirm ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
              </button>
            </div>
          </div>

          <div className="flex items-center justify-end gap-3 pt-2">
            <button type="button" onClick={onClose} className="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors">
              Cancel
            </button>
            <button type="submit" disabled={saving}
              className="inline-flex items-center gap-1.5 px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
              {saving ? <Loader className="w-4 h-4 animate-spin" /> : <Lock className="w-4 h-4" />}
              {saving ? 'Changing...' : 'Change Password'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
