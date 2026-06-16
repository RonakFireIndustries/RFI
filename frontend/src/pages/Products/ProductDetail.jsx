import ModuleDetailPage from '../ERP/ModuleDetailPage';
import { useProductsStore } from '../../store/productsStore';

export default function ProductDetail() {
  return (
    <ModuleDetailPage
      title={(product) => product.name || `Product #${product.id}`}
      store={useProductsStore}
      backPath="/dashboard/products"
      fields={[
        { label: 'SKU', path: 'sku' },
        { label: 'Category', path: 'category.name' },
        { label: 'Supplier', path: 'supplier.name' },
        { label: 'Purchase Price', path: 'purchase_price' },
        { label: 'Selling Price', path: 'selling_price' },
        { label: 'Status', path: 'status' },
      ]}
      sections={[
        {
          title: 'Inventory By Warehouse',
          path: 'inventories',
          emptyText: 'No inventory records.',
          columns: [
            { header: 'Warehouse', accessorKey: 'warehouse.name' },
            { header: 'Quantity', accessorKey: 'quantity' },
          ],
        },
      ]}
    />
  );
}
