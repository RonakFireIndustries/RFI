import { useState, useEffect } from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { ThemeProvider } from './contexts/ThemeContext';
import UpdatePrompt from './components/Pwa/UpdatePrompt';
import SplashScreen from './components/Pwa/SplashScreen';
import { useAuthStore } from './store/authStore';
import Login from './pages/Auth/Login';

import DashboardLayout from './components/Layout/DashboardLayout';

// Protected Route Wrapper
const ProtectedRoute = ({ children }) => {
  const isAuthenticated = useAuthStore((state) => state.isAuthenticated);
  const token = useAuthStore((state) => state.token);

  const storedAuth = typeof window !== 'undefined'
    ? JSON.parse(localStorage.getItem('auth-storage') || 'null')
    : null;
  const hasPersistedToken = Boolean(token || storedAuth?.state?.token);

  if (!isAuthenticated && hasPersistedToken) {
    return null;
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  return children;
};

// Placeholder for missing pages to avoid errors
const PlaceholderPage = ({ title }) => (
  <div className="bg-white rounded-lg shadow border border-gray-100 p-6">
    <h2 className="text-xl font-semibold mb-4">{title}</h2>
    <p className="text-gray-500">This page is under construction.</p>
  </div>
);

function App() {
  const [ready, setReady] = useState(false);
  useEffect(() => { setReady(true); }, []);

  return (
    <ThemeProvider>
      <BrowserRouter>
        {!ready && <SplashScreen />}
        <UpdatePrompt />
        <Routes>
          <Route path="/login" element={<Login />} />

          {/* Protected Routes */}
          <Route
            path="/dashboard/*"
            element={
              <ProtectedRoute>
                <DashboardLayout />
              </ProtectedRoute>
            }
          />

          {/* Default redirect */}
          <Route path="*" element={<Navigate to="/dashboard" replace />} />
        </Routes>
      </BrowserRouter>
    </ThemeProvider>
  );
}

export default App;
