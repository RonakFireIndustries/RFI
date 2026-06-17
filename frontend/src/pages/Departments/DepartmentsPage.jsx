import React from 'react';
import ModuleListPage from '../ERP/ModuleListPage';
import { useDepartmentStore } from '../../store/departmentStore';

export default function DepartmentsPage() {
  return (
    <ModuleListPage
      title="Departments"
      description="Manage organization departments."
      store={useDepartmentStore}
      searchPlaceholder="Search departments..."
      resourceName="departments"
      columns={[
        { header: 'ID', accessor: 'id' },
        { header: 'Name', accessor: 'name' },
        { header: 'Description', accessor: 'description' },
        { header: 'Employees', accessor: 'employees_count' }
      ]}
      fields={[
        { name: 'name', label: 'Department Name', required: true },
        { name: 'description', label: 'Description', type: 'textarea' },
      ]}
    />
  );
}
