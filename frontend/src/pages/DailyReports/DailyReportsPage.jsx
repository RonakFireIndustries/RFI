import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { Plus, Search, Filter, CheckCircle, Clock, XCircle, AlertTriangle } from 'lucide-react';
import { useDailyReportsStore } from '../../store/dailyReportStore';
import { useDepartmentStore } from '../../store/departmentStore';
import { useSiteStore } from '../../store/siteStore';
import DataTable from '../../components/Shared/DataTable';

export default function DailyReportsPage() {
  const { items, loading, fetchItems } = useDailyReportsStore();
  const { items: sites, fetchItems: fetchSites } = useSiteStore();
  
  const [search, setSearch] = useState('');
  const [siteId, setSiteId] = useState('');
  const [status, setStatus] = useState('');

  useEffect(() => {
    fetchSites({ per_page: 100 });
  }, [fetchSites]);

  useEffect(() => {
    fetchItems({ site_id: siteId || undefined, status: status || undefined });
  }, [fetchItems, siteId, status]);

  const columns = [
    { 
      header: 'Date', 
      accessorKey: 'date',
      cell: ({ getValue }) => {
        const val = getValue();
        return val ? new Date(val).toLocaleDateString() : '--';
      }
    },
    { 
      header: 'Employee', 
      accessorKey: 'employee.full_name' 
    },
    { 
      header: 'Site', 
      accessorKey: 'site.name',
      cell: ({ row }) => {
        const site = row.original.site;
        return site ? `${site.name} (${site.code})` : 'N/A';
      }
    },
    { 
      header: 'Hours', 
      accessorKey: 'hours_worked' 
    },
    { 
      header: 'Status', 
      accessorKey: 'status',
      cell: ({ row }) => {
        let badgeClass = 'bg-gray-100 text-gray-800';
        let Icon = Clock;
        
        switch (row.status) {
          case 'Approved':
            badgeClass = 'bg-green-100 text-green-800';
            Icon = CheckCircle;
            break;
          case 'Submitted':
            badgeClass = 'bg-blue-100 text-blue-800';
            Icon = CheckCircle;
            break;
          case 'Rejected':
            badgeClass = 'bg-red-100 text-red-800';
            Icon = XCircle;
            break;
          case 'Rework Required':
            badgeClass = 'bg-orange-100 text-orange-800';
            Icon = AlertTriangle;
            break;
        }

        return (
          <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${badgeClass}`}>
            <Icon className="w-3 h-3 mr-1" />
            {row.status}
          </span>
        );
      }
    },
    {
      id: 'actions',
      header: 'Actions',
      enableSorting: false,
      cell: ({ row }) => (
        <div className="flex justify-end gap-2">
          <Link 
            to={`/dashboard/daily-reports/${row.original.id}`}
            className="rounded-md border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50"
          >
            Review
          </Link>
        </div>
      )
    }
  ];

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Daily Progress Reports</h1>
          <p className="mt-1 text-sm text-gray-500">Manage and review employee daily work logs.</p>
        </div>
        <Link 
          to="/dashboard/daily-reports/new"
          className="inline-flex items-center justify-center gap-2 rounded-md bg-[#1a56db] px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 shadow-sm"
        >
          <Plus className="h-4 w-4" />
          Submit Report
        </Link>
      </div>

      <div className="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex flex-col md:flex-row gap-4">
        <div className="flex-1 relative">
          <Search className="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
          <input
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Search by employee..."
            className="w-full rounded-lg border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm focus:border-[#1a56db] focus:ring-1 focus:ring-[#1a56db]"
          />
        </div>
        <div className="w-full md:w-64">
          <select 
            value={siteId}
            onChange={(e) => setSiteId(e.target.value)}
            className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#1a56db] focus:ring-1 focus:ring-[#1a56db]"
          >
            <option value="">All Sites</option>
            {sites.map(site => (
              <option key={site.id} value={site.id}>{site.name}</option>
            ))}
          </select>
        </div>
        <div className="w-full md:w-48">
          <select 
            value={status}
            onChange={(e) => setStatus(e.target.value)}
            className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#1a56db] focus:ring-1 focus:ring-[#1a56db]"
          >
            <option value="">All Statuses</option>
            <option value="Draft">Draft</option>
            <option value="Submitted">Submitted</option>
            <option value="Approved">Approved</option>
            <option value="Rework Required">Rework Required</option>
            <option value="Rejected">Rejected</option>
          </select>
        </div>
      </div>

      {loading ? (
        <div className="rounded-xl border border-gray-200 bg-white p-8 text-center text-sm text-gray-500 shadow-sm">Loading daily reports...</div>
      ) : (
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <DataTable columns={columns} data={items} globalFilter={search} onGlobalFilterChange={setSearch} />
        </div>
      )}
    </div>
  );
}
