import React, { useEffect } from 'react';
import { Link } from 'react-router-dom';
import { Plus, Eye } from 'lucide-react';
import { useLeaveRequestStore } from '../../store/leaveRequestStore';
import DataTable from '../../components/Shared/DataTable';

export default function LeaveRequestsPage() {
  const { items, fetchItems, loading } = useLeaveRequestStore();

  useEffect(() => {
    fetchItems({ per_page: 100 });
  }, [fetchItems]);

  const columns = [
    { header: 'Employee', accessorKey: 'employee.full_name', cell: ({ row }) => row.original.employee?.full_name || row.original.employee?.name || 'N/A' },
    { header: 'Type', accessorKey: 'leave_type.name' },
    { header: 'Start Date', accessorKey: 'start_date' },
    { header: 'End Date', accessorKey: 'end_date' },
    { header: 'Days', accessorKey: 'total_days' },
    {
      header: 'Status',
      accessorKey: 'status',
      cell: ({ row }) => (
        <span className={`px-2 py-1 text-xs rounded-full ${row.original.status === 'Approved' ? 'bg-green-100 text-green-800' :
            row.original.status === 'Rejected' ? 'bg-red-100 text-red-800' :
              row.original.status === 'Submitted' ? 'bg-blue-100 text-blue-800' :
                'bg-gray-100 text-gray-800'
          }`}>
          {row.original.status}
        </span>
      )
    },
    {
      id: 'actions',
      header: 'Actions',
      cell: ({ row }) => (
        <Link to={`/dashboard/leave-management/requests/${row.original.id}`} className="text-[#1a56db] hover:text-[#1546b5]">
          <Eye className="w-4 h-4" />
        </Link>
      )
    }
  ];

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div>
          <h1 className="text-2xl font-bold text-gray-800">Leave Requests</h1>
          <p className="text-gray-500 text-sm mt-1">Manage and track employee leave applications.</p>
        </div>
        <Link
          to="/dashboard/leave-management/requests/new"
          className="inline-flex items-center justify-center sm:w-auto bg-[#1a56db] text-white px-4 py-2 rounded-lg hover:bg-[#1546b5] transition-colors"
        >
          <Plus className="w-4 h-4 mr-2" />
          Apply Leave
        </Link>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        {loading ? (
          <div className="text-center py-10 text-gray-500">Loading requests...</div>
        ) : (
          <DataTable
            columns={columns}
            data={items}
            emptyText="No leave requests found."
          />
        )}
      </div>
    </div>
  );
}
