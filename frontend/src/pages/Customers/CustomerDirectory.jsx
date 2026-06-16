import { useEffect, useState } from 'react';
import ModuleListPage from '../ERP/ModuleListPage';
import api from '../../services/api';
import { unwrapList } from '../../services/resourceHelpers';
import { useCustomersStore } from '../../store/customersStore';

export default function CustomerDirectory() {
  const [lookups, setLookups] = useState({ branches: [] });

  useEffect(() => {
    api.get('/branches')
      .then((branches) => setLookups({ branches: unwrapList(branches.data) }))
      .catch((error) => console.error('Failed to load customer lookups', error));
  }, []);

  return (
    <ModuleListPage
      title="Customers"
      description="Manage customers and inspect their sales order history."
      store={useCustomersStore}
      detailBasePath="/dashboard/customers"
      searchPlaceholder="Search customers..."
      lookups={lookups}
      columns={[
        { header: 'Customer', accessor: 'name' },
        { header: 'Email', accessor: 'email' },
        { header: 'Phone', accessor: 'phone' },
        { header: 'Branch', accessor: 'branch.name' },
        { header: 'Orders', cellValue: (row) => row.orders_count ?? row.sales_orders?.length ?? 0 },
        { header: 'Balance', cellValue: (row) => Number(row.balance || row.outstanding_balance || 0).toFixed(2) },
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
