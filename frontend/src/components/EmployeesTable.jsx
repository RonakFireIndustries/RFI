import {
  createColumnHelper,
  flexRender,
  getCoreRowModel,
  useReactTable,
} from '@tanstack/react-table'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"

const defaultData = [
  { id: 1, firstName: 'tanner', lastName: 'linsley', department: 'Engineering', status: 'Active' },
  { id: 2, firstName: 'tandy', lastName: 'miller', department: 'HR', status: 'Active' },
  { id: 3, firstName: 'joe', lastName: 'dirte', department: 'Sales', status: 'Inactive' },
]

const columnHelper = createColumnHelper()

const columns = [
  columnHelper.accessor('id', {
    header: () => 'ID',
    cell: info => info.getValue(),
  }),
  columnHelper.accessor('firstName', {
    header: () => 'First Name',
    cell: info => info.getValue(),
  }),
  columnHelper.accessor(row => row.lastName, {
    id: 'lastName',
    header: () => <span>Last Name</span>,
    cell: info => <i>{info.getValue()}</i>,
  }),
  columnHelper.accessor('department', {
    header: () => 'Department',
    cell: info => info.renderValue(),
  }),
  columnHelper.accessor('status', {
    header: 'Status',
    cell: info => (
      <span className={`px-2 py-1 rounded-full text-xs font-medium ${info.getValue() === 'Active' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400'}`}>
        {info.getValue()}
      </span>
    ),
  }),
]

export function EmployeesTable() {
  const table = useReactTable({
    data: defaultData,
    columns,
    getCoreRowModel: getCoreRowModel(),
  })

  return (
    <div className="rounded-md border border-gray-200 dark:border-gray-700">
      <Table>
        <TableHeader className="bg-gray-50 dark:bg-gray-800/50">
          {table.getHeaderGroups().map(headerGroup => (
            <TableRow key={headerGroup.id}>
              {headerGroup.headers.map(header => (
                <TableHead key={header.id}>
                  {header.isPlaceholder
                    ? null
                    : flexRender(
                        header.column.columnDef.header,
                        header.getContext()
                      )}
                </TableHead>
              ))}
            </TableRow>
          ))}
        </TableHeader>
        <TableBody>
          {table.getRowModel().rows.map(row => (
            <TableRow key={row.id}>
              {row.getVisibleCells().map(cell => (
                <TableCell key={cell.id}>
                  {flexRender(cell.column.columnDef.cell, cell.getContext())}
                </TableCell>
              ))}
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </div>
  )
}
