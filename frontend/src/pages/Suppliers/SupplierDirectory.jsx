import ModuleListPage from '../ERP/ModuleListPage';
import { useSuppliersStore } from '../../store/suppliersStore';

export default function SupplierDirectory() {
  return (
    <ModuleListPage
      title="Suppliers"
      description="Manage suppliers and inspect linked products and purchase activity."
      store={useSuppliersStore}
      detailBasePath="/dashboard/suppliers"
      searchPlaceholder="Search suppliers..."
      columns={[
        { header: 'Supplier', accessor: 'name' },
        { header: 'Email', accessor: 'email' },
        { header: 'Phone', accessor: 'phone' },
        { header: 'Products', cellValue: (row) => row.products_count ?? row.products?.length ?? 0 },
        { header: 'Balance', cellValue: (row) => Number(row.balance || 0).toFixed(2) },
      ]}
      fields={[
        { name: 'name', label: 'Name', required: true },
        { name: 'email', label: 'Email', type: 'email', emptyAsNull: true },
        { name: 'phone', label: 'Phone', emptyAsNull: true },
        { name: 'gst_number', label: 'GST Number', emptyAsNull: true },
        { name: 'address', label: 'Address', fullWidth: true, emptyAsNull: true },
      ]}
    />
  );
}
