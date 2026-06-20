import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { Plus, Eye, XCircle } from 'lucide-react';
import { leaveRequestsService } from '../../services/leaveRequestsService';
import DataTable from '../../components/Shared/DataTable';

export default function MyLeaveRequestsPage() {
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);

  const fetchMyLeaves = async () => {
    setLoading(true);
    try {
      const response = await leaveRequestsService.myList({ per_page: 100 });
      const data = response?.data;
      const list = data?.leave_requests || (data?.data?.leave_requests) || [];
      setItems(list);
    } catch (err) {
      setItems([]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchMyLeaves();
  }, []);

  const handleCancel = async (id) => {
    if (!confirm('Are you sure you want to cancel this leave request?')) return;
    const comments = prompt('Please provide a reason for cancellation:');
    if (!comments) return;
    try {
      await leaveRequestsService.cancel(id, { comments });
      fetchMyLeaves();
    } catch (error) {
      alert('Failed to cancel: ' + (error.response?.data?.message || error.message));
    }
  };

  const activeItems = (items || []).filter((item) => item.status !== 'Cancelled');

  const columns = [
    { header: 'Type', accessorKey: 'leave_type.name' },
    {
      header: 'From',
      accessorKey: 'start_date',
      cell: ({ getValue }) => {
        const v = getValue();
        return v ? new Date(v).toLocaleDateString() : '--';
      }
    },
    {
      header: 'To',
      accessorKey: 'end_date',
      cell: ({ getValue }) => {
        const v = getValue();
        return v ? new Date(v).toLocaleDateString() : '--';
      }
    },
    { header: 'Days', accessorKey: 'total_days' },
    {
      header: 'Status',
      accessorKey: 'status',
      cell: ({ row }) => {
        const s = row.original.status;
        const badgeClass = s === 'Approved' ? 'bg-green-100 text-green-800' :
          s === 'Rejected' ? 'bg-red-100 text-red-800' :
          s === 'Submitted' ? 'bg-blue-100 text-blue-800' :
          'bg-gray-100 text-gray-800';
        return (
          <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${badgeClass}`}>
            {s}
          </span>
        );
      }
    },
    {
      id: 'actions',
      header: 'Actions',
      cell: ({ row }) => (
        <div className="flex justify-end gap-2">
          <Link
            to={`/dashboard/my-leaves/${row.original.id}`}
            className="inline-flex items-center rounded-md border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50"
          >
            <Eye className="w-3.5 h-3.5 mr-1" />
            View
          </Link>
          {(row.original.status === 'Submitted' || row.original.status === 'Draft') && (
            <button
              onClick={() => handleCancel(row.original.id)}
              className="inline-flex items-center rounded-md border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50"
            >
              <XCircle className="w-3.5 h-3.5 mr-1" />
              Cancel
            </button>
          )}
        </div>
      )
    }
  ];

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">My Leaves</h1>
          <p className="text-gray-500 text-sm mt-1">View and manage your leave requests.</p>
        </div>
        <Link
          to="/dashboard/my-leaves/new"
          className="inline-flex items-center justify-center sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors"
        >
          <Plus className="w-4 h-4 mr-2" />
          Apply Leave
        </Link>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        {loading ? (
          <div className="text-center py-10 text-gray-500">Loading...</div>
        ) : (
          <DataTable
            columns={columns}
            data={activeItems}
            emptyText="No leave requests found."
          />
        )}
      </div>
    </div>
  );
}
