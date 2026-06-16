import ModuleDetailPage from '../ERP/ModuleDetailPage';
import { useInventoryStore } from '../../store/inventoryStore';

export default function InventoryDetail() {
  return (
    <ModuleDetailPage
      title={(inventory) => `${inventory.product?.name || 'Inventory'} at ${inventory.warehouse?.name || inventory.branch?.name || 'Warehouse'}`}
      store={useInventoryStore}
      backPath="/dashboard/inventory"
      fields={[
        { label: 'Product', path: 'product.name' },
        { label: 'SKU', path: 'product.sku' },
        { label: 'Warehouse', render: (inventory) => inventory.warehouse?.name || inventory.branch?.name || '-' },
        { label: 'Quantity', path: 'quantity' },
      ]}
      sections={[
        {
          title: 'Transactions',
          path: 'transactions',
          emptyText: 'No transaction history.',
          columns: [
            { header: 'Type', accessorKey: 'type' },
            { header: 'Quantity', accessorKey: 'quantity' },
            { header: 'Notes', accessorKey: 'notes' },
          ],
        },
      ]}
    />
  );
}
