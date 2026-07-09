import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import DataTable from '../../../components/Shared/DataTable';
import { Search, Plus } from 'lucide-react';
import { useProductsStore } from '../../../store/productsStore';
import { useInventoryLocationStore } from '../../../store/inventoryLocationStore';
import { transactionLedgerService } from '../../../services/transactionLedgerService';

const parseLocationId = (compositeId) => {
  const match = compositeId.match(/^site_(\d+)$/);
  if (!match) return { location_type: '', location_id: '' };
  return { location_type: 'App\\Models\\Site', location_id: parseInt(match[2], 10) };
};

export default function RecordTransactionPage() {
  const navigate = useNavigate();
  const [transactions, setTransactions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [form, setForm] = useState({
    product_id: '', location_type: 'App\\Models\\Site', location_id: '', transaction_type: 'adjustment',
    quantity: '', unit_price: '', to_location_type: 'App\\Models\\Site', to_location_id: '', notes: '',
  });
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState(null);

  const { items: products, fetchItems: fetchProducts } = useProductsStore();
  const { items: locations, fetchItems: fetchLocations } = useInventoryLocationStore();

  useEffect(() => {
    fetchProducts();
    fetchLocations();
    loadTransactions();
  }, [fetchProducts, fetchLocations]);

  const loadTransactions = async () => {
    setLoading(true);
    try {
      const data = await transactionLedgerService.list({ per_page: 20 });
      setTransactions(data);
    } catch (e) {
      console.error(e);
    }
    setLoading(false);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setError(null);
    try {
      await transactionLedgerService.create(form);
      setIsModalOpen(false);
      setForm({ product_id: '', location_type: 'App\\Models\\Site', location_id: '', transaction_type: 'adjustment', quantity: '', unit_price: '', to_location_type: 'App\\Models\\Site', to_location_id: '', notes: '' });
      loadTransactions();
    } catch (e) {
      setError(e.response?.data?.message || e.message);
    }
    setSubmitting(false);
  };

  const transactionTypes = [
    'opening_stock', 'purchase', 'purchase_return', 'issue', 'consumption',
    'transfer_in', 'transfer_out', 'adjustment', 'damage', 'sales', 'sales_return'
  ];

  const columns = [
    { accessorFn: (row) => row.transaction_number, id: 'number', header: 'Txn #' },
    { accessorFn: (row) => row.product?.name || '-', id: 'product', header: 'Product' },
    { accessorFn: (row) => row.locationable?.name || '-', id: 'location', header: 'Location' },
    { accessorFn: (row) => row.transaction_type?.replace(/_/g, ' '), id: 'type', header: 'Type' },
    { accessorFn: (row) => Number(row.quantity).toLocaleString(), id: 'qty', header: 'Qty' },
    { accessorFn: (row) => row.created_at ? new Date(row.created_at).toLocaleString() : '-', id: 'date', header: 'Date' },
  ];

  return (
    <div className="space-y-5 pb-10">
      <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Record Transaction</h1>
          <p className="mt-1 text-sm text-gray-500">Record stock movements manually</p>
        </div>
        <button
          onClick={() => setIsModalOpen(true)}
          className="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-primary/90"
        >
          <Plus className="h-4 w-4" /> New Transaction
        </button>
      </div>

      <div className="relative max-w-md">
        <Search className="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
        <input
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          placeholder="Search..."
          className="w-full rounded-md border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring"
        />
      </div>

      {loading ? (
        <div className="rounded-lg border border-gray-200 bg-white p-8 text-center text-sm text-gray-500">Loading...</div>
      ) : (
        <DataTable columns={columns} data={transactions} globalFilter={search} onGlobalFilterChange={setSearch} />
      )}

      {isModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 py-6 sm:py-12">
          <div className="flex w-full max-w-lg flex-col rounded-lg bg-white shadow-xl max-h-full">
            <div className="flex items-center justify-between border-b border-gray-100 px-5 py-4">
              <h2 className="text-lg font-semibold text-gray-900">Record Transaction</h2>
              <button onClick={() => setIsModalOpen(false)} className="text-gray-500 hover:text-gray-700">&times;</button>
            </div>
            <form onSubmit={handleSubmit} className="flex flex-col overflow-hidden">
              <div className="overflow-y-auto p-5 space-y-4">
                {error && <div className="rounded-md bg-red-50 p-3 text-sm text-red-700">{error}</div>}

                <div>
                  <label className="mb-1 block text-sm font-medium text-gray-700">Transaction Type</label>
                  <select
                    value={form.transaction_type}
                    onChange={(e) => setForm({ ...form, transaction_type: e.target.value })}
                    className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                    required
                  >
                    {transactionTypes.map((t) => (
                      <option key={t} value={t}>{t.replace(/_/g, ' ')}</option>
                    ))}
                  </select>
                </div>

                <div>
                  <label className="mb-1 block text-sm font-medium text-gray-700">Product</label>
                  <select value={form.product_id} onChange={(e) => setForm({ ...form, product_id: e.target.value })} className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" required>
                    <option value="">Select Product</option>
                    {products.map((p) => <option key={p.id} value={p.id}>{p.name} ({p.sku})</option>)}
                  </select>
                </div>

                <div>
                  <label className="mb-1 block text-sm font-medium text-gray-700">Location</label>
                  <select value={form.location_id ? `site_${form.location_id}` : ''} onChange={(e) => { const { location_type, location_id } = parseLocationId(e.target.value); setForm({ ...form, location_type, location_id }); }} className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" required>
                    <option value="">Select Location</option>
                    {locations.map((l) => <option key={l.id} value={l.id}>{l.name} ({l.type})</option>)}
                  </select>
                </div>

                {form.transaction_type === 'transfer_out' && (
                  <div>
                    <label className="mb-1 block text-sm font-medium text-gray-700">Destination Location</label>
                    <select value={form.to_location_id ? `site_${form.to_location_id}` : ''} onChange={(e) => { const { location_type, location_id } = parseLocationId(e.target.value); setForm({ ...form, to_location_type: location_type, to_location_id: location_id }); }} className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                      <option value="">Select Destination</option>
                      {locations.filter((l) => l.location_id !== form.location_id || l.location_type !== form.location_type).map((l) => (
                        <option key={l.id} value={l.id}>{l.name} ({l.type})</option>
                      ))}
                    </select>
                  </div>
                )}

                <div>
                  <label className="mb-1 block text-sm font-medium text-gray-700">Quantity</label>
                  <input type="number" step="0.01" value={form.quantity} onChange={(e) => setForm({ ...form, quantity: e.target.value })} className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" required />
                </div>

                <div>
                  <label className="mb-1 block text-sm font-medium text-gray-700">Unit Price</label>
                  <input type="number" step="0.01" value={form.unit_price} onChange={(e) => setForm({ ...form, unit_price: e.target.value })} className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                </div>

                <div>
                  <label className="mb-1 block text-sm font-medium text-gray-700">Notes</label>
                  <textarea value={form.notes} onChange={(e) => setForm({ ...form, notes: e.target.value })} className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" rows={2} />
                </div>
              </div>

              <div className="flex shrink-0 justify-end gap-3 border-t border-gray-100 bg-gray-50 p-4">
                <button type="button" onClick={() => setIsModalOpen(false)} className="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" disabled={submitting} className="rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-primary/90 disabled:opacity-50">
                  {submitting ? 'Recording...' : 'Record Transaction'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
