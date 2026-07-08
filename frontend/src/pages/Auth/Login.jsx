import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuthStore } from '../../store/authStore';
import { authService } from '../../services/authService';
import { Eye, EyeOff, Lock, Mail } from 'lucide-react';

import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";

export default function Login() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [rememberMe, setRememberMe] = useState(false);
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  const navigate = useNavigate();
  const setAuth = useAuthStore((state) => state.setAuth);

  const handleLogin = async (e) => {
    e.preventDefault();
    setError('');
    setIsLoading(true);

    try {
      const response = await authService.login({ email, password });

      if (response.data.token && response.data.user) {
        const user = response.data.user;
        const roles = (user.roles || []).map(r => typeof r === 'string' ? r : r.name);
        const permissions = (user.permissions || []).map(p => typeof p === 'string' ? p : p.name);
        setAuth(user, response.data.token, roles, permissions);

        if (rememberMe) {
          localStorage.setItem('remembered-email', email);
        } else {
          localStorage.removeItem('remembered-email');
        }

        navigate('/dashboard');
      } else {
        setError('Login failed: Invalid response format');
      }
    } catch (err) {
      setError(err.response?.data?.message || err.message || 'Failed to login. Check your credentials.');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-00 p-4 safe-bottom"
      style={{ paddingTop: 'env(safe-area-inset-top, 1rem)' }}>
      {/* Decorative background shapes */}
      <div className="absolute inset-0 overflow-hidden pointer-events-none">
        <div className="absolute -top-40 -right-40 w-80 h-80 rounded-full bg-blue-200/30 dark:bg-blue-800/10 blur-3xl" />
        <div className="absolute -bottom-40 -left-40 w-80 h-80 rounded-full bg-indigo-200/30 dark:bg-indigo-800/10 blur-3xl" />
        <div className="absolute top-1/3 -left-20 w-40 h-40 rounded-full bg-cyan-200/20 dark:bg-cyan-800/10 blur-2xl" />
      </div>

      <Card className="relative w-full max-w-md border-none shadow-2xl shadow-blue-900/10 dark:shadow-black/30 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm">
        {/* Branding */}
        <div className="pt-10 pb-2 px-8 text-center">
          <div className="flex justify-center mb-4">
            <img src="/logo.png" alt="RFI" className="h-24 w-auto" />
          </div>
          <h1 className="text-lg font-bold text-gray-900 dark:text-gray-100 tracking-tight">
            Ronak Fire Industries
          </h1>
          <p className="text-xs text-gray-500 dark:text-gray-400 mt-0.5 font-medium uppercase tracking-wider">
            RFI Management Portal
          </p>
        </div>

        {/* Welcome section */}
        <div className="px-8 pt-4 pb-2 text-center">
          <h2 className="text-xl font-semibold text-gray-900 dark:text-gray-100">
            Welcome Back
          </h2>
          <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Sign in to your account to continue
          </p>
        </div>

        <form onSubmit={handleLogin}>
          <CardContent className="space-y-4 px-8 pb-8 pt-4">
            {error && (
              <div className="flex items-center gap-2 p-3 text-sm text-red-600 bg-red-50 dark:bg-red-900/30 dark:text-red-400 rounded-lg border border-red-200 dark:border-red-800/50">
                <svg className="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                  <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                </svg>
                <span>{error}</span>
              </div>
            )}

            <div className="space-y-1.5">
              <Label htmlFor="email" className="text-sm font-medium text-gray-700 dark:text-gray-300">
                Email Address
              </Label>
              <div className="relative">
                <Mail className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
                <Input
                  id="email"
                  type="email"
                  placeholder="admin@example.com"
                  required
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  className="pl-10 h-11 bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500/20 text-gray-900 dark:text-gray-100 placeholder:text-gray-100 dark:placeholder:text-gray-100"
                />
              </div>
            </div>

            <div className="space-y-1.5">
              <Label htmlFor="password" className="text-sm font-medium text-gray-700 dark:text-gray-300">
                Password
              </Label>
              <div className="relative">
                <Lock className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none z-10" />
                <Input
                  id="password"
                  type={showPassword ? 'text' : 'password'}
                  placeholder="Enter your password"
                  required
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  className="pl-10 pr-10 h-11 bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500/20 text-gray-900 dark:text-gray-100 placeholder:text-gray-100 dark:placeholder:text-gray-100"
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                  tabIndex={-1}
                >
                  {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                </button>
              </div>
            </div>

            {/* Remember Me & Forgot Password */}
            <div className="flex items-center justify-between pt-1">
              <label className="flex items-center gap-2 cursor-pointer select-none">
                <input
                  type="checkbox"
                  checked={rememberMe}
                  onChange={(e) => setRememberMe(e.target.checked)}
                  className="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500/30 cursor-pointer"
                />
                <span className="text-sm text-gray-600 dark:text-gray-400">Remember me</span>
              </label>
              <button
                type="button"
                onClick={() => {/* TODO: navigate to forgot password */ }}
                className="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition-colors"
              >
                Forgot Password?
              </button>
            </div>

            {/* Submit */}
            <Button
              type="submit"
              className="w-full h-11 mt-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold rounded-lg shadow-lg shadow-blue-600/20 hover:shadow-blue-600/30 transition-all duration-200 disabled:opacity-60"
              disabled={isLoading}
            >
              {isLoading ? (
                <span className="flex items-center gap-2">
                  <svg className="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                  </svg>
                  Signing in...
                </span>
              ) : (
                'Sign In'
              )}
            </Button>
          </CardContent>
        </form>
      </Card>
    </div>
  );
}
