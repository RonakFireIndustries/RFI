import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { Plus, Search, CheckCircle, Clock, XCircle, AlertTriangle } from 'lucide-react';
import { useDailyReportsStore } from '../../store/dailyReportStore';
import { useSiteStore } from '../../store/siteStore';
import { useAuthStore } from '../../store/authStore';
import { employeeSiteService } from '../../services/employeeSiteService';
import DataTable from '../../components/Shared/DataTable';
import { formatHoursOrDash } from '../../utils/duration';

const STATUS_BADGES = {
  Approved: { className: 'bg-green-100 text-green-800', Icon: CheckCircle },
  Submitted: { className: 'bg-blue-100 text-blue-800', Icon: CheckCircle },
  Rejected: { className: 'bg-red-100 text-red-800', Icon: XCircle },
  'Rework Required': { className: 'bg-orange-100 text-orange-800', Icon: AlertTriangle },
};

export default function DailyReportsPage() {
  const { items, loading, fetchItems } = useDailyReportsStore();
  const { items: sites, fetchItems: fetchSites } = useSiteStore();
  const { user, permissions } = useAuthStore();

  const [search, setSearch] = useState('');
  const [siteId, setSiteId] = useState('');
  const [status, setStatus] = useState('');
  const [day, setDay] = useState('');
  const [fromDate, setFromDate] = useState('');
  const [toDate, setToDate] = useState('');

  const employeeId = user?.employee?.id;
  const canViewAllSites = !!(user?.is_super_admin || permissions.includes('sites.view'));

  useEffect(() => {
    if (!canViewAllSites) {
      if (!employeeId) return;
      let cancelled = false;
      employeeSiteService.getCurrentSite(employeeId)
        .then((data) => {
          if (cancelled) return;
          const site = data?.site || (data?.id ? data : null);
          if (site?.id) setSiteId(String(site.id));
        })
        .catch(() => {});
      return () => { cancelled = true; };
    }
    fetchSites({ per_page: 100 });
  }, [canViewAllSites, employeeId, fetchSites]);

  useEffect(() => {
    fetchItems({
      site_id: siteId || undefined,
      status: status || undefined,
      // A single day and a range are mutually exclusive (the UI clears one when
      // the other is set), which keeps the server-side filter unambiguous.
      date: day || undefined,
      start_date: !day && fromDate ? fromDate : undefined,
      end_date: !day && toDate ? toDate : undefined,
    });
  }, [fetchItems, siteId, status, day, fromDate, toDate]);

  const hasFilters = Boolean(search || siteId || status || day || fromDate || toDate);

  const clearFilters = () => {
    setSearch('');
    setSiteId('');
    setStatus('');
    setDay('');
    setFromDate('');
    setToDate('');
  };

  const handleDayChange = (value) => {
    setDay(value);
    if (value) {
      setFromDate('');
      setToDate('');
    }
  };

  const handleFromChange = (value) => {
    setFromDate(value);
    if (value) setDay('');
  };

  const handleToChange = (value) => {
    setToDate(value);
    if (value) setDay('');
  };

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
      cell: ({ row }) => row.original.site?.name || 'N/A'
    },
    {
      header: 'Hours',
      accessorKey: 'hours_worked',
      cell: ({ row }) => formatHoursOrDash(row.original.hours_worked)
    },
    {
      header: 'Status',
      accessorKey: 'status',
      cell: ({ row }) => {
        const status = row.original.status;
        const badge = STATUS_BADGES[status] || { className: 'bg-gray-100 text-gray-800', Icon: Clock };
        const Icon = badge.Icon;

        return (
          <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium whitespace-nowrap ${badge.className}`}>
            <Icon className="w-3 h-3 mr-1" />
            {status || 'Unknown'}
          </span>
        );
      }
    },
    {
      id: 'actions',
      header: 'Actions',
      enableSorting: false,
      cell: ({ row }) => {
        const report = row.original;
        const isOwn = report.employee_id === employeeId;

        return (
          <div className="flex justify-end gap-2 whitespace-nowrap">
            {report.can_edit && (
              <Link
                to={`/dashboard/daily-reports/${report.id}/edit`}
                className="rounded-md border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100"
              >
                {report.status === 'Rework Required' ? 'Resubmit' : 'Edit'}
              </Link>
            )}
            {isOwn && report.status === 'Rejected' && (
              <Link
                to={`/dashboard/daily-reports/new?date=${report.date}`}
                className="rounded-md border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-100"
              >
                New Report
              </Link>
            )}
            <Link
              to={`/dashboard/daily-reports/${report.id}`}
              className="rounded-md border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50"
            >
              View
            </Link>
          </div>
        );
      }
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
          className="inline-flex items-center justify-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground hover:bg-primary/90 shadow-sm"
        >
          <Plus className="h-4 w-4" />
          Submit Report
        </Link>
      </div>

      <div className="bg-white p-4 rounded-xl shadow-sm border border-gray-200 space-y-3">
        <div className="flex flex-col md:flex-row gap-3">
          <div className="flex-1 relative">
            <Search className="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
            <input
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search by employee..."
              className="w-full rounded-lg border border-input bg-card py-2 pl-9 pr-3 text-sm focus:border-primary focus:ring-1 focus:ring-primary"
            />
          </div>
          {canViewAllSites && (
            <div className="w-full md:w-56">
              <select
                value={siteId}
                onChange={(e) => setSiteId(e.target.value)}
                className="w-full rounded-lg border border-input px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary"
              >
                <option value="">All Sites</option>
                {sites.map(site => (
                  <option key={site.id} value={site.id}>{site.name}</option>
                ))}
              </select>
            </div>
          )}
          <div className="w-full md:w-48">
            <select
              value={status}
              onChange={(e) => setStatus(e.target.value)}
              className="w-full rounded-lg border border-input px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary"
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

        <div className="flex flex-col sm:flex-row sm:items-end gap-3 pt-1 border-t border-gray-100">
          <div>
            <label className="block text-xs font-semibold text-gray-500 mb-1">Day</label>
            <input
              type="date"
              value={day}
              onChange={(e) => handleDayChange(e.target.value)}
              className="w-full rounded-lg border border-input px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary"
            />
          </div>
          <div>
            <label className="block text-xs font-semibold text-gray-500 mb-1">From</label>
            <input
              type="date"
              value={fromDate}
              max={toDate || undefined}
              onChange={(e) => handleFromChange(e.target.value)}
              className="w-full rounded-lg border border-input px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary"
            />
          </div>
          <div>
            <label className="block text-xs font-semibold text-gray-500 mb-1">To</label>
            <input
              type="date"
              value={toDate}
              min={fromDate || undefined}
              onChange={(e) => handleToChange(e.target.value)}
              className="w-full rounded-lg border border-input px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary"
            />
          </div>
          {hasFilters && (
            <button
              type="button"
              onClick={clearFilters}
              className="text-sm font-semibold text-gray-600 hover:text-gray-900 underline"
            >
              Clear filters
            </button>
          )}
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
