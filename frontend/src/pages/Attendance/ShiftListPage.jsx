import React from 'react';
import ModuleListPage from '../ERP/ModuleListPage';
import { useShiftsStore } from '../../store/shiftStore';

export default function ShiftListPage() {
  const fields = [
    { name: 'name', label: 'Shift Name', required: true },
    { name: 'start_time', label: 'Start Time', type: 'time', required: true },
    { name: 'end_time', label: 'End Time', type: 'time', required: true },
    { name: 'grace_period', label: 'Grace Period (mins)', type: 'number', emptyAsNull: true },
    { name: 'late_threshold', label: 'Late Threshold (mins)', type: 'number', emptyAsNull: true },
    { name: 'half_day_threshold', label: 'Half Day Threshold (mins)', type: 'number', emptyAsNull: true },
    { name: 'status', label: 'Status', type: 'select', required: true, defaultValue: 'Active', options: [{ value: 'Active', label: 'Active' }, { value: 'Inactive', label: 'Inactive' }] },
  ];

  const columns = [
    { header: 'Name', accessor: 'name' },
    { header: 'Start Time', accessor: 'start_time' },
    { header: 'End Time', accessor: 'end_time' },
    { header: 'Grace Period', accessor: 'grace_period' },
    { header: 'Status', accessor: 'status' },
  ];

  return (
    <ModuleListPage
      title="Shifts"
      store={useShiftsStore}
      columns={columns}
      fields={fields}
    />
  );
}
