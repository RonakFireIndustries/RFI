import ModuleDetailPage from '../ERP/ModuleDetailPage';
import Money from '../../components/Shared/Money';
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
        {
          // The accounting ledger this customer posts against. Created
          // automatically on save; "Not linked" only before the backfill ran.
          label: 'Ledger',
          render: (customer) =>
            customer.ledger
              ? `${customer.ledger.name} · ${customer.ledger.group || '—'}`
              : 'Not linked to accounts yet',
        },
        { label: 'Orders', render: (customer) => customer.orders_count ?? customer.sales_orders?.length ?? 0 },
        { label: 'Outstanding', render: (customer) => <Money value={customer.outstanding_balance ?? customer.balance} /> },
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
            {
              header: 'Total',
              accessorKey: 'total_amount',
              cell: ({ getValue }) => <Money value={getValue()} />,
            },
          ],
        },
      ]}
    />
  );
}
