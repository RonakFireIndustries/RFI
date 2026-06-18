import {
  LayoutDashboard, Users, Calendar, ClipboardList, MapPin, Clock, FileText, Briefcase,
  DollarSign, Layers, Package, Boxes, Warehouse, ShoppingCart, Shield, Settings,
} from 'lucide-react';

export const menuCategories = [
  {
    title: 'Overview',
    items: [
      { name: 'Dashboard', path: '/dashboard', icon: LayoutDashboard, permission: 'view dashboard' },
    ],
  },
  {
    title: 'Human Resources',
    items: [
      { name: 'Employees', path: '/dashboard/employees', icon: Users, permission: 'employee.view' },
      { name: 'Attendance', path: '/dashboard/attendance', icon: Calendar, permission: 'attendance.view' },
      { name: 'My Attendance', path: '/dashboard/my-attendance', icon: MapPin, permission: null },
      { name: 'Shifts', path: '/dashboard/shifts', icon: Clock, permission: 'shift.view' },
      { name: 'Daily Reports', path: '/dashboard/daily-reports', icon: FileText, permission: 'daily-report.view' },
      { name: 'Leave', path: '/dashboard/leave-management', icon: Briefcase, permission: 'leave.view' },
    ],
  },
  {
    title: 'Payroll',
    items: [
      { name: 'Payroll Dashboard', path: '/dashboard/payroll', icon: DollarSign, permission: 'view_payroll' },
      { name: 'Salary Structures', path: '/dashboard/salary-structures', icon: DollarSign, permission: 'salary-structure.view' },
      { name: 'Payroll Periods', path: '/dashboard/period-payroll', icon: Calendar, permission: 'payroll.generate' },
      { name: 'Process Payroll', path: '/dashboard/process-payroll', icon: ClipboardList, permission: 'payroll.generate' },
      { name: 'My Payroll', path: '/dashboard/my-payroll', icon: FileText, permission: null },
    ],
  },
  {
    title: 'Organization',
    items: [
      { name: 'Departments', path: '/dashboard/departments', icon: Layers, permission: 'department.view' },
      { name: 'Designations', path: '/dashboard/designations', icon: Briefcase, permission: 'designation.view' },
      { name: 'Sites', path: '/dashboard/sites', icon: MapPin, permission: 'site.view' },
    ],
  },
  {
    title: 'Supply Chain',
    items: [
      { name: 'Inventory', path: '/dashboard/inventory', icon: Package, permission: 'view_inventory' },
      { name: 'Products', path: '/dashboard/products', icon: Boxes, permission: 'view_products' },
      { name: 'Categories', path: '/dashboard/categories', icon: Layers, permission: 'view_categories' },
      { name: 'Warehouses', path: '/dashboard/warehouses', icon: Warehouse, permission: 'view warehouses' },
    ],
  },
  {
    title: 'Sales & Purchases',
    items: [
      { name: 'Customers', path: '/dashboard/customers', icon: Users, permission: 'view_customers' },
      { name: 'Suppliers', path: '/dashboard/suppliers', icon: ShoppingCart, permission: 'view_suppliers' },
      { name: 'Purchases', path: '/dashboard/purchases', icon: ClipboardList, permission: 'view_purchase_orders' },
      { name: 'Sales', path: '/dashboard/sales', icon: ClipboardList, permission: 'view_sales_orders' },
      { name: 'Invoices', path: '/dashboard/invoices', icon: FileText, permission: 'view_invoices' },
    ],
  },
  {
    title: 'System',
    items: [
      { name: 'Reports', path: '/dashboard/reports', icon: ClipboardList, permission: 'view reports' },
      { name: 'Role & Permissions', path: '/dashboard/roles', icon: Shield, permission: 'manage_roles' },
      { name: 'User Access', path: '/dashboard/user-access', icon: Shield, permission: 'manage_users' },
    ],
  },
];
