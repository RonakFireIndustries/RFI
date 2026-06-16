import ModuleDetailPage from '../ERP/ModuleDetailPage';
import { useSuppliersStore } from '../../store/suppliersStore';

export default function SupplierProfile() {
  return (
    <ModuleDetailPage
      title={(supplier) => supplier.name || `Supplier #${supplier.id}`}
      store={useSuppliersStore}
      backPath="/dashboard/suppliers"
      fields={[
        { label: 'Email', path: 'email' },
        { label: 'Phone', path: 'phone' },
        { label: 'GST Number', path: 'gst_number' },
        { label: 'Branch', path: 'branch.name' },
        { label: 'Products', render: (supplier) => supplier.products_count ?? supplier.products?.length ?? 0 },
        { label: 'Balance', render: (supplier) => Number(supplier.balance || 0).toFixed(2) },
      ]}
      sections={[
        {
          title: 'Products',
          path: 'products',
          emptyText: 'No products linked to this supplier.',
          columns: [
            { header: 'SKU', accessorKey: 'sku' },
            { header: 'Product', accessorKey: 'name' },
            { header: 'Category', accessorKey: 'category.name' },
            { header: 'Selling Price', accessorKey: 'selling_price' },
          ],
        },
        {
          title: 'Purchase Orders',
          path: 'purchase_orders',
          emptyText: 'No purchase orders.',
          columns: [
            { header: 'Order #', accessorKey: 'id' },
            { header: 'Status', accessorKey: 'status' },
            { header: 'Total', accessorKey: 'total_amount' },
          ],
        },
      ]}
    />
  );
}
