import ModuleListPage from '../../ERP/ModuleListPage';
import { useUnitConversionStore } from '../../../store/unitConversionStore';
import { useUnitStore } from '../../../store/unitStore';
import { useEffect } from 'react';

export default function ConversionsPage() {
  const { items: units, fetchItems } = useUnitStore();

  useEffect(() => {
    fetchItems();
  }, [fetchItems]);

  return (
    <ModuleListPage
      title="Unit Conversions"
      description="Manage conversion rates between units (e.g., KG to Gram)"
      store={useUnitConversionStore}
      detailBasePath="/dashboard/inventory/conversions"
      searchPlaceholder="Search conversions..."
      columns={[
        {
          accessor: 'from_unit',
          header: 'From Unit',
          cell: (row) => row.from_unit?.name || `Unit #${row.from_unit_id}`,
        },
        {
          accessor: 'to_unit',
          header: 'To Unit',
          cell: (row) => row.to_unit?.name || `Unit #${row.to_unit_id}`,
        },
        {
          accessor: 'conversion_factor',
          header: 'Conversion Factor',
          cell: (row) => Number(row.conversion_factor).toLocaleString(undefined, { maximumFractionDigits: 6 }),
        },
      ]}
      fields={[
        { name: 'from_unit_id', label: 'From Unit', type: 'select', required: true, optionsKey: 'fromUnits' },
        { name: 'to_unit_id', label: 'To Unit', type: 'select', required: true, optionsKey: 'toUnits' },
        { name: 'conversion_factor', label: 'Conversion Factor', type: 'number', required: true, step: '0.000001' },
      ]}
      lookups={{
        fromUnits: units,
        toUnits: units,
      }}
    />
  );
}
