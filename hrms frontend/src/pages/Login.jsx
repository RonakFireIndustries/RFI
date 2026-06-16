import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useDispatch, useSelector } from 'react-redux';
import { Eye, EyeOff, User, Lock, Loader2 } from 'lucide-react';
import { loginUser } from '../store/slices/authSlice';

export default function Login() {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const { loading, error } = useSelector(state => state.auth);
  
  const [showPassword, setShowPassword] = useState(false);
  const [credentials, setCredentials] = useState({
    email: '',
    password: ''
  });

  const handleChange = (e) => {
    setCredentials({ ...credentials, [e.target.name]: e.target.value });
  };

  const handleLogin = async (e) => {
    e.preventDefault();
    const resultAction = await dispatch(loginUser(credentials));
    if (loginUser.fulfilled.match(resultAction)) {
      navigate('/dashboard');
    }
  };

  return (
    <form onSubmit={handleLogin} className="flex flex-col gap-6 w-full">
      {error && (
        <div className="bg-red-50 text-red-600 p-3 rounded-lg text-sm font-semibold border border-red-200">
          {error}
        </div>
      )}
      {/* Email Input */}
      <div>
        <label className="block text-sm font-semibold text-[#4A5568] mb-1 text-left">
          Email or Employee ID
        </label>
        <div className="relative">
          <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#A0AEC0]">
            <User className="h-5 w-5" />
          </div>
          <input
            name="email"
            type="text"
            placeholder="e.g. EMP-24019"
            value={credentials.email}
            onChange={handleChange}
            required
            className="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
          />
        </div>
      </div>

      {/* Password Input */}
      <div>
        <div className="flex justify-between items-center mb-1">
          <label className="block text-sm font-semibold text-[#4A5568]">
            Password
          </label>
          <span className="text-sm font-semibold text-[#ED8936] cursor-pointer hover:underline">
            Forgot Password?
          </span>
        </div>
        <div className="relative">
          <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#A0AEC0]">
            <Lock className="h-5 w-5" />
          </div>
          <input
            name="password"
            type={showPassword ? 'text' : 'password'}
            placeholder="••••••••"
            value={credentials.password}
            onChange={handleChange}
            required
            className="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
          />
          <button
            type="button"
            className="absolute inset-y-0 right-0 pr-3 flex items-center text-[#A0AEC0] hover:text-gray-700"
            onClick={() => setShowPassword(!showPassword)}
          >
            {showPassword ? <EyeOff className="h-5 w-5" /> : <Eye className="h-5 w-5" />}
          </button>
        </div>
      </div>

      {/* Remember Me */}
      <div className="flex items-center -mt-2">
        <input 
          type="checkbox" 
          id="remember" 
          className="w-4 h-4 mr-2 border-[#CBD5E0] rounded text-[#0B1B36] focus:ring-[#0B1B36]" 
        />
        <label htmlFor="remember" className="text-sm text-[#4A5568] cursor-pointer select-none">
          Remember Me
        </label>
      </div>

      {/* Submit Button */}
      <button
        type="submit"
        disabled={loading}
        className="w-full mt-2 py-3 px-4 bg-[#0B1B36] text-white font-semibold rounded-lg hover:bg-[#081428] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0B1B36] transition-colors disabled:opacity-70 disabled:cursor-not-allowed flex justify-center items-center"
      >
        {loading ? <Loader2 className="h-6 w-6 animate-spin" /> : 'Sign In'}
      </button>
    </form>
  );
}
