import ModuleListPage from '../ERP/ModuleListPage';
import { useHolidayStore } from '../../store/holidayStore';
import { useEffect, useMemo, useState } from 'react';
import DataTable from '../../components/Shared/DataTable';
import { holidayService } from '../../services/holidayService';
import { formatDate } from '../../utils/date';

const TYPE_OPTIONS = [
  { value: 'Public Holiday', name: 'Public Holiday' },
  { value: 'Restricted Holiday', name: 'Restricted Holiday' },
  { value: 'Optional Holiday', name: 'Optional Holiday' },
];

const STATUS_OPTIONS = [
  { value: 'Active', name: 'Active' },
  { value: 'Inactive', name: 'Inactive' },
];

export default function HolidaysPage() {
  const [years, setYears] = useState([]);
  const [year, setYear] = useState(String(new Date().getFullYear()));

  // ModuleListPage refetches whenever this object changes identity, so it has
  // to be memoised - a fresh literal on every render would refetch in a loop.
  const fetchParams = useMemo(() => ({ year }), [year]);

  useEffect(() => {
    let mounted = true;
    holidayService
      .years()
      .then((list) => {
        if (!mounted) return;
        const normalized = (list || [])
          .map((y) => String(y))
          .sort((a, b) => Number(b) - Number(a));
        setYears(normalized);
        if (normalized.length && !normalized.includes(year)) {
          setYear(normalized[0]);
        }
      })
      .catch(() => {});
    return () => {
      mounted = false;
    };
  }, []);

  // The selected year always stays selectable, even when no holiday has been
  // declared for it yet - otherwise the year filter would disappear the
  // moment the calendar for the current year was empty.
  const yearOptions = useMemo(() => {
    const options = new Set(years.map(String));
    options.add(String(year));
    return Array.from(options).sort((a, b) => Number(b) - Number(a));
  }, [years, year]);

  return (
    <ModuleListPage
      title="Holidays"
      description="Manage the company holiday calendar."
      store={useHolidayStore}
      module="holidays"
      searchPlaceholder="Search holidays..."
      fetchParams={fetchParams}
      resourceName="holidays"
      columns={[
        { header: 'Name', accessor: 'name' },
        {
          header: 'Date',
          accessor: 'date',
          cell: (row) => formatDate(row.date),
        },
        { header: 'Day', accessor: 'day_of_week' },
        { header: 'Type', accessor: 'type' },
        {
          header: 'Status',
          accessor: 'status',
          cell: (row) => (
            <span
              className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${
                row.status === 'Active'
                  ? 'bg-green-50 text-green-700'
                  : 'bg-gray-50 text-gray-500'
              }`}
            >
              {row.status}
            </span>
          ),
        },
        { header: 'Description', accessor: 'description' },
      ]}
      fields={[
        { name: 'name', label: 'Holiday Name', type: 'text', required: true },
        { name: 'date', label: 'Date', type: 'date', required: true },
        {
          name: 'type',
          label: 'Type',
          type: 'select',
          required: true,
          options: TYPE_OPTIONS,
          defaultValue: 'Public Holiday',
        },
        {
          name: 'status',
          label: 'Status',
          type: 'select',
          options: STATUS_OPTIONS,
          defaultValue: 'Active',
        },
        { name: 'description', label: 'Description', type: 'textarea', fullWidth: true },
      ]}
      renderItems={(items, ctx) => (
        <div className="space-y-4">
          <div className="flex items-center gap-3">
            <label htmlFor="holiday-year" className="text-sm font-medium text-gray-700">
              Year
            </label>
            <select
              id="holiday-year"
              value={year}
              onChange={(event) => setYear(event.target.value)}
              className="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring"
            >
              {yearOptions.map((option) => (
                <option key={option} value={option}>
                  {option}
                </option>
              ))}
            </select>
          </div>
          <DataTable
            columns={ctx.tableColumns}
            data={items}
            emptyText="No holidays configured for this year."
          />
        </div>
      )}
    />
  );
}
