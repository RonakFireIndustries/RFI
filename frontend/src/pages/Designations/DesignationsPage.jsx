import React, { useEffect, useState } from 'react';
import ModuleListPage from '../ERP/ModuleListPage';
import { useDesignationStore } from '../../store/designationStore';
import api from '../../services/api';
import { unwrapList } from '../../services/resourceHelpers';

export default function DesignationsPage() {
  const [lookups, setLookups] = useState({ departments: [] });

  useEffect(() => {
    api.get('/departments')
      .then((res) => setLookups({ departments: unwrapList(res.data) }))
      .catch((error) => console.error('Failed to load departments lookup', error));
  }, []);

  return (
    <ModuleListPage
      title="Designations"
      description="Manage job titles and roles."
      store={useDesignationStore}
      searchPlaceholder="Search designations..."
      lookups={lookups}
      resourceName="designations"
      columns={[
        { header: 'ID', accessor: 'id' },
        { header: 'Name', accessor: 'name' },
        { header: 'Department', cellValue: (row) => row.department?.name || '-' },
        { header: 'Description', accessor: 'description' },
        { header: 'Employees', accessor: 'employees_count' }
      ]}
      fields={[
        { name: 'name', label: 'Designation Name', required: true },
        { name: 'department_id', label: 'Department', type: 'select', optionsKey: 'departments', required: true },
        { name: 'description', label: 'Description', type: 'textarea' },
      ]}
    />
  );
}
