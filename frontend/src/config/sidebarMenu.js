import {
  LayoutDashboard, Users, Calendar, ClipboardList, MapPin, Clock, FileText, Briefcase,
  DollarSign, Layers, Package, Boxes, Warehouse, ShoppingCart, Shield, Settings, Sliders,
  Ruler, GitCompare, Move3D, FileSpreadsheet, FileClock, FileInput,
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
      { name: 'Employees', path: '/dashboard/employees', icon: Users, roles: ['Super Admin', 'Admin', 'HR Manager', 'General Manager', 'Production Manager', 'Workshop Supervisor', 'Design Manager'] },
      { name: 'Attendance', path: '/dashboard/attendance', icon: Calendar, roles: ['Super Admin', 'Admin', 'HR Manager', 'General Manager', 'Production Manager', 'Workshop Supervisor'] },
      { name: 'My Attendance', path: '/dashboard/my-attendance', icon: MapPin, roles: ['*'] },
      { name: 'Shifts', path: '/dashboard/shifts', icon: Clock, roles: ['Super Admin', 'Admin', 'HR Manager'] },
      { name: 'Daily Reports', path: '/dashboard/daily-reports', icon: FileText, roles: ['Super Admin', 'Admin', 'HR Manager', 'General Manager', 'Production Manager', 'Workshop Supervisor', 'Design Manager'] },
      { name: 'Leave', path: '/dashboard/leave-management', icon: Briefcase, roles: ['Super Admin', 'Admin', 'HR Manager', 'General Manager'] },
      { name: 'My Leaves', path: '/dashboard/my-leaves', icon: FileText, roles: ['*'] },
    ],
  },
  {
    title: 'Payroll',
    items: [
      { name: 'Payroll Dashboard', path: '/dashboard/payroll', icon: DollarSign, roles: ['Super Admin', 'Admin', 'Finance Manager', 'Accountant', 'General Manager'] },
      { name: 'Salary Structures', path: '/dashboard/salary-structures', icon: DollarSign, roles: ['Super Admin', 'Admin', 'Finance Manager', 'Accountant'] },
      { name: 'Payroll Periods', path: '/dashboard/period-payroll', icon: Calendar, roles: ['Super Admin', 'Admin', 'Finance Manager'] },
      { name: 'Process Payroll', path: '/dashboard/process-payroll', icon: ClipboardList, roles: ['Super Admin', 'Admin', 'Finance Manager'] },
      { name: 'My Payroll', path: '/dashboard/my-payroll', icon: FileText, roles: ['*'] },
    ],
  },
  {
    title: 'Organization',
    items: [
      { name: 'Departments', path: '/dashboard/departments', icon: Layers, roles: ['Super Admin', 'Admin', 'HR Manager'] },
      { name: 'Designations', path: '/dashboard/designations', icon: Briefcase, roles: ['Super Admin', 'Admin', 'HR Manager'] },
      { name: 'Sites', path: '/dashboard/sites', icon: MapPin, roles: ['Super Admin', 'Admin', 'HR Manager', 'Production Manager', 'Workshop Supervisor'] },
    ],
  },
  {
    title: 'Inventory',
    items: [
      { name: 'Dashboard', path: '/dashboard/inventory', icon: LayoutDashboard, roles: ['Super Admin', 'Admin', 'Store Manager', 'Warehouse Manager', 'Inventory Staff'] },
      { name: 'Products', path: '/dashboard/products', icon: Boxes, roles: ['Super Admin', 'Admin', 'Store Manager', 'Warehouse Manager', 'Inventory Staff'] },
      { name: 'Categories', path: '/dashboard/categories', icon: Layers, roles: ['Super Admin', 'Admin', 'Store Manager', 'Warehouse Manager'] },
      { name: 'Locations', path: '/dashboard/inventory/locations', icon: MapPin, roles: ['Super Admin', 'Admin', 'Store Manager', 'Warehouse Manager'] },
      { name: 'Units', path: '/dashboard/inventory/units', icon: Ruler, roles: ['Super Admin', 'Admin', 'Store Manager', 'Warehouse Manager'] },
      { name: 'Conversions', path: '/dashboard/inventory/conversions', icon: GitCompare, roles: ['Super Admin', 'Admin', 'Store Manager', 'Warehouse Manager'] },
      { name: 'Stock', path: '/dashboard/inventory/stock', icon: Package, roles: ['Super Admin', 'Admin', 'Store Manager', 'Warehouse Manager', 'Inventory Staff', 'General Manager'] },
      { name: 'Transfers', path: '/dashboard/inventory/transfers', icon: Move3D, roles: ['Super Admin', 'Admin', 'Store Manager', 'Warehouse Manager', 'Inventory Staff'] },
      { name: 'Transactions', path: '/dashboard/inventory/transactions', icon: FileSpreadsheet, roles: ['Super Admin', 'Admin', 'Store Manager', 'Warehouse Manager', 'Inventory Staff'] },
      { name: 'Stock Requests', path: '/dashboard/inventory/requests', icon: FileInput, roles: ['Super Admin', 'Admin', 'Store Manager', 'Warehouse Manager', 'Production Manager', 'Inventory Staff'] },
    ],
  },
  {
    title: 'Sales & Purchases',
    items: [
      { name: 'Customers', path: '/dashboard/customers', icon: Users, roles: ['Super Admin', 'Admin', 'Sales Manager', 'Sales Executive', 'Accountant', 'Finance Manager'] },
      { name: 'Suppliers', path: '/dashboard/suppliers', icon: ShoppingCart, roles: ['Super Admin', 'Admin', 'Accountant', 'Finance Manager'] },
      { name: 'Purchases', path: '/dashboard/purchases', icon: ClipboardList, roles: ['Super Admin', 'Admin', 'Finance Manager', 'Accountant'] },
      { name: 'Sales', path: '/dashboard/sales', icon: ClipboardList, roles: ['Super Admin', 'Admin', 'Sales Manager', 'Sales Executive'] },
      { name: 'Invoices', path: '/dashboard/invoices', icon: FileText, roles: ['Super Admin', 'Admin', 'Sales Manager', 'Sales Executive', 'Finance Manager', 'Accountant'] },
    ],
  },
  {
    title: 'System',
    items: [
      { name: 'Reports', path: '/dashboard/reports', icon: ClipboardList, roles: ['Super Admin', 'Admin', 'General Manager', 'Finance Manager', 'Design Manager'] },
      { name: 'Role Configuration', path: '/dashboard/role-configuration', icon: Sliders, roles: ['Super Admin', 'Admin', 'System Admin', 'IT Manager'] },
      { name: 'User Access', path: '/dashboard/user-access', icon: Shield, roles: ['Super Admin', 'Admin', 'System Admin', 'IT Manager'] },
    ],
  },
];
