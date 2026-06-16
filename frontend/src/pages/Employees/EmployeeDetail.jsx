import ModuleDetailPage from '../ERP/ModuleDetailPage';
import { useEmployeesStore } from '../../store/employeesStore';

const simpleColumns = [
  { header: 'Date', accessorKey: 'report_date' },
  { header: 'Status/Type', accessorKey: 'status' },
  { header: 'Notes', accessorKey: 'content' },
];

export default function EmployeeDetail() {
  return (
    <ModuleDetailPage
      title={(employee) => employee.name || employee.user?.name || `Employee #${employee.id}`}
      store={useEmployeesStore}
      backPath="/dashboard/employees"
      fields={[
        { label: 'Email', path: 'email' },
        { label: 'Department', render: (employee) => employee.department?.name || employee.department_name || '-' },
        { label: 'Designation', path: 'designation.name' },
        { label: 'Branch', render: (employee) => employee.user?.branch?.name || employee.branch?.name || '-' },
        { label: 'Salary', path: 'salary' },
        { label: 'Status', path: 'status' },
      ]}
      sections={[
        { title: 'Sites', path: 'sites', emptyText: 'No site assignments.', columns: [{ header: 'Site', accessorKey: 'name' }, { header: 'Status', accessorKey: 'status' }] },
        { title: 'Daily Reports', path: 'daily_reports', emptyText: 'No daily reports.', columns: simpleColumns },
        { title: 'Attendance', path: 'attendances', emptyText: 'No attendance records.', columns: [{ header: 'Date', accessorKey: 'date' }, { header: 'Status', accessorKey: 'status' }] },
        { title: 'Leaves', path: 'leaves', emptyText: 'No leave records.', columns: [{ header: 'Type', accessorKey: 'type' }, { header: 'Status', accessorKey: 'status' }] },
        { title: 'Payroll', path: 'payroll', emptyText: 'No payroll records.', columns: [{ header: 'Month', accessorKey: 'month' }, { header: 'Net Pay', accessorKey: 'net_pay' }, { header: 'Status', accessorKey: 'status' }] },
      ]}
    />
  );
}
