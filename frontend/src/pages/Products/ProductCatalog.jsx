import { useEffect, useState } from 'react';
import ModuleListPage from '../ERP/ModuleListPage';
import api from '../../services/api';
import { unwrapList } from '../../services/resourceHelpers';
import { useProductsStore } from '../../store/productsStore';

export default function ProductCatalog() {
  const [lookups, setLookups] = useState({ categories: [], suppliers: [] });

  useEffect(() => {
    Promise.all([api.get('/categories'), api.get('/suppliers')])
      .then(([categories, suppliers]) => {
        setLookups({ categories: unwrapList(categories.data), suppliers: unwrapList(suppliers.data) });
      })
      .catch((error) => console.error('Failed to load product lookups', error));
  }, []);

  return (
    <ModuleListPage
      title="Products"
      description="Manage catalog items with category, supplier, and warehouse inventory relationships."
      store={useProductsStore}
      detailBasePath="/dashboard/products"
      searchPlaceholder="Search products or SKUs..."
      lookups={lookups}
      columns={[
        { header: 'SKU', accessor: 'sku' },
        { header: 'Product', accessor: 'name' },
        { header: 'Category', accessor: 'category.name' },
        { header: 'Supplier', accessor: 'supplier.name' },
        { header: 'Stock', cellValue: (row) => row.inventory_quantity ?? 0 },
        { header: 'Selling Price', cellValue: (row) => Number(row.selling_price || 0).toFixed(2) },
        { header: 'Status', accessor: 'status' },
      ]}
      fields={[
        { name: 'sku', label: 'SKU', required: true },
        { name: 'name', label: 'Product Name', required: true },
        { name: 'category_id', label: 'Category', type: 'select', optionsKey: 'categories', emptyAsNull: true },
        { name: 'supplier_id', label: 'Supplier', type: 'select', optionsKey: 'suppliers', emptyAsNull: true },
        { name: 'purchase_price', label: 'Purchase Price', type: 'number', step: '0.01', required: true },
        { name: 'selling_price', label: 'Selling Price', type: 'number', step: '0.01', required: true },
        { name: 'gst_percentage', label: 'GST %', type: 'number', step: '0.01', defaultValue: 0 },
        { name: 'status', label: 'Status', type: 'select', defaultValue: 'active', options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }] },
      ]}
    />
  );
}
