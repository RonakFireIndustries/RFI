import { useEffect, useState } from 'react';
import { useProductStockStore } from '../../../store/productStockStore';
import DataTable from '../../../components/Shared/DataTable';
import { Search, X } from 'lucide-react';
import { useSearchParams } from 'react-router-dom';

export default function StockPage() {
  const { items, loading, fetchItems } = useProductStockStore();
  const [searchParams, setSearchParams] = useSearchParams();
  const [search, setSearch] = useState('');

  // Deep-link filters, e.g. from the inventory dashboard.
  const lowStock = searchParams.get('low_stock');
  const productId = searchParams.get('product_id');
  const locationType = searchParams.get('location_type');
  const locationId = searchParams.get('location_id');
  const hasLocationFilter = Boolean(locationType && locationId);

  useEffect(() => {
    const params = {};
    if (lowStock) params.low_stock = lowStock;
    if (productId) params.product_id = productId;
    if (locationType && locationId) {
      params.location_type = locationType;
      params.location_id = locationId;
    }
    fetchItems(params);
  }, [fetchItems, lowStock, productId, locationType, locationId]);

  const filters = [
    lowStock && { key: 'low_stock', label: 'Low stock only' },
    productId && {
      key: 'product_id',
      label: items[0]?.product?.name ? `Product: ${items[0].product.name}` : 'Filtered by product',
    },
    hasLocationFilter && {
      key: 'location',
      label: items[0]?.locationable?.name ? `Location: ${items[0].locationable.name}` : 'Filtered by location',
    },
  ].filter(Boolean);

  const removeFilter = (key) => {
    const next = new URLSearchParams(searchParams);
    if (key === 'location') {
      next.delete('location_type');
      next.delete('location_id');
    } else {
      next.delete(key);
    }
    setSearchParams(next, { replace: true });
  };

  const clearFilters = () => {
    const next = new URLSearchParams(searchParams);
    ['low_stock', 'product_id', 'location_type', 'location_id'].forEach((key) => next.delete(key));
    setSearchParams(next, { replace: true });
  };

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

      {filters.length > 0 && (
        <div className="flex flex-wrap items-center gap-2">
          <span className="text-xs font-medium text-muted-foreground">Filtered by:</span>
          {filters.map((filter) => (
            <button
              key={filter.key}
              type="button"
              onClick={() => removeFilter(filter.key)}
              className="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-xs font-medium text-primary hover:bg-primary/20 transition-colors"
            >
              {filter.label}
              <X className="h-3 w-3" />
            </button>
          ))}
          {filters.length > 1 && (
            <button
              type="button"
              onClick={clearFilters}
              className="text-xs font-medium text-muted-foreground hover:text-foreground"
            >
              Clear all
            </button>
          )}
        </div>
      )}

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
