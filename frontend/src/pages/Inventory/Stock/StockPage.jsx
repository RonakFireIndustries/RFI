import { useEffect, useState } from 'react';
import { useProductStockStore } from '../../../store/productStockStore';
import DataTable from '../../../components/Shared/DataTable';
import { Search } from 'lucide-react';
import { useSearchParams } from 'react-router-dom';

export default function StockPage() {
  const { items, loading, fetchItems } = useProductStockStore();
  const [searchParams] = useSearchParams();
  const [search, setSearch] = useState('');

  useEffect(() => {
    const params = {};
    const lowStock = searchParams.get('low_stock');
    if (lowStock) params.low_stock = lowStock;
    fetchItems(params);
  }, [fetchItems, searchParams]);

  const columns = [
    {
      accessorFn: (row) => row.product?.name || `Product #${row.product_id}`,
      id: 'product',
      header: 'Product',
    },
    {
      accessorFn: (row) => row.product?.sku || '-',
      id: 'sku',
      header: 'SKU',
    },
    {
      accessorFn: (row) => row.locationable?.name || `Location #${row.location_id}`,
      id: 'location',
      header: 'Location',
    },
    {
      accessorFn: (row) => Number(row.quantity).toLocaleString(),
      id: 'quantity',
      header: 'Quantity',
    },
    {
      accessorFn: (row) => Number(row.reserved_quantity).toLocaleString(),
      id: 'reserved',
      header: 'Reserved',
    },
    {
      id: 'available',
      header: 'Available',
      cell: ({ row }) => {
        const available = Number(row.original.available_quantity);
        const reorder = Number(row.original.product?.reorder_level || 0);
        return (
          <span className={`font-medium ${available <= reorder && reorder > 0 ? 'text-red-600' : 'text-green-600'}`}>
            {available.toLocaleString()}
          </span>
        );
      },
    },
    {
      id: 'status',
      header: 'Status',
      cell: ({ row }) => {
        const available = Number(row.original.available_quantity);
        const reorder = Number(row.original.product?.reorder_level || 0);
        if (reorder > 0 && available <= reorder) {
          return (
            <span className="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700">
              Low Stock
            </span>
          );
        }
        return (
          <span className="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">
            In Stock
          </span>
        );
      },
    },
  ];

  return (
    <div className="space-y-5 pb-10">
      <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Product Stock</h1>
          <p className="mt-1 text-sm text-gray-500">View stock levels across all locations</p>
        </div>
      </div>

      <div className="relative max-w-md">
        <Search className="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
        <input
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          placeholder="Search stock..."
          className="w-full rounded-md border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring"
        />
      </div>

      {loading ? (
        <div className="rounded-lg border border-gray-200 bg-white p-8 text-center text-sm text-gray-500">Loading stock...</div>
      ) : (
        <DataTable columns={columns} data={items} globalFilter={search} onGlobalFilterChange={setSearch} />
      )}
    </div>
  );
}
