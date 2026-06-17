import React from 'react';
import ModuleListPage from '../ERP/ModuleListPage';
import { useAttendanceStore } from '../../store/attendanceStore';

export default function AttendancePage() {
  const fields = [
    { name: 'employee_id', label: 'Employee', type: 'select', optionsKey: 'employees', required: true, lookupDisplay: 'full_name' },
    { name: 'date', label: 'Date', type: 'date', required: true },
    { name: 'check_in', label: 'Check In', type: 'time', emptyAsNull: true },
    { name: 'check_out', label: 'Check Out', type: 'time', emptyAsNull: true },
    { name: 'site_id', label: 'Site', type: 'select', optionsKey: 'sites', emptyAsNull: true, lookupDisplay: 'name' },
    { name: 'shift_id', label: 'Shift', type: 'select', optionsKey: 'shifts', emptyAsNull: true, lookupDisplay: 'name' },
    { name: 'status', label: 'Status', type: 'select', required: true, defaultValue: 'Present', options: [
        { value: 'Present', label: 'Present' },
        { value: 'Absent', label: 'Absent' },
        { value: 'Half Day', label: 'Half Day' },
        { value: 'Late', label: 'Late' }
      ]
    },
    { name: 'remarks', label: 'Remarks', type: 'text', emptyAsNull: true },
  ];

  return (
    <div className="flex flex-col gap-6">
      <ModuleListPage
        title="Attendance Log"
        store={useAttendanceStore}
        fields={fields}
      />
    </div>
  );
}
