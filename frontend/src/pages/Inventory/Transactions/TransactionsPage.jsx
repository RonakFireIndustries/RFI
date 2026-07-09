import { useEffect, useState } from 'react';
import { useTransactionLedgerStore } from '../../../store/transactionLedgerStore';
import DataTable from '../../../components/Shared/DataTable';
import { Search, Filter } from 'lucide-react';

const typeColors = {
  opening_stock: 'bg-blue-50 text-blue-700',
  purchase: 'bg-green-50 text-green-700',
  purchase_return: 'bg-yellow-50 text-yellow-700',
  issue: 'bg-orange-50 text-orange-700',
  consumption: 'bg-red-50 text-red-700',
  transfer_in: 'bg-teal-50 text-teal-700',
  transfer_out: 'bg-purple-50 text-purple-700',
  adjustment: 'bg-gray-50 text-gray-700',
  damage: 'bg-red-50 text-red-700',
  sales: 'bg-pink-50 text-pink-700',
  sales_return: 'bg-indigo-50 text-indigo-700',
};

export default function TransactionsPage() {
  const { items, loading, fetchItems } = useTransactionLedgerStore();
  const [search, setSearch] = useState('');
  const [typeFilter, setTypeFilter] = useState('');

  useEffect(() => {
    const params = {};
    if (typeFilter) params.transaction_type = typeFilter;
    fetchItems(params);
  }, [fetchItems, typeFilter]);

  const columns = [
    { accessorFn: (row) => row.transaction_number, id: 'number', header: 'Txn #' },
    { accessorFn: (row) => row.product?.name || '-', id: 'product', header: 'Product' },
    { accessorFn: (row) => row.locationable?.name || '-', id: 'location', header: 'Location' },
    {
      id: 'type',
      header: 'Type',
      cell: ({ row }) => (
        <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium capitalize ${typeColors[row.original.transaction_type] || 'bg-gray-50 text-gray-700'}`}>
          {row.original.transaction_type?.replace(/_/g, ' ')}
        </span>
      ),
    },
    {
      id: 'quantity',
      header: 'Qty',
      cell: ({ row }) => {
        const qty = Number(row.original.quantity);
        const isInflow = ['opening_stock', 'purchase', 'purchase_return', 'transfer_in', 'sales_return'].includes(row.original.transaction_type);
        return <span className={`font-medium ${isInflow ? 'text-green-600' : 'text-red-600'}`}>{qty > 0 ? '+' : ''}{qty}</span>;
      },
    },
    { accessorFn: (row) => row.creator || '-', id: 'by', header: 'Created By' },
    { accessorFn: (row) => row.created_at ? new Date(row.created_at).toLocaleString() : '-', id: 'date', header: 'Date' },
    { accessorFn: (row) => row.notes || '-', id: 'notes', header: 'Notes' },
  ];

  return (
    <div className="space-y-5 pb-10">
      <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Transaction Ledger</h1>
          <p className="mt-1 text-sm text-gray-500">All stock movements recorded</p>
        </div>
        <div className="flex gap-2">
          <select
            value={typeFilter}
            onChange={(e) => setTypeFilter(e.target.value)}
            className="rounded-md border border-gray-300 px-3 py-2 text-sm"
          >
            <option value="">All Types</option>
            {Object.keys(typeColors).map((type) => (
              <option key={type} value={type}>{type.replace(/_/g, ' ')}</option>
            ))}
          </select>
        </div>
      </div>

      <div className="relative max-w-md">
        <Search className="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
        <input
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          placeholder="Search transactions..."
          className="w-full rounded-md border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring"
        />
      </div>

      {loading ? (
        <div className="rounded-lg border border-gray-200 bg-white p-8 text-center text-sm text-gray-500">Loading transactions...</div>
      ) : (
        <DataTable columns={columns} data={items} globalFilter={search} onGlobalFilterChange={setSearch} />
      )}
    </div>
  );
}
