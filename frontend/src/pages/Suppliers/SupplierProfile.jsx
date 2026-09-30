import { useMemo } from 'react';
import ModuleDetailPage from '../ERP/ModuleDetailPage';
import { useSuppliersStore } from '../../store/suppliersStore';
import Money from '../../components/Shared/Money';
import { usePriceVisibility } from '../../hooks/usePriceVisibility';

export default function SupplierProfile() {
  const { canSeePrices } = usePriceVisibility();

  const productColumns = useMemo(() => {
    const cols = [
      { header: 'SKU', accessorKey: 'sku' },
      { header: 'Product', accessorKey: 'name' },
      { header: 'Category', accessorKey: 'category.name' },
    ];
    if (canSeePrices) {
      cols.push({
        header: 'Selling Price',
        accessorKey: 'selling_price',
        cell: ({ getValue }) => <Money value={getValue()} />,
      });
    }
    return cols;
  }, [canSeePrices]);

  return (
    <ModuleDetailPage
      title={(supplier) => supplier.name || `Supplier #${supplier.id}`}
      store={useSuppliersStore}
      backPath="/dashboard/suppliers"
      fields={[
        { label: 'Email', path: 'email' },
        { label: 'Phone', path: 'phone' },
        { label: 'GST Number', path: 'gst_number' },
        { label: 'Products', render: (supplier) => supplier.products_count ?? supplier.products?.length ?? 0 },
        { label: 'Balance', render: (supplier) => <Money value={supplier.balance} /> },
      ]}
      sections={[
        {
          title: 'Products',
          path: 'products',
          emptyText: 'No products linked to this supplier.',
          columns: productColumns,
        },
        {
          title: 'Purchase Orders',
          path: 'purchase_orders',
          emptyText: 'No purchase orders.',
          columns: [
            { header: 'Order #', accessorKey: 'id' },
            { header: 'Status', accessorKey: 'status' },
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
