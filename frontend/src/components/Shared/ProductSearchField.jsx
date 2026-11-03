import { useMemo, useState } from 'react';
import { Search, X, Package } from 'lucide-react';

/**
 * Inline product search field (no dropdown overlay).
 * Type to filter products by name, SKU, product code or dimension;
 * results render inline below the input. Click a result to select it.
 */
export default function ProductSearchField({
  products = [],
  value,
  onSelect,
  placeholder = 'Search by name, SKU or dimension...',
}) {
  const [query, setQuery] = useState('');
  const selected = value ? products.find((p) => String(p.id) === String(value)) : null;

  const filtered = useMemo(() => {
    const q = query.trim().toLowerCase();
    if (!q) return [];
    return products.filter((p) =>
      p.name?.toLowerCase().includes(q) ||
      p.sku?.toLowerCase().includes(q) ||
      p.product_code?.toLowerCase().includes(q) ||
      p.dimension?.toLowerCase().includes(q)
    );
  }, [query, products]);

  if (selected) {
    return (
      <div className="flex items-center justify-between rounded-md border border-primary/30 bg-primary/5 px-3 py-2">
        <div className="flex items-center gap-2 min-w-0">
          <Package className="h-4 w-4 shrink-0 text-primary" />
          <div className="min-w-0">
            <p className="truncate text-sm font-medium text-gray-900">
              <span className="text-primary">{selected.sku}</span> — {selected.name}
            </p>
            {selected.dimension && (
              <p className="text-xs text-gray-500">Dimension: {selected.dimension}</p>
            )}
          </div>
        </div>
        <button
          type="button"
          onClick={() => { onSelect(null); setQuery(''); }}
          className="ml-2 rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
          aria-label="Clear product"
        >
          <X className="h-4 w-4" />
        </button>
      </div>
    );
  }

  return (
    <div>
      <div className="relative">
        <Search className="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
        <input
          type="text"
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          placeholder={placeholder}
          autoComplete="off"
          className="w-full rounded-md border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring"
        />
      </div>
      {query.trim() && (
        <div className="mt-1 max-h-48 overflow-y-auto rounded-md border border-gray-200 bg-white">
          {filtered.length === 0 ? (
            <div className="px-3 py-2 text-sm text-gray-400">No products match "{query.trim()}".</div>
          ) : (
            filtered.map((p) => (
              <button
                key={p.id}
                type="button"
                onClick={() => { onSelect(p); setQuery(''); }}
                className="flex w-full items-center justify-between gap-2 border-b border-gray-100 px-3 py-2 text-left text-sm last:border-b-0 hover:bg-blue-50"
              >
                <span className="min-w-0 truncate">
                  <span className="font-medium text-gray-900">{p.sku}</span>
                  <span className="text-gray-700"> — {p.name}</span>
                </span>
                {p.dimension && (
                  <span className="shrink-0 text-xs text-gray-500">{p.dimension}</span>
                )}
              </button>
            ))
          )}
        </div>
      )}
    </div>
  );
}
