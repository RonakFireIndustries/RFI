import React, { useEffect } from 'react';
import { useLeaveBalanceStore } from '../../store/leaveBalanceStore';
import DataTable from '../../components/Shared/DataTable';

export default function LeaveBalancesPage() {
  const { items, fetchItems, loading } = useLeaveBalanceStore();

  useEffect(() => {
    fetchItems({ per_page: 100 });
  }, [fetchItems]);

  const columns = [
    { header: 'Employee', accessorKey: 'employee.full_name', cell: ({ row }) => row.original.employee?.full_name || row.original.employee?.name || 'N/A' },
    { header: 'Leave Type', accessorKey: 'leave_type.name' },
    { header: 'Year', accessorKey: 'year' },
    { header: 'Allocated', accessorKey: 'allocated' },
    { header: 'Used', accessorKey: 'used' },
    { header: 'Remaining', accessorKey: 'remaining' },
  ];

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-800">Leave Balances</h1>
          <p className="text-gray-500 text-sm mt-1">Monitor employee leave allocations and usage.</p>
        </div>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        {loading ? (
          <div className="text-center py-10 text-gray-500">Loading balances...</div>
        ) : (
          <DataTable 
            columns={columns} 
            data={items} 
            emptyText="No leave balances found." 
          />
        )}
      </div>
    </div>
  );
}
