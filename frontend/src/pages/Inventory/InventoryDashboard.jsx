import { useEffect, useState } from 'react';
import ModuleListPage from '../ERP/ModuleListPage';
import api from '../../services/api';
import { unwrapList } from '../../services/resourceHelpers';
import { useInventoryStore } from '../../store/inventoryStore';

export default function InventoryDashboard() {
  const [lookups, setLookups] = useState({ products: [], warehouses: [] });

  useEffect(() => {
    Promise.all([api.get('/products'), api.get('/branches')])
      .then(([products, branches]) => {
        setLookups({ products: unwrapList(products.data), warehouses: unwrapList(branches.data) });
      })
      .catch((error) => console.error('Failed to load inventory lookups', error));
  }, []);

  return (
    <ModuleListPage
      title="Inventory"
      description="Track product quantities by warehouse and inspect transaction history."
      store={useInventoryStore}
      detailBasePath="/dashboard/inventory"
      searchPlaceholder="Search inventory..."
      lookups={lookups}
      columns={[
        { header: 'Product', accessor: 'product.name' },
        { header: 'SKU', accessor: 'product.sku' },
        { header: 'Warehouse', cellValue: (row) => row.warehouse?.name || row.branch?.name || '-' },
        { header: 'Quantity', accessor: 'quantity' },
      ]}
      fields={[
        { name: 'product_id', label: 'Product', type: 'select', optionsKey: 'products', required: true },
        { name: 'branch_id', label: 'Warehouse', type: 'select', optionsKey: 'warehouses', valuePath: 'warehouse_id', required: true },
        { name: 'quantity', label: 'Quantity', type: 'number', required: true, defaultValue: 0 },
      ]}
    />
  );
}
