import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { useAuthStore } from './store/authStore';
import Login from './pages/Auth/Login';

// Layout
import DashboardLayout from './components/Layout/DashboardLayout';

// Pages
import DashboardOverview from './pages/Dashboard/DashboardOverview';
import InventoryDashboard from './pages/Inventory/InventoryDashboard';
import ProductCatalog from './pages/Products/ProductCatalog';
import Warehouses from './pages/Warehouses/Warehouses';
import EmployeesPage from './pages/Employees/EmployeesPage';
import PermissionManagement from './pages/Admin/PermissionManagement';

// Super Admin
import SuperAdminLogin from './pages/SuperAdmin/SuperAdminLogin';
import SuperAdminDashboard from './pages/SuperAdmin/SuperAdminDashboard';

// Protected Route Wrapper
const ProtectedRoute = ({ children, permission, requiredRole }) => {
  const isAuthenticated = useAuthStore((state) => state.isAuthenticated);
  const permissions = useAuthStore((state) => state.permissions);
  const roles = useAuthStore((state) => state.roles);
  const user = useAuthStore((state) => state.user);
  const token = useAuthStore((state) => state.token);

  const storedAuth = typeof window !== 'undefined'
    ? JSON.parse(localStorage.getItem('auth-storage') || 'null')
    : null;
  const hasPersistedToken = Boolean(token || storedAuth?.state?.token);

  const hasRequiredRole = !requiredRole
    || roles.includes(requiredRole)
    || (requiredRole === 'Super Admin' && user?.email === 'superadmin@example.com');

  const canAccess = hasRequiredRole && (!permission || roles.includes('Super Admin') || roles.includes('Admin') || permissions.includes(permission));

  if (!isAuthenticated && hasPersistedToken) {
    return null;
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  if (!canAccess) {
    return <Navigate to="/dashboard" replace />;
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
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/login" element={<Login />} />
        <Route path="/superadmin" element={<SuperAdminLogin />} />
        
        {/* Protected Super Admin Route */}
        <Route 
          path="/superadmin/dashboard" 
          element={
            <ProtectedRoute requiredRole="Super Admin">
              <SuperAdminDashboard />
            </ProtectedRoute>
          } 
        />
        
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
  );
}

export default App;
