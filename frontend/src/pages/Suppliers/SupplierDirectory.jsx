import { useEffect, useState } from 'react';
import ModuleListPage from '../ERP/ModuleListPage';
import api from '../../services/api';
import { unwrapList } from '../../services/resourceHelpers';
import { useSuppliersStore } from '../../store/suppliersStore';

export default function SupplierDirectory() {
  const [lookups, setLookups] = useState({ branches: [] });

  useEffect(() => {
    api.get('/branches')
      .then((branches) => setLookups({ branches: unwrapList(branches.data) }))
      .catch((error) => console.error('Failed to load supplier lookups', error));
  }, []);

  return (
    <ModuleListPage
      title="Suppliers"
      description="Manage suppliers and inspect linked products and purchase activity."
      store={useSuppliersStore}
      detailBasePath="/dashboard/suppliers"
      searchPlaceholder="Search suppliers..."
      lookups={lookups}
      columns={[
        { header: 'Supplier', accessor: 'name' },
        { header: 'Email', accessor: 'email' },
        { header: 'Phone', accessor: 'phone' },
        { header: 'Branch', accessor: 'branch.name' },
        { header: 'Products', cellValue: (row) => row.products_count ?? row.products?.length ?? 0 },
        { header: 'Balance', cellValue: (row) => Number(row.balance || 0).toFixed(2) },
      ]}
      fields={[
        { name: 'name', label: 'Name', required: true },
        { name: 'email', label: 'Email', type: 'email', emptyAsNull: true },
        { name: 'phone', label: 'Phone', emptyAsNull: true },
        { name: 'gst_number', label: 'GST Number', emptyAsNull: true },
        { name: 'branch_id', label: 'Branch', type: 'select', optionsKey: 'branches', required: true },
        { name: 'address', label: 'Address', fullWidth: true, emptyAsNull: true },
      ]}
    />
  );
}
