import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { Provider as ReduxProvider } from 'react-redux';
import { store } from './store';

// Layouts
import DashboardLayout from './layouts/DashboardLayout';
import AuthLayout from './layouts/AuthLayout';

import Dashboard from './pages/Dashboard';
import Login from './pages/Login';
import Performance from './pages/Performance';
import EmployeesList from './pages/EmployeesList';
import SiteManagement from './pages/SiteManagement';
import LeaveManagement from './pages/LeaveManagement';
import PayrollManagement from './pages/PayrollManagement';
import EmployeeProfile from './pages/EmployeeProfile';
import AttendanceDashboard from './pages/AttendanceDashboard';
import Announcements from './pages/Announcements';
import Settings from './pages/Settings';

function App() {
  return (
    <ReduxProvider store={store}>
      <Router>
        <Routes>
          <Route element={<AuthLayout />}>
            <Route path="/login" element={<Login />} />
          </Route>

          <Route element={<DashboardLayout />}>
            <Route path="/" element={<Navigate to="/dashboard" replace />} />
            <Route path="/dashboard" element={<Dashboard />} />
            <Route path="/performance" element={<Performance />} />
            <Route path="/employees" element={<EmployeesList />} />
            <Route path="/employees/:id" element={<EmployeeProfile />} />
            <Route path="/sites" element={<SiteManagement />} />
            <Route path="/attendance" element={<AttendanceDashboard />} /> 
            <Route path="/leave" element={<LeaveManagement />} />
            <Route path="/payroll" element={<PayrollManagement />} />
            <Route path="/announcements" element={<Announcements />} />
            <Route path="/settings" element={<Settings />} />
          </Route>
        </Routes>
      </Router>
    </ReduxProvider>
  );
}

export default App;
