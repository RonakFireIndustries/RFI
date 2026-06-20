import ModuleListPage from '../../ERP/ModuleListPage';
import { useInventoryLocationStore } from '../../../store/inventoryLocationStore';

const typeColors = {
  warehouse: 'bg-purple-50 text-purple-700',
  site: 'bg-orange-50 text-orange-700',
  workshop: 'bg-teal-50 text-teal-700',
  store: 'bg-pink-50 text-pink-700',
};

export default function LocationsPage() {
  return (
    <ModuleListPage
      title="Inventory Locations"
      description="Manage warehouses, sites, workshops, and stores"
      store={useInventoryLocationStore}
      detailBasePath="/dashboard/inventory/locations"
      searchPlaceholder="Search locations..."
      columns={[
        { accessor: 'name', header: 'Name' },
        { accessor: 'code', header: 'Code' },
        {
          accessor: 'type',
          header: 'Type',
          cell: (row) => (
            <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium capitalize ${typeColors[row.type] || 'bg-gray-50 text-gray-700'}`}>
              {row.type}
            </span>
          ),
        },
        { accessor: 'address', header: 'Address' },
        { accessor: 'contact_person', header: 'Contact' },
        { accessor: 'phone', header: 'Phone' },
        { accessor: 'stock_count', header: 'Stock Items' },
        {
          accessor: 'status',
          header: 'Status',
          cell: (row) => (
            <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${row.status === 'active' ? 'bg-green-50 text-green-700' : 'bg-gray-50 text-gray-500'
              }`}>
              {row.status}
            </span>
          ),
        },
      ]}
      fields={[
        { label: 'Location Details', type: 'heading' },
        { name: 'name', label: 'Name', type: 'text', required: true },
        { name: 'code', label: 'Code', type: 'text', required: true },
        {
          name: 'type', label: 'Type', type: 'select', required: true, options: [
            { value: 'warehouse', name: 'Warehouse' },
            { value: 'site', name: 'Site' },
            { value: 'workshop', name: 'Workshop' },
            { value: 'store', name: 'Store' },
          ]
        },
        { label: 'Contact Information', type: 'heading' },
        { name: 'address', label: 'Address', type: 'text', fullWidth: true },
        { name: 'contact_person', label: 'Contact Person', type: 'text' },
        { name: 'phone', label: 'Phone', type: 'text' },
        { name: 'email', label: 'Email', type: 'text' },
        { label: 'Settings', type: 'heading' },
        {
          name: 'status', label: 'Status', type: 'select', options: [
            { value: 'active', name: 'Active' },
            { value: 'inactive', name: 'Inactive' },
          ]
        },
        { name: 'notes', label: 'Notes', type: 'text', fullWidth: true },
      ]}
    />
  );
}
