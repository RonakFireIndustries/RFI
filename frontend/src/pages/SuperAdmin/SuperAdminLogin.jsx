import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Shield, Lock, Mail, Loader2, Database } from 'lucide-react';
import { useAuthStore } from '../../store/authStore';
import api from '../../services/api';

export default function SuperAdminLogin() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const navigate = useNavigate();
  const setAuth = useAuthStore((state) => state.setAuth);

  const handleLogin = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    try {
      const response = await api.post('/login', { email, password });
      const { user, access_token, roles, permissions } = response.data;
      console.log(response.data);
            
      if (roles.includes('Super Admin') || user.email === 'superadmin@example.com') {
        setAuth(user, access_token, roles || [], permissions || []);
        navigate('/superadmin/dashboard');
      } else {
        setError('Access Denied. You are not a Super Admin.');
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to authenticate');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-gray-900 flex items-center justify-center p-4">
      <div className="max-w-md w-full bg-gray-800 rounded-xl shadow-2xl p-8 border border-gray-700 relative overflow-hidden">
        {/* Top gradient accent */}
        <div className="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-red-500 via-yellow-500 to-green-500"></div>
        
        <div className="flex flex-col items-center mb-8">
          <div className="w-16 h-16 bg-gray-900 rounded-2xl flex items-center justify-center border border-gray-700 shadow-inner mb-4">
            <Shield className="w-8 h-8 text-red-500" />
          </div>
          <h1 className="text-2xl font-bold text-white tracking-tight">Super Admin Portal</h1>
          <p className="text-gray-400 text-sm mt-2 text-center">
            Database access restricted to authorized personnel only.
          </p>
        </div>

        {error && (
          <div className="bg-red-500/10 border border-red-500/50 rounded-lg p-3 mb-6 flex items-center">
            <p className="text-red-400 text-sm">{error}</p>
          </div>
        )}

        <form onSubmit={handleLogin} className="space-y-5">
          <div>
            <label className="block text-sm font-medium text-gray-300 mb-1">Root Email</label>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <Mail className="h-5 w-5 text-gray-500" />
              </div>
              <input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
                className="block w-full pl-10 pr-3 py-2.5 border border-gray-600 rounded-lg leading-5 bg-gray-900 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm transition-colors"
                placeholder="superadmin@example.com"
              />
            </div>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-300 mb-1">Root Password</label>
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <Lock className="h-5 w-5 text-gray-500" />
              </div>
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
                className="block w-full pl-10 pr-3 py-2.5 border border-gray-600 rounded-lg leading-5 bg-gray-900 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm transition-colors"
                placeholder="••••••••"
              />
            </div>
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors mt-6"
          >
            {loading ? <Loader2 className="w-5 h-5 animate-spin" /> : 'Initialize Database Access'}
          </button>
        </form>
      </div>
    </div>
  );
}
