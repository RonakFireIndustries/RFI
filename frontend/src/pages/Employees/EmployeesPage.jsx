import { useEffect, useState } from 'react';
import ModuleListPage from '../ERP/ModuleListPage';
import api from '../../services/api';
import { unwrapList } from '../../services/resourceHelpers';
import { useEmployeesStore } from '../../store/employeesStore';

export default function EmployeesPage() {
  const [lookups, setLookups] = useState({ branches: [], departments: [], designations: [] });

  useEffect(() => {
    Promise.all([api.get('/branches'), api.get('/departments'), api.get('/designations')])
      .then(([branches, departments, designations]) => {
        setLookups({
          branches: unwrapList(branches.data),
          departments: unwrapList(departments.data),
          designations: unwrapList(designations.data),
        });
      })
      .catch((error) => console.error('Failed to load employee lookups', error));
  }, []);

  return (
    <ModuleListPage
      title="Employees"
      description="Manage employee records with department, designation, site, attendance, leave, and payroll relationships."
      store={useEmployeesStore}
      detailBasePath="/dashboard/employees"
      searchPlaceholder="Search employees..."
      lookups={lookups}
      columns={[
        { header: 'Name', accessor: 'name' },
        { header: 'Email', accessor: 'email' },
        { header: 'Department', cellValue: (row) => row.department?.name || row.department_name || '-' },
        { header: 'Designation', accessor: 'designation.name' },
        { header: 'Branch', cellValue: (row) => row.user?.branch?.name || row.branch?.name || '-' },
        { header: 'Status', accessor: 'status' },
      ]}
      fields={[
        { name: 'name', label: 'Name', required: true },
        { name: 'email', label: 'Email', type: 'email', required: true },
        { name: 'password', label: 'Password', type: 'password' },
        { name: 'branch_id', label: 'Branch', type: 'select', optionsKey: 'branches', valuePath: 'user.branch_id', required: true },
        { name: 'department_id', label: 'Department', type: 'select', optionsKey: 'departments', emptyAsNull: true },
        { name: 'designation_id', label: 'Designation', type: 'select', optionsKey: 'designations', emptyAsNull: true },
        { name: 'salary', label: 'Salary', type: 'number', step: '0.01', emptyAsNull: true },
        { name: 'shift', label: 'Shift' },
        { name: 'joining_date', label: 'Joining Date', type: 'date' },
        { name: 'status', label: 'Status', type: 'select', defaultValue: 'active', options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }] },
      ]}
    />
  );
}
