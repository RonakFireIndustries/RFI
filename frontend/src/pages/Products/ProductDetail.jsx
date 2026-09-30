import { useMemo } from 'react';
import ModuleDetailPage from '../ERP/ModuleDetailPage';
import { useProductsStore } from '../../store/productsStore';
import Money from '../../components/Shared/Money';
import { usePriceVisibility } from '../../hooks/usePriceVisibility';

export default function ProductDetail() {
  const { canSeePrices } = usePriceVisibility();

  const fields = useMemo(() => {
    const flds = [
      { label: 'SKU', path: 'sku' },
      { label: 'Dimension', path: 'dimension' },
      { label: 'Category', path: 'category.name' },
      { label: 'Supplier', path: 'supplier.name' },
      { label: 'Status', path: 'status' },
    ];
    if (canSeePrices) {
      flds.splice(3, 0, {
        label: 'Purchase Price',
        render: (product) => <Money value={product.purchase_price} />,
      });
      flds.splice(4, 0, {
        label: 'Selling Price',
        render: (product) => <Money value={product.selling_price} />,
      });
    }
    return flds;
  }, [canSeePrices]);

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
