import React, { useEffect, useMemo } from 'react';
import { useLeaveBalanceStore } from '../../store/leaveBalanceStore';
import DataTable from '../../components/Shared/DataTable';

export default function LeaveBalancesPage() {
  const { items, fetchItems, loading } = useLeaveBalanceStore();

  useEffect(() => {
    fetchItems({ per_page: 100 });
  }, [fetchItems]);

  const summary = useMemo(() => {
    if (!items || items.length === 0) return null;
    const totalAllocated = items.reduce((s, i) => s + parseFloat(i.allocated || 0), 0);
    const totalUsed = items.reduce((s, i) => s + parseFloat(i.used || 0), 0);
    const totalRemaining = items.reduce((s, i) => s + parseFloat(i.remaining || 0), 0);
    return { totalAllocated, totalUsed, totalRemaining };
  }, [items]);

  const columns = [
    {
      header: 'Employee',
      accessorKey: 'employee.full_name',
      cell: ({ row }) => {
        const emp = row.original.employee;
        return (
          <div>
            <p className="font-medium text-gray-900">{emp?.full_name || emp?.name || 'N/A'}</p>
            {emp?.department && (
              <p className="text-xs text-gray-500">{emp.department.name}</p>
            )}
          </div>
        );
      },
    },
    { header: 'Leave Type', accessorKey: 'leave_type.name' },
    { header: 'Year', accessorKey: 'year' },
    {
      header: 'Allocated',
      accessorKey: 'allocated',
      cell: ({ getValue }) => <span className="font-medium">{getValue() ?? '-'}</span>,
    },
    {
      header: 'Used',
      accessorKey: 'used',
      cell: ({ getValue }) => <span className="font-medium text-orange-600">{getValue() ?? '-'}</span>,
    },
    {
      header: 'Remaining',
      accessorKey: 'remaining',
      cell: ({ getValue }) => <span className="font-semibold text-green-600">{getValue() ?? '-'}</span>,
    },
    {
      header: 'Carry Fwd',
      accessorKey: 'carry_forward',
      cell: ({ getValue }) => getValue() ?? '-',
    },
    {
      header: 'Expired',
      accessorKey: 'expired',
      cell: ({ getValue }) => <span className="text-red-500">{getValue() ?? '-'}</span>,
    },
  ];

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Leave Balances</h1>
          <p className="text-gray-500 text-sm mt-1">Monitor employee leave allocations and usage across all leave types.</p>
        </div>
      </div>

      {/* Summary Cards */}
      {summary && (
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p className="text-xs text-gray-500 uppercase tracking-wider font-semibold">Total Allocated</p>
            <p className="text-2xl font-bold text-gray-900 mt-1">{summary.totalAllocated.toFixed(1)}</p>
          </div>
          <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p className="text-xs text-gray-500 uppercase tracking-wider font-semibold">Total Used</p>
            <p className="text-2xl font-bold text-orange-600 mt-1">{summary.totalUsed.toFixed(1)}</p>
          </div>
          <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p className="text-xs text-gray-500 uppercase tracking-wider font-semibold">Total Remaining</p>
            <p className="text-2xl font-bold text-green-600 mt-1">{summary.totalRemaining.toFixed(1)}</p>
          </div>
        </div>
      )}

      <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        {loading ? (
          <div className="text-center py-10 text-gray-500">Loading balances...</div>
        ) : (
          <DataTable
            columns={columns}
            data={items}
            emptyText="No leave balances found. Balances are created when leave types exist and employees are assigned."
          />
        )}
      </div>
    </div>
  );
}
