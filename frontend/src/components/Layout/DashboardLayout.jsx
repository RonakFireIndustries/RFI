import { Link, useLocation, Outlet, Routes, Route, useNavigate } from 'react-router-dom';
import { useAuthStore } from '../../store/authStore';
import { useState, useEffect, useRef } from 'react';
import api from '../../services/api';
import {
  LayoutDashboard, Users, Calendar, ClipboardList,
  Settings, LogOut, Package, Boxes, Layers, Warehouse, FileText, Bell, HelpCircle, Search, ShoppingCart, Shield, Briefcase, DollarSign, Menu, X,
  MapPin, Clock
} from 'lucide-react';

// Import all pages
import DashboardOverview from '../../pages/Dashboard/DashboardOverview';
import InventoryDashboard from '../../pages/Inventory/InventoryDashboard';
import InventoryDetail from '../../pages/Inventory/InventoryDetail';
import ProductCatalog from '../../pages/Products/ProductCatalog';
import ProductDetail from '../../pages/Products/ProductDetail';
import Warehouses from '../../pages/Warehouses/Warehouses';
import EmployeesPage from '../../pages/Employees/EmployeesPage';
import EmployeeDetail from '../../pages/Employees/EmployeeDetail';
import DepartmentsPage from '../../pages/Departments/DepartmentsPage';
import DesignationsPage from '../../pages/Designations/DesignationsPage';
import PermissionManagement from '../../pages/Admin/PermissionManagement';
import RoleList from '../../pages/Admin/Roles/RoleList';
import RoleForm from '../../pages/Admin/Roles/RoleForm';
import PermissionList from '../../pages/Admin/Permissions/PermissionList';
import PermissionForm from '../../pages/Admin/Permissions/PermissionForm';
import UserAccess from '../../pages/Admin/UserAccess/UserAccess';
import BranchSelector from './BranchSelector';
import PurchaseOrdersPage from '../../pages/Purchases/PurchaseOrdersPage';
import SalesOrdersPage from '../../pages/Sales/SalesOrdersPage';
import SupplierDirectory from '../../pages/Suppliers/SupplierDirectory';
import SupplierProfile from '../../pages/Suppliers/SupplierProfile';
import CustomerDirectory from '../../pages/Customers/CustomerDirectory';
import CustomerProfile from '../../pages/Customers/CustomerProfile';
import CategoryDirectory from '../../pages/Categories/CategoryDirectory';
import InvoiceList from '../../pages/Sales/InvoiceList';
import InvoiceBuilder from '../../pages/Sales/InvoiceBuilder';
import InvoicePreview from '../../pages/Sales/InvoicePreview';
import Attendance from '../../pages/Attendance/Attendance';
import Payroll from '../../pages/Payroll/Payroll';
import Sites from '@/pages/Sites/Sites';
import ShiftListPage from '../../pages/Attendance/ShiftListPage';
import DailyReportsPage from '../../pages/DailyReports/DailyReportsPage';
import DailyReportDetail from '../../pages/DailyReports/DailyReportDetail';
import DailyReportForm from '../../pages/DailyReports/DailyReportForm';
import Leaves from '../../pages/leaves/Leaves';
import LeaveDashboardPage from '../../pages/LeaveManagement/LeaveDashboardPage';
import LeaveTypesPage from '../../pages/LeaveManagement/LeaveTypesPage';
import LeaveBalancesPage from '../../pages/LeaveManagement/LeaveBalancesPage';
import LeaveRequestsPage from '../../pages/LeaveManagement/LeaveRequestsPage';
import LeaveRequestForm from '../../pages/LeaveManagement/LeaveRequestForm';
import LeaveApprovalPage from '../../pages/LeaveManagement/LeaveApprovalPage';

const PlaceholderPage = ({ title }) => (
  <div className="bg-white rounded-lg shadow border border-gray-100 p-6">
    <h2 className="text-xl font-semibold mb-4">{title}</h2>
    <p className="text-gray-500">This page is under construction.</p>
  </div>
);

