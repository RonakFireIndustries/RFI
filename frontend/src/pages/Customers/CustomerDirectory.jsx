import ModuleListPage from '../ERP/ModuleListPage';
import Money from '../../components/Shared/Money';
import { useCustomersStore } from '../../store/customersStore';

export default function CustomerDirectory() {
  return (
    <ModuleListPage
      title="Customers"
      description="Manage customers and inspect their sales order history."
      store={useCustomersStore}
      module="customers"
      detailBasePath="/dashboard/customers"
      searchPlaceholder="Search customers..."
      columns={[
        { header: 'Customer', accessor: 'name' },
        { header: 'Email', accessor: 'email' },
        { header: 'Phone', accessor: 'phone' },
        { header: 'Orders', cellValue: (row) => row.orders_count ?? row.sales_orders?.length ?? 0 },
        { header: 'Balance', cell: (row) => <Money value={row.balance ?? row.outstanding_balance} /> },
      ]}
      fields={[
        { name: 'name', label: 'Name', required: true },
        { name: 'email', label: 'Email', type: 'email', emptyAsNull: true },
        { name: 'phone', label: 'Phone', emptyAsNull: true },
        { name: 'gst_number', label: 'GST Number', emptyAsNull: true },
        { name: 'address', label: 'Address', fullWidth: true, emptyAsNull: true },
      ]}
    />
  );
}
