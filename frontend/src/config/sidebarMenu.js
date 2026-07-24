import {
  LayoutDashboard, Users, Calendar, ClipboardList, MapPin, Clock, FileText, Briefcase,
  DollarSign, Layers, Package, Boxes, Warehouse, ShoppingCart, Shield, Settings, Sliders,
  Ruler, GitCompare, Move3D, FileSpreadsheet, FileClock, FileInput, Building2, MessageSquare,
  TrendingUp, Target, Bell, BarChart3,
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
      { name: 'Employees', path: '/dashboard/employees', icon: Users, roles: ['Admin', 'HR', 'Manager'] },
      { name: 'Attendance', path: '/dashboard/attendance', icon: Calendar, roles: ['Admin'] },
      { name: 'My Attendance', path: '/dashboard/my-attendance', icon: MapPin, roles: ['*'] },
      { name: 'Shifts', path: '/dashboard/shifts', icon: Clock, roles: ['Admin', 'HR'] },
      { name: 'Daily Reports', path: '/dashboard/daily-reports', icon: FileText, roles: ['*'] },
      { name: 'Leave', path: '/dashboard/leave-management', icon: Briefcase, roles: ['Admin', 'HR', 'Manager'] },
      { name: 'My Leaves', path: '/dashboard/my-leaves', icon: FileText, roles: ['*'] },
    ],
  },
  {
    title: 'Payroll',
    items: [
      { name: 'Payroll Dashboard', path: '/dashboard/payroll', icon: DollarSign, roles: ['Admin', 'Accountant', 'HR'] },
      { name: 'Salary Structures', path: '/dashboard/salary-structures', icon: DollarSign, roles: ['Admin', 'Accountant', 'HR'] },
      { name: 'Payroll Periods', path: '/dashboard/period-payroll', icon: Calendar, roles: ['Admin', 'Accountant'] },
      { name: 'Process Payroll', path: '/dashboard/process-payroll', icon: ClipboardList, roles: ['Admin', 'Accountant'] },
      { name: 'My Payroll', path: '/dashboard/my-payroll', icon: FileText, roles: ['*'] },
    ],
  },
  {
    title: 'Organization',
    items: [
      { name: 'Departments', path: '/dashboard/departments', icon: Layers, roles: ['Admin', 'HR'] },
      { name: 'Designations', path: '/dashboard/designations', icon: Briefcase, roles: ['Admin', 'HR'] },
      { name: 'Sites', path: '/dashboard/sites', icon: MapPin, roles: ['Admin', 'Manager', 'HR'] },
    ],
  },
  {
    title: 'Sales & Survey',
    items: [
      { name: 'Buildings', path: '/dashboard/buildings', icon: Building2, roles: ['Admin', 'Manager', 'HR', 'Sales'] },
      { name: 'Sales Dashboard', path: '/dashboard/sales-dashboard', icon: BarChart3, roles: ['Admin', 'Sales', 'Manager'] },
      { name: 'Buildings Map', path: '/dashboard/buildings-map', icon: MapPin, roles: ['Admin', 'Sales', 'Manager'] },
      { name: 'Site Visits', path: '/dashboard/site-visits', icon: Calendar, roles: ['Admin', 'Sales', 'Manager'] },
      { name: 'Opportunities', path: '/dashboard/opportunities', icon: Target, roles: ['Admin', 'Sales', 'Manager'] },
      { name: 'Follow-ups', path: '/dashboard/follow-ups', icon: Bell, roles: ['Admin', 'Sales', 'Manager'] },
    ],
  },
  {
    title: 'Inventory',
    items: [
      { name: 'Dashboard', path: '/dashboard/inventory', icon: LayoutDashboard, roles: ['Admin', 'Store Manager', 'Manager'] },
      { name: 'Products', path: '/dashboard/products', icon: Boxes, roles: ['Admin', 'Store Manager', 'Manager', 'Accountant'] },
      { name: 'Categories', path: '/dashboard/categories', icon: Layers, roles: ['Admin', 'Store Manager'] },
      { name: 'Locations', path: '/dashboard/inventory/locations', icon: MapPin, roles: ['Admin', 'Store Manager', 'Manager'] },
      { name: 'Units', path: '/dashboard/inventory/units', icon: Ruler, roles: ['Admin', 'Store Manager'] },
      { name: 'Conversions', path: '/dashboard/inventory/conversions', icon: GitCompare, roles: ['Admin', 'Store Manager'] },
      { name: 'Stock', path: '/dashboard/inventory/stock', icon: Package, roles: ['Admin', 'Store Manager', 'Manager'] },
      { name: 'Transfers', path: '/dashboard/inventory/transfers', icon: Move3D, roles: ['Admin', 'Store Manager'] },
      { name: 'Transactions', path: '/dashboard/inventory/transactions', icon: FileSpreadsheet, roles: ['Admin', 'Store Manager', 'Manager'] },
      { name: 'Stock Requests', path: '/dashboard/inventory/requests', icon: FileInput, roles: ['Admin', 'Store Manager'] },
    ],
  },
  {
    title: 'Sales & Purchases',
    items: [
      { name: 'Customers', path: '/dashboard/customers', icon: Users, roles: ['Admin', 'Accountant'] },
      { name: 'Suppliers', path: '/dashboard/suppliers', icon: ShoppingCart, roles: ['Admin', 'Accountant'] },
      { name: 'Purchases', path: '/dashboard/purchases', icon: ClipboardList, roles: ['Admin', 'Accountant', 'Store Manager'] },
      { name: 'Sales', path: '/dashboard/sales', icon: ClipboardList, roles: ['Admin', 'Accountant', 'Store Manager'] },
      { name: 'Invoices', path: '/dashboard/invoices', icon: FileText, roles: ['Admin', 'Accountant'] },
    ],
  },
  {
    title: 'Communication',
    items: [
      { name: 'Chat', path: '/dashboard/chat', icon: MessageSquare, roles: ['*'] },
    ],
  },
  {
    title: 'System',
    items: [
      { name: 'Reports', path: '/dashboard/reports', icon: ClipboardList, roles: ['Admin', 'Manager', 'Accountant', 'HR'] },
      { name: 'Role Configuration', path: '/dashboard/role-configuration', icon: Sliders, roles: ['Admin'] },
      { name: 'User Access', path: '/dashboard/user-access', icon: Shield, roles: ['Admin'] },
    ],
  },
];
