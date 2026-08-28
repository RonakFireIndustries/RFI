import {
  LayoutDashboard, Users, Calendar, ClipboardList, MapPin, Clock, FileText, Briefcase,
  DollarSign, Layers, Package, Boxes, Warehouse, ShoppingCart, Shield, Settings,
  Ruler, GitCompare, Move3D, FileSpreadsheet, FileClock, FileInput, Building2,
  TrendingUp, Target, Bell, BarChart3, PenTool, Flame,
} from 'lucide-react';

/*
 * Sidebar menu is now permission-first.
 *
 * Every item that requires access declares the canonical permission name(s)
 * needed to view it. In nearly all cases that is "<module>.view".
 *
 *   - "public" items (self-service, dashboard) have permissions: ['*'].
 *   - Access Control is restricted to super admins / access-control.manage.
 *
 * canAccess() in DashboardLayout resolves an item using the user's permission
 * list ONLY. Role names are no longer used for menu decisions.
 *
 * Permission names here MUST match config/access.php in the backend.
 */
export const menuCategories = [
  {
    title: 'Overview',
    items: [
      { name: 'Dashboard', path: '/dashboard', icon: LayoutDashboard, permissions: ['*'] },
    ],
  },
  {
    title: 'Human Resources',
    items: [
      { name: 'Employees', path: '/dashboard/employees', icon: Users, permissions: ['employees.view'] },
      { name: 'Attendance', path: '/dashboard/attendance', icon: Calendar, permissions: ['attendance.view'] },
      { name: 'My Attendance', path: '/dashboard/my-attendance', icon: MapPin, permissions: ['*'] },
      { name: 'Shifts', path: '/dashboard/shifts', icon: Clock, permissions: ['shifts.view'] },
      { name: 'Daily Reports', path: '/dashboard/daily-reports', icon: FileText, permissions: ['*'] },
      { name: 'Leave', path: '/dashboard/leave-management', icon: Briefcase, permissions: ['leaves.view'] },
      { name: 'My Leaves', path: '/dashboard/my-leaves', icon: FileText, permissions: ['*'] },
    ],
  },
  {
    title: 'Payroll',
    items: [
      { name: 'Payroll Dashboard', path: '/dashboard/payroll', icon: DollarSign, permissions: ['payroll.view'] },
      { name: 'Salary Structures', path: '/dashboard/salary-structures', icon: DollarSign, permissions: ['salary-structures.view'] },
      { name: 'Payroll Periods', path: '/dashboard/period-payroll', icon: Calendar, permissions: ['payroll.manage'] },
      { name: 'Process Payroll', path: '/dashboard/process-payroll', icon: ClipboardList, permissions: ['payroll.manage'] },
      { name: 'My Payroll', path: '/dashboard/my-payroll', icon: FileText, permissions: ['*'] },
    ],
  },
  {
    title: 'Organization',
    items: [
      { name: 'Departments', path: '/dashboard/departments', icon: Layers, permissions: ['departments.view'] },
      { name: 'Designations', path: '/dashboard/designations', icon: Briefcase, permissions: ['designations.view'] },
      { name: 'Sites', path: '/dashboard/sites', icon: MapPin, permissions: ['sites.view'] },
    ],
  },
  {
    title: 'Sales & Survey',
    items: [
      { name: 'Buildings', path: '/dashboard/buildings', icon: Building2, permissions: ['buildings.view'] },
      { name: 'Fire Extinguishers', path: '/dashboard/extinguishers', icon: Flame, permissions: ['extinguishers.view'] },
      { name: 'Sales Dashboard', path: '/dashboard/sales-dashboard', icon: BarChart3, permissions: ['reports.view'] },
      { name: 'Buildings Map', path: '/dashboard/buildings-map', icon: MapPin, permissions: ['buildings.view'] },
      { name: 'Site Visits', path: '/dashboard/site-visits', icon: Calendar, permissions: ['buildings.view'] },
      { name: 'Opportunities', path: '/dashboard/opportunities', icon: Target, permissions: ['buildings.view'] },
      { name: 'Follow-ups', path: '/dashboard/follow-ups', icon: Bell, permissions: ['buildings.view'] },
    ],
  },
  {
    title: 'Inventory',
    items: [
      { name: 'Dashboard', path: '/dashboard/inventory', icon: LayoutDashboard, permissions: ['inventory.view'] },
      { name: 'Products', path: '/dashboard/products', icon: Boxes, permissions: ['products.view'] },
      { name: 'Categories', path: '/dashboard/categories', icon: Layers, permissions: ['categories.view'] },
      { name: 'Locations', path: '/dashboard/inventory/locations', icon: MapPin, permissions: ['inventory-locations.view'] },
      { name: 'Units', path: '/dashboard/inventory/units', icon: Ruler, permissions: ['units.view'] },
      { name: 'Conversions', path: '/dashboard/inventory/conversions', icon: GitCompare, permissions: ['unit-conversions.view'] },
      { name: 'Stock', path: '/dashboard/inventory/stock', icon: Package, permissions: ['stock.view'] },
      { name: 'Transfers', path: '/dashboard/inventory/transfers', icon: Move3D, permissions: ['stock-transfers.view'] },
      { name: 'Transactions', path: '/dashboard/inventory/transactions', icon: FileSpreadsheet, permissions: ['stock-transactions.view'] },
      { name: 'Stock Requests', path: '/dashboard/inventory/requests', icon: FileInput, permissions: ['stock-requests.view'] },
    ],
  },
  {
    title: 'Sales & Purchases',
    items: [
      { name: 'Customers', path: '/dashboard/customers', icon: Users, permissions: ['customers.view'] },
      { name: 'Suppliers', path: '/dashboard/suppliers', icon: ShoppingCart, permissions: ['suppliers.view'] },
      { name: 'Purchases', path: '/dashboard/purchases', icon: ClipboardList, permissions: ['purchase-orders.view'] },
      { name: 'Sales', path: '/dashboard/sales', icon: ClipboardList, permissions: ['sales-orders.view'] },
      { name: 'Invoices', path: '/dashboard/invoices', icon: FileText, permissions: ['invoices.view'] },
    ],
  },
  {
    title: 'System',
    items: [
      { name: 'Reports', path: '/dashboard/reports', icon: ClipboardList, permissions: ['reports.view'] },
      { name: 'Access Control', path: '/dashboard/access-control', icon: Shield, permissions: ['access-control.manage', '__super_admin__'] },
    ],
  },
];
