import { useMemo } from 'react';
import ModuleDetailPage from '../ERP/ModuleDetailPage';
import { useProductsStore } from '../../store/productsStore';
import { useAuthStore } from '../../store/authStore';

const FINANCE_ROLES = ['Admin', 'Accountant'];

export default function ProductDetail() {
  const userRoles = useAuthStore((s) => s.roles);
  const canFinance = userRoles.some((r) => FINANCE_ROLES.includes(r));

  const fields = useMemo(() => {
    const flds = [
      { label: 'SKU', path: 'sku' },
      { label: 'Category', path: 'category.name' },
      { label: 'Supplier', path: 'supplier.name' },
      { label: 'Status', path: 'status' },
    ];
    if (canFinance) {
      flds.splice(3, 0, { label: 'Purchase Price', path: 'purchase_price' });
      flds.splice(4, 0, { label: 'Selling Price', path: 'selling_price' });
    }
    return flds;
  }, [canFinance]);

  return (
    <ModuleDetailPage
      title={(product) => product.name || `Product #${product.id}`}
      store={useProductsStore}
      backPath="/dashboard/products"
      fields={fields}
      sections={[
        {
          title: 'Stock By Location',
          path: 'stock',
          emptyText: 'No stock records.',
          columns: [
            { header: 'Location', accessorKey: 'locationable.name' },
            { header: 'Quantity', accessorKey: 'quantity' },
            { header: 'Available', accessorKey: 'available_quantity' },
            { header: 'Reserved', accessorKey: 'reserved_quantity' },
          ],
        },
      ]}
    />
  );
}
