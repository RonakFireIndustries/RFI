import { Link, useLocation, Outlet, Routes, Route, useNavigate } from 'react-router-dom';
import { useAuthStore } from '../../store/authStore';
import { useState, useEffect, useRef } from 'react';
import api from '../../services/api';
import {
  Settings, LogOut, Bell, HelpCircle, Search, Menu, X,
} from 'lucide-react';
import { menuCategories } from '../../config/sidebarMenu';

// Import all pages
import DashboardRenderer from '../Dashboard/DashboardRenderer';
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
import EmployeeDashboard from '../../pages/Attendance/EmployeeDashboard';
import EmployeeAttendanceHistory from '../../pages/Attendance/EmployeeAttendanceHistory';
import SalaryStructuresPage from '../../pages/Payroll/SalaryStructuresPage';
import PayrollPeriodsPage from '../../pages/Payroll/PayrollPeriodsPage';
import PayrollGenerationPage from '../../pages/Payroll/PayrollGenerationPage';
import EmployeePayrollPage from '../../pages/Payroll/EmployeePayrollPage';
import PayslipPage from '../../pages/Payroll/PayslipPage';
import Sites from '@/pages/Sites/Sites';
import ShiftListPage from '../../pages/Attendance/ShiftListPage';
import DailyReportsPage from '../../pages/DailyReports/DailyReportsPage';
import DailyReportDetail from '../../pages/DailyReports/DailyReportDetail';
import DailyReportForm from '../../pages/DailyReports/DailyReportForm';
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
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  useEffect(() => {
    if (isMobileMenuOpen) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }
    return () => { document.body.style.overflow = ''; };
  }, [isMobileMenuOpen]);

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
    if (!permission) return true;
    const adminRoles = roles?.map(r => r.toLowerCase()) || [];
    if (adminRoles.some(r => ['super admin', 'admin', 'system admin', 'superadmin'].includes(r))) return true;
    if (!permissions || permissions.length === 0) return false;
    return permissions?.includes(permission);
  };

    return (
    <div className="min-h-screen bg-gray-50 flex font-sans text-gray-800">
      {/* Mobile backdrop */}
      {isMobileMenuOpen && (
        <div
          className="fixed inset-0 bg-black/50 z-40 md:hidden transition-opacity"
          onClick={() => setIsMobileMenuOpen(false)}
        />
      )}

      {/* Sticky Sidebar */}
      <div className={`
        fixed left-0 top-0 h-screen bg-white border-r border-gray-200 flex flex-col z-50 transition-all duration-300
        ${isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'}
        md:translate-x-0
        ${isSidebarOpen ? 'w-64' : 'w-20'}
        print:hidden
      `}>
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
            onClick={() => {
              if (window.innerWidth < 768) {
                setIsMobileMenuOpen(false);
              } else {
                setIsSidebarOpen(!isSidebarOpen);
              }
            }}
            className="text-gray-400 hover:text-gray-600"
          >
            {isSidebarOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
          </button>
        </div>

        <div className="flex-1 py-4 overflow-y-auto">
          <div className="px-3 space-y-6">
            {menuCategories.map((category, index) => {
              const visibleItems = category.items.filter((item) => canAccess(item.permission));

              if (visibleItems.length === 0) return null;

              return (
                <div key={index} className="flex flex-col">
                  {isSidebarOpen && (
                    <h3 className="px-3 mb-2 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                      {category.title}
                    </h3>
                  )}
                  <ul className="space-y-1">
                    {visibleItems.map((item) => {
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
              );
            })}
          </div>
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
      <div className={`flex-1 flex flex-col min-w-0 overflow-hidden transition-all duration-300 ${isSidebarOpen ? 'md:ml-64' : 'md:ml-20'} print:ml-0 print:w-full`}>
        {/* Top Navbar */}
        <header className="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 md:px-6 z-10 sticky top-0 print:hidden">
          <div className="flex items-center flex-1 min-w-0">
            <button
              onClick={() => setIsMobileMenuOpen(true)}
              className="md:hidden mr-2 text-gray-400 hover:text-gray-600 flex-shrink-0"
            >
              <Menu className="w-5 h-5" />
            </button>
            <div className="w-full max-w-md lg:max-w-2xl relative" ref={searchRef}>
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
          <div className="ml-2 md:ml-4 flex items-center space-x-2 md:space-x-4">
            <div className="hidden md:block">
              <BranchSelector />
            </div>
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
        <main className="flex-1 overflow-y-auto bg-gray-50/50 p-4 md:p-6">
          <div className="max-w-7xl mx-auto">
            <Routes>
              <Route index element={<DashboardRenderer />} />
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
              <Route path="my-attendance" element={<EmployeeDashboard />} />
              <Route path="attendance/history" element={<EmployeeAttendanceHistory />} />
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

              <Route path="payroll" element={<PayrollGenerationPage />} />
              <Route path="salary-structures" element={<SalaryStructuresPage />} />
              <Route path="period-payroll" element={<PayrollPeriodsPage />} />
              <Route path="process-payroll" element={<PayrollGenerationPage />} />
              <Route path="my-payroll" element={<EmployeePayrollPage />} />
              <Route path="payslip/:id" element={<PayslipPage />} />
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
