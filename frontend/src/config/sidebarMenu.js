import {
  LayoutDashboard, Users, Calendar, ClipboardList, MapPin, Clock, FileText, Briefcase,
  DollarSign, Layers, Package, Boxes, Warehouse, ShoppingCart, Shield, Settings,
  Ruler, GitCompare, Move3D, FileSpreadsheet, FileClock, FileInput, Building2,
  TrendingUp, Target, Bell, BarChart3, PenTool,
} from 'lucide-react';

export const menuCategories = [
  {
    title: 'Overview',
    items: [
      { name: 'Dashboard', path: '/dashboard', icon: LayoutDashboard, roles: ['*'] },
    ],
  },
  {
    title: 'Human Resources',
    items: [
      { name: 'Employees', path: '/dashboard/employees', icon: Users, roles: ['Admin', 'HR', 'Manager'], permissions: ['view_employees', 'employee.view', 'manage employees'] },
      { name: 'Attendance', path: '/dashboard/attendance', icon: Calendar, roles: ['Admin'], permissions: ['attendance.view', 'view_attendance'] },
      { name: 'My Attendance', path: '/dashboard/my-attendance', icon: MapPin, roles: ['*'] },
      { name: 'Shifts', path: '/dashboard/shifts', icon: Clock, roles: ['Admin', 'HR'], permissions: ['shift.view'] },
      { name: 'Daily Reports', path: '/dashboard/daily-reports', icon: FileText, roles: ['*'] },
      { name: 'Leave', path: '/dashboard/leave-management', icon: Briefcase, roles: ['Admin', 'HR', 'Manager'], permissions: ['view_leaves', 'leave.view', 'leave.approve'] },
      { name: 'My Leaves', path: '/dashboard/my-leaves', icon: FileText, roles: ['*'] },
    ],
  },
  {
    title: 'Payroll',
    items: [
      { name: 'Payroll Dashboard', path: '/dashboard/payroll', icon: DollarSign, roles: ['Admin', 'Accountant', 'HR'], permissions: ['view_payroll', 'manage_payroll'] },
      { name: 'Salary Structures', path: '/dashboard/salary-structures', icon: DollarSign, roles: ['Admin', 'Accountant', 'HR'], permissions: ['view_payroll', 'manage_payroll'] },
      { name: 'Payroll Periods', path: '/dashboard/period-payroll', icon: Calendar, roles: ['Admin', 'Accountant'], permissions: ['manage_payroll'] },
      { name: 'Process Payroll', path: '/dashboard/process-payroll', icon: ClipboardList, roles: ['Admin', 'Accountant'], permissions: ['manage_payroll'] },
      { name: 'My Payroll', path: '/dashboard/my-payroll', icon: FileText, roles: ['*'] },
    ],
  },
  {
    title: 'Organization',
    items: [
      { name: 'Departments', path: '/dashboard/departments', icon: Layers, roles: ['Admin', 'HR'], permissions: ['department.view'] },
      { name: 'Designations', path: '/dashboard/designations', icon: Briefcase, roles: ['Admin', 'HR'], permissions: ['designation.view'] },
      { name: 'Sites', path: '/dashboard/sites', icon: MapPin, roles: ['Admin', 'Manager', 'HR'], permissions: ['view_sites', 'site.view'] },
    ],
  },
  {
    title: 'Sales & Survey',
    items: [
      { name: 'Buildings', path: '/dashboard/buildings', icon: Building2, roles: ['Admin', 'Manager', 'HR', 'Sales'], permissions: ['building.view'] },
      { name: 'Sales Dashboard', path: '/dashboard/sales-dashboard', icon: BarChart3, roles: ['Admin', 'Sales', 'Manager'], permissions: ['view reports'] },
      { name: 'Buildings Map', path: '/dashboard/buildings-map', icon: MapPin, roles: ['Admin', 'Sales', 'Manager'], permissions: ['building.view'] },
      { name: 'Site Visits', path: '/dashboard/site-visits', icon: Calendar, roles: ['Admin', 'Sales', 'Manager'], permissions: ['building.view'] },
      { name: 'Opportunities', path: '/dashboard/opportunities', icon: Target, roles: ['Admin', 'Sales', 'Manager'], permissions: ['building.view'] },
      { name: 'Follow-ups', path: '/dashboard/follow-ups', icon: Bell, roles: ['Admin', 'Sales', 'Manager'], permissions: ['building.view'] },
    ],
  },
  {
    title: 'Design',
    items: [
      { name: 'Buildings', path: '/dashboard/buildings', icon: Building2, roles: ['Designer'], permissions: ['building.view'] },
      { name: 'Site Visits', path: '/dashboard/site-visits', icon: Calendar, roles: ['Designer'], permissions: ['building.view'] },
      { name: 'Follow-ups', path: '/dashboard/follow-ups', icon: Bell, roles: ['Designer'], permissions: ['building.view'] },
      { name: 'Opportunities', path: '/dashboard/opportunities', icon: Target, roles: ['Designer'], permissions: ['building.view'] },
    ],
  },
  {
    title: 'Inventory',
    items: [
      { name: 'Dashboard', path: '/dashboard/inventory', icon: LayoutDashboard, roles: ['Admin', 'Store Manager', 'Manager'], permissions: ['inventory.dashboard.view', 'view_inventory'] },
      { name: 'Products', path: '/dashboard/products', icon: Boxes, roles: ['Admin', 'Store Manager', 'Manager', 'Accountant'], permissions: ['view_products', 'product.view'] },
      { name: 'Categories', path: '/dashboard/categories', icon: Layers, roles: ['Admin', 'Store Manager'], permissions: ['view_categories', 'category.view'] },
      { name: 'Locations', path: '/dashboard/inventory/locations', icon: MapPin, roles: ['Admin', 'Store Manager', 'Manager'], permissions: ['inventory.locations.view'] },
      { name: 'Units', path: '/dashboard/inventory/units', icon: Ruler, roles: ['Admin', 'Store Manager'], permissions: ['inventory.units.view'] },
      { name: 'Conversions', path: '/dashboard/inventory/conversions', icon: GitCompare, roles: ['Admin', 'Store Manager'], permissions: ['inventory.conversions.view'] },
      { name: 'Stock', path: '/dashboard/inventory/stock', icon: Package, roles: ['Admin', 'Store Manager', 'Manager'], permissions: ['inventory.stock.view'] },
      { name: 'Transfers', path: '/dashboard/inventory/transfers', icon: Move3D, roles: ['Admin', 'Store Manager'], permissions: ['inventory.transfers.view'] },
      { name: 'Transactions', path: '/dashboard/inventory/transactions', icon: FileSpreadsheet, roles: ['Admin', 'Store Manager', 'Manager'], permissions: ['inventory.transactions.view'] },
      { name: 'Stock Requests', path: '/dashboard/inventory/requests', icon: FileInput, roles: ['Admin', 'Store Manager'], permissions: ['inventory.requests.view'] },
    ],
  },
  {
    title: 'Sales & Purchases',
    items: [
      { name: 'Customers', path: '/dashboard/customers', icon: Users, roles: ['Admin', 'Accountant'], permissions: ['view_customers', 'customer.view'] },
      { name: 'Suppliers', path: '/dashboard/suppliers', icon: ShoppingCart, roles: ['Admin', 'Accountant'], permissions: ['view_suppliers', 'manage_suppliers', 'supplier.view'] },
      { name: 'Purchases', path: '/dashboard/purchases', icon: ClipboardList, roles: ['Admin', 'Accountant', 'Store Manager'], permissions: ['view_purchase_orders', 'create_purchase_orders'] },
      { name: 'Sales', path: '/dashboard/sales', icon: ClipboardList, roles: ['Admin', 'Accountant', 'Store Manager'], permissions: ['view_sales_orders', 'create_sales_orders'] },
      { name: 'Invoices', path: '/dashboard/invoices', icon: FileText, roles: ['Admin', 'Accountant'], permissions: ['view_invoices', 'invoice.view'] },
    ],
  },
  {
    title: 'System',
    items: [
      { name: 'Reports', path: '/dashboard/reports', icon: ClipboardList, roles: ['Admin', 'Manager', 'Accountant', 'HR'], permissions: ['view reports'] },
      { name: 'Access Control', path: '/dashboard/access-control', icon: Shield, roles: ['Admin'], permissions: ['manage_users', 'manage_roles', 'manage_permissions'] },
    ],
  },
];
