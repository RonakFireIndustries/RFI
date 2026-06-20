import ModuleDetailPage from '../ERP/ModuleDetailPage';
import { useCustomersStore } from '../../store/customersStore';

export default function CustomerProfile() {
  return (
    <ModuleDetailPage
      title={(customer) => customer.name || `Customer #${customer.id}`}
      store={useCustomersStore}
      backPath="/dashboard/customers"
      fields={[
        { label: 'Email', path: 'email' },
        { label: 'Phone', path: 'phone' },
        { label: 'GST Number', path: 'gst_number' },
        { label: 'Orders', render: (customer) => customer.orders_count ?? customer.sales_orders?.length ?? 0 },
        { label: 'Outstanding', render: (customer) => Number(customer.outstanding_balance || customer.balance || 0).toFixed(2) },
      ]}
      sections={[
        {
          title: 'Sales Orders',
          path: 'sales_orders',
          emptyText: 'No sales orders.',
          columns: [
            { header: 'Order #', accessorKey: 'id' },
            { header: 'Status', accessorKey: 'status' },
            { header: 'Products', accessorKey: 'products_count' },
            { header: 'Total', accessorKey: 'total_amount' },
          ],
        },
      ]}
    />
  );
}