export default function DashboardLayout() {
  const { user, roles, permissions, logout } = useAuthStore();
  const location = useLocation();
  const [isSidebarOpen, setIsSidebarOpen] = useState(true);

  const navigate = useNavigate();
  const searchRef = useRef(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [searchResults, setSearchResults] = useState([]);
  const [isSearching, setIsSearching] = useState(false);
  const [showResults, setShowResults] = useState(false);

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (searchRef.current && !searchRef.current.contains(event.target)) {
        setShowResults(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  useEffect(() => {
    const fetchSearchResults = async () => {
      if (searchQuery.length < 2) {
        setSearchResults([]);
        return;
      }
      setIsSearching(true);
      try {
        const response = await api.get(`/search?q=${encodeURIComponent(searchQuery)}`);
        setSearchResults(response.data.data || response.data);
      } catch (error) {
        console.error('Search failed', error);
      } finally {
        setIsSearching(false);
      }
    };

    const debounceTimer = setTimeout(() => {
      fetchSearchResults();
    }, 300);

    return () => clearTimeout(debounceTimer);
  }, [searchQuery]);

  const canAccess = (permission) => {
    // If no permission specified, always allow
    if (!permission) return true;
    // If user is Super Admin or Admin, allow all
    if (roles?.includes('Super Admin') || roles?.includes('Admin')) return true;
    // If permissions not yet loaded, show menu items
    if (!permissions || permissions.length === 0) return true;
    // Check if user has specific permission
    return permissions?.includes(permission);
  };

  const menuItems = [
    { name: 'Dashboard', path: '/dashboard', icon: LayoutDashboard, permission: 'view dashboard' },
    { name: 'Employees', path: '/dashboard/employees', icon: Users, permission: 'employee.view' },
    { name: 'Attendance', path: '/dashboard/attendance', icon: Calendar, permission: 'view attendance' },
    { name: 'Shifts', path: '/dashboard/shifts', icon: Clock, permission: 'shift.view' },
    { name: 'Daily Reports', path: '/dashboard/daily-reports', icon: FileText, permission: 'daily-report.view' },
    { name: 'Leave', path: '/dashboard/leave-management', icon: Briefcase, permission: 'leave.view' },
    { name: 'Payroll', path: '/dashboard/payroll', icon: DollarSign, permission: 'view payroll' },
    { name: 'Inventory', path: '/dashboard/inventory', icon: Package, permission: 'view inventory' },
    { name: 'Products', path: '/dashboard/products', icon: Boxes, permission: 'view products' },
    { name: 'Categories', path: '/dashboard/categories', icon: Layers, permission: 'view categories' },
    { name: 'Departments', path: '/dashboard/departments', icon: Layers, permission: 'department.view' },
    { name: 'Designations', path: '/dashboard/designations', icon: Briefcase, permission: 'designation.view' },
    { name: 'Sites', path: '/dashboard/sites', icon: MapPin, permission: 'view sites' },
    { name: 'Warehouses', path: '/dashboard/warehouses', icon: Warehouse, permission: 'view warehouses' },
    { name: 'Customers', path: '/dashboard/customers', icon: Users, permission: 'manage customers' },
    { name: 'Suppliers', path: '/dashboard/suppliers', icon: ShoppingCart, permission: 'manage suppliers' },
    { name: 'Purchases', path: '/dashboard/purchases', icon: ClipboardList, permission: 'manage purchases' },
    { name: 'Sales', path: '/dashboard/sales', icon: ClipboardList, permission: 'manage sales' },
    { name: 'Invoices', path: '/dashboard/invoices', icon: FileText, permission: 'view invoices' },
    { name: 'Reports', path: '/dashboard/reports', icon: ClipboardList, permission: 'view reports' },
    { name: 'Role & Permissions', path: '/dashboard/roles', icon: Shield, permission: 'manage settings' },
    { name: 'User Access', path: '/dashboard/user-access', icon: Shield, permission: 'manage settings' },
  ];

  return (
    <div className="min-h-screen bg-gray-50 flex font-sans text-gray-800">
      {/* Sticky Sidebar */}
      <div className={`fixed left-0 top-0 h-screen bg-white border-r border-gray-200 flex flex-col z-50 transition-all duration-300 ${isSidebarOpen ? 'w-64' : 'w-20'}`}>
        <div className="h-16 flex items-center justify-between px-6 border-b border-gray-200">
          {isSidebarOpen ? (
            <div className="flex flex-col">
              <span className="text-2xl font-bold text-[#1a56db]">RFI</span>
              <span className="text-[10px] text-gray-500 font-medium uppercase tracking-wider">Management Suite</span>
            </div>
          ) : (
            <div className="text-2xl font-bold text-[#1a56db]">R</div>
          )}
          <button
            onClick={() => setIsSidebarOpen(!isSidebarOpen)}
            className="text-gray-400 hover:text-gray-600"
          >
            {isSidebarOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
          </button>
        </div>

        <div className="flex-1 py-4 overflow-y-auto">
          <ul className="space-y-1 px-3">
            {menuItems.filter((item) => canAccess(item.permission)).map((item) => {
              const isActive = location.pathname === item.path || (item.path !== '/dashboard' && location.pathname.startsWith(item.path));
              return (
                <li key={item.name}>
                  <Link
                    to={item.path}
                    title={!isSidebarOpen ? item.name : ''}
                    className={`flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors ${isActive
                      ? 'bg-[#1a56db] text-white'
                      : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
                      }`}
                  >
                    <item.icon className={`w-5 h-5 flex-shrink-0 ${!isSidebarOpen ? '' : 'mr-3'} ${isActive ? 'text-white' : 'text-gray-400'}`} />
                    {isSidebarOpen && <span>{item.name}</span>}
                  </Link>
                </li>
              );
            })}
          </ul>
        </div>

        <div className="p-4 border-t border-gray-200">
          <Link
            to="/dashboard/settings"
            title={!isSidebarOpen ? 'Settings' : ''}
            className={`flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors ${location.pathname.startsWith('/dashboard/settings')
              ? 'bg-[#1a56db] text-white'
              : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
              }`}
          >
            <Settings className="w-5 h-5 flex-shrink-0" />
            {isSidebarOpen && <span className="ml-3">Settings</span>}
          </Link>
        </div>
        <div className="p-4 border-t border-gray-200 bg-gray-50 flex items-center justify-center">
          <div className="w-8 h-8 rounded-full bg-[#e1effe] text-[#1a56db] flex items-center justify-center font-bold flex-shrink-0">
            {user?.name?.substring(0, 2).toUpperCase() || 'JD'}
          </div>
          {isSidebarOpen && (
            <div className="flex-1 min-w-0 ml-3">
              <p className="text-sm font-medium text-gray-900 truncate">
                {user?.name || 'John Doe'}
              </p>
              <p className="text-xs text-gray-500 truncate">
                {roles?.[0] || 'Administrator'}
              </p>
            </div>
          )}
        </div>
      </div>

      {/* Main Content Area with offset for sidebar */}
      <div className={`flex-1 flex flex-col min-w-0 overflow-hidden transition-all duration-300 ${isSidebarOpen ? 'ml-64' : 'ml-20'}`}>
        {/* Top Navbar */}
        <header className="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 z-10 sticky top-0">
          <div className="flex-1 flex">
            <div className="w-full max-w-2xl relative" ref={searchRef}>
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <Search className="h-4 w-4 text-gray-400" />
              </div>
              <input
                className="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-gray-50 placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-[#1a56db] focus:border-[#1a56db] sm:text-sm transition duration-150 ease-in-out"
                placeholder="Search anything (Ctrl+K)"
                type="search"
                value={searchQuery}
                onChange={(e) => {
                  setSearchQuery(e.target.value);
                  setShowResults(true);
                }}
                onFocus={() => {
                  if (searchQuery.length >= 2) setShowResults(true);
                }}
              />

              {showResults && searchQuery.length >= 2 && (
                <div className="absolute top-full left-0 right-0 mt-1 bg-white rounded-md shadow-lg border border-gray-200 overflow-hidden z-50">
                  <div className="max-h-96 overflow-y-auto">
                    {isSearching ? (
                      <div className="p-4 text-center text-sm text-gray-500">Searching...</div>
                    ) : searchResults.length > 0 ? (
                      <ul>
                        {searchResults.map((result, idx) => (
                          <li key={`${result.type}-${result.id}-${idx}`}>
                            <button
                              className="w-full text-left px-4 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-b-0 focus:outline-none focus:bg-gray-50"
                              onClick={() => {
                                setShowResults(false);
                                setSearchQuery('');
                                navigate(result.url);
                              }}
                            >
                              <div className="flex justify-between items-start">
                                <div>
                                  <p className="text-sm font-medium text-gray-900">{result.title}</p>
                                  <p className="text-xs text-gray-500 mt-0.5">{result.subtitle}</p>
                                </div>
                                <span className="text-[10px] uppercase font-bold tracking-wider px-2 py-1 bg-gray-100 text-gray-600 rounded">
                                  {result.type}
                                </span>
                              </div>
                            </button>
                          </li>
                        ))}
                      </ul>
                    ) : (
                      <div className="p-4 text-center text-sm text-gray-500">No results found for "{searchQuery}"</div>
                    )}
                  </div>
                </div>
              )}
            </div>
          </div>
          <div className="ml-4 flex items-center space-x-4">
            <BranchSelector />
            <button className="text-gray-400 hover:text-gray-500">
              <span className="sr-only">View notifications</span>
              <Bell className="h-5 w-5" />
            </button>
            <button className="text-gray-400 hover:text-gray-500">
              <span className="sr-only">Help</span>
              <HelpCircle className="h-5 w-5" />
            </button>
            <div className="relative border-l border-gray-200 pl-4">
              <button
                onClick={logout}
                className="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none"
              >
                <LogOut className="h-5 w-5 mr-1" />
                <span className="hidden sm:block">Logout</span>
              </button>
            </div>
          </div>
        </header>

        {/* Page Content */}
        <main className="flex-1 overflow-y-auto bg-gray-50/50 p-6">
          <div className="max-w-7xl mx-auto">
            <Routes>
              <Route index element={<DashboardOverview />} />
              <Route path="inventory" element={<InventoryDashboard />} />
              <Route path="inventory/:id" element={<InventoryDetail />} />
              <Route path="products" element={<ProductCatalog />} />
              <Route path="products/:id" element={<ProductDetail />} />
              <Route path="warehouses" element={<Warehouses />} />
              <Route path="employees" element={<EmployeesPage />} />
              <Route path="employees/:id" element={<EmployeeDetail />} />
              <Route path="departments" element={<DepartmentsPage />} />
              <Route path="designations" element={<DesignationsPage />} />
              <Route path="attendance" element={<Attendance />} />
              <Route path="shifts" element={<ShiftListPage />} />
              <Route path="daily-reports" element={<DailyReportsPage />} />
              <Route path="daily-reports/new" element={<DailyReportForm />} />
              <Route path="daily-reports/:id" element={<DailyReportDetail />} />

              <Route path="leave-management" element={<LeaveDashboardPage />} />
              <Route path="leave-management/types" element={<LeaveTypesPage />} />
              <Route path="leave-management/balances" element={<LeaveBalancesPage />} />
              <Route path="leave-management/requests" element={<LeaveRequestsPage />} />
              <Route path="leave-management/requests/new" element={<LeaveRequestForm />} />
              <Route path="leave-management/requests/:id" element={<LeaveApprovalPage />} />

              <Route path="payroll" element={<Payroll />} />
              <Route path="branches" element={<PlaceholderPage title="Branches" />} />
              <Route path="categories" element={<CategoryDirectory />} />
              <Route path="customers" element={<CustomerDirectory />} />
              <Route path="customers/:id" element={<CustomerProfile />} />
              <Route path="suppliers" element={<SupplierDirectory />} />
              <Route path="suppliers/:id" element={<SupplierProfile />} />
              <Route path="purchases" element={<PurchaseOrdersPage />} />
              <Route path="sales" element={<SalesOrdersPage />} />
              <Route path="invoices" element={<InvoiceList />} />
              <Route path="invoices/create" element={<InvoiceBuilder />} />
              <Route path="invoices/:id" element={<InvoicePreview />} />
              <Route path="reports" element={<PlaceholderPage title="Reports" />} />
              <Route path="settings" element={<PlaceholderPage title="Settings" />} />
              <Route path="permissions" element={<PermissionManagement />} />
              <Route path="roles" element={<RoleList />} />
              <Route path="roles/create" element={<RoleForm />} />
              <Route path="roles/:id/edit" element={<RoleForm />} />
              <Route path="permissions-list" element={<PermissionList />} />
              <Route path="permissions-list/create" element={<PermissionForm />} />
              <Route path="permissions-list/:id/edit" element={<PermissionForm />} />
              <Route path="user-access" element={<UserAccess />} />
              <Route path="sites" element={<Sites />} />
            </Routes>
          </div>
        </main>
      </div>
    </div>
  );
}
