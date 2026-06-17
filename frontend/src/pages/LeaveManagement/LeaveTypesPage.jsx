import React, { useEffect, useState } from 'react';
import { Plus, Settings } from 'lucide-react';
import { useLeaveTypeStore } from '../../store/leaveTypeStore';
import DataTable from '../../components/Shared/DataTable';

export default function LeaveTypesPage() {
  const { items, fetchItems, loading } = useLeaveTypeStore();

  useEffect(() => {
    fetchItems({ per_page: 100 });
  }, [fetchItems]);

  const columns = [
    { header: 'Name', accessorKey: 'name' },
    { header: 'Code', accessorKey: 'code' },
    { header: 'Annual Allocation', accessorKey: 'annual_allocation' },
    { header: 'Carry Forward', accessorKey: 'carry_forward', cell: (row) => row.carry_forward ? 'Yes' : 'No' },
    { header: 'Requires Approval', accessorKey: 'requires_approval', cell: (row) => row.requires_approval ? 'Yes' : 'No' },
    { 
      header: 'Status', 
      accessorKey: 'status',
      cell: (row) => (
        <span className={`px-2 py-1 text-xs rounded-full ${row.status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
          {row.status}
        </span>
      )
    }
  ];

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-800">Leave Types</h1>
          <p className="text-gray-500 text-sm mt-1">Configure organizational leave rules.</p>
        </div>
        <button className="flex items-center bg-[#1a56db] text-white px-4 py-2 rounded-lg hover:bg-[#1546b5] transition-colors">
          <Plus className="w-4 h-4 mr-2" />
          Add Leave Type
        </button>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        {loading ? (
          <div className="text-center py-10 text-gray-500">Loading leave types...</div>
        ) : (
          <DataTable 
            columns={columns} 
            data={items} 
            emptyText="No leave types configured." 
          />
        )}
      </div>
    </div>
  );
}
