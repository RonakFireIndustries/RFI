import ModuleListPage from '../../ERP/ModuleListPage';
import { useUnitStore } from '../../../store/unitStore';

export default function UnitsPage() {
  return (
    <ModuleListPage
      title="Units"
      description="Manage measurement units for products"
      store={useUnitStore}
      detailBasePath="/dashboard/inventory/units"
      searchPlaceholder="Search units..."
      columns={[
        { accessor: 'name', header: 'Name' },
        { accessor: 'code', header: 'Code' },
        { accessor: 'type', header: 'Type' },
        {
          accessor: 'status',
          header: 'Status',
          cell: (row) => (
            <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${
              row.status === 'active' ? 'bg-green-50 text-green-700' : 'bg-gray-50 text-gray-500'
            }`}>
              {row.status}
            </span>
          ),
        },
      ]}
      fields={[
        { name: 'name', label: 'Name', type: 'text', required: true },
        { name: 'code', label: 'Code', type: 'text', required: true },
        { name: 'type', label: 'Type', type: 'text' },
        { name: 'status', label: 'Status', type: 'select', options: [
          { value: 'active', name: 'Active' },
          { value: 'inactive', name: 'Inactive' },
        ]},
      ]}
    />
  );
}
