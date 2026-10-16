import {
  flexRender,
  getCoreRowModel,
  getFilteredRowModel,
  getSortedRowModel,
  useReactTable,
} from '@tanstack/react-table';
import { ArrowDownUp } from 'lucide-react';
import ResponsiveTable from './ResponsiveTable';

export default function DataTable({ columns, data, globalFilter, onGlobalFilterChange, emptyText = 'No records found.', onRowClick }) {
  const table = useReactTable({
    data,
    columns,
    state: { globalFilter },
    onGlobalFilterChange,
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
  });

  // Rows often contain their own controls (View / Edit / Delete). Those should
  // keep their own behaviour instead of also triggering the row navigation.
  const handleRowClick = (event, row) => {
    if (!onRowClick) return;
    if (event.target.closest('button, a, input, select, textarea, [role="button"]')) return;
    onRowClick(row.original);
  };

  const handleRowKeyDown = (event, row) => {
    if (!onRowClick) return;
    if (event.target !== event.currentTarget) return;
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      onRowClick(row.original);
    }
  };

  return (
    <div className="overflow-hidden rounded-lg border border-border bg-card">
      <ResponsiveTable>
        <table className="w-full text-left text-sm min-w-max">
          <thead className="bg-muted text-xs font-semibold uppercase tracking-wider text-muted-foreground">
            {table.getHeaderGroups().map((headerGroup) => (
              <tr key={headerGroup.id}>
                {headerGroup.headers.map((header) => (
                  <th key={header.id} className="px-2 py-3 sm:px-4">
                    {header.isPlaceholder ? null : (
                      <button
                        type="button"
                        onClick={header.column.getToggleSortingHandler()}
                        className="inline-flex items-center gap-2 text-left"
                      >
                        {flexRender(header.column.columnDef.header, header.getContext())}
                        {header.column.getCanSort() && <ArrowDownUp className="h-3.5 w-3.5 text-muted-foreground" />}
                      </button>
                    )}
                  </th>
                ))}
              </tr>
            ))}
          </thead>
          <tbody className="divide-y divide-border">
            {table.getRowModel().rows.length === 0 ? (
              <tr>
                <td className="px-2 py-8 text-center text-muted-foreground sm:px-4" colSpan={columns.length}>
                  {emptyText}
                </td>
              </tr>
            ) : (
              table.getRowModel().rows.map((row) => (
                <tr
                  key={row.id}
                  onClick={(event) => handleRowClick(event, row)}
                  onKeyDown={(event) => handleRowKeyDown(event, row)}
                  tabIndex={onRowClick ? 0 : undefined}
                  className={`transition-colors ${onRowClick ? 'cursor-pointer' : ''} hover:bg-muted/50`}
                >
                  {row.getVisibleCells().map((cell) => (
                    <td key={cell.id} className="px-2 py-2.5 align-top text-xs sm:px-4 sm:py-3 sm:text-sm text-foreground/80 whitespace-normal">
                      {flexRender(cell.column.columnDef.cell, cell.getContext())}
                    </td>
                  ))}
                </tr>
              ))
            )}
          </tbody>
        </table>
      </ResponsiveTable>
    </div>
  );
}
