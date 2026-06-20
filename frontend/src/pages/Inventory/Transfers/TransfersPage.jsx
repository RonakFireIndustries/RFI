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

export default function TransfersPage() {
  const navigate = useNavigate();
  const [transfers, setTransfers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [form, setForm] = useState({
    product_id: '', location_type: 'App\\Models\\Site', location_id: '', to_location_type: 'App\\Models\\Site', to_location_id: '',
    quantity: '', notes: '',
  });
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState(null);

  const { items: products, fetchItems: fetchProducts } = useProductsStore();
  const { items: locations, fetchItems: fetchLocations } = useInventoryLocationStore();

  useEffect(() => {
    fetchProducts();
    fetchLocations();
    loadTransfers();
  }, [fetchProducts, fetchLocations]);

  const loadTransfers = async () => {
    setLoading(true);
    try {
      const data = await transactionLedgerService.list({ transaction_type: 'transfer_out', per_page: 50 });
      setTransfers(data);
    } catch (e) { console.error(e); }
    setLoading(false);
  };

  const handleTransfer = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setError(null);
    try {
      await transactionLedgerService.create({
        ...form,
        transaction_type: 'transfer_out',
        unit_price: 0,
      });
      setIsModalOpen(false);
      setForm({ product_id: '', location_type: 'App\\Models\\Site', location_id: '', to_location_type: 'App\\Models\\Site', to_location_id: '', quantity: '', notes: '' });
      loadTransfers();
    } catch (e) {
      setError(e.response?.data?.message || e.message);
    }
    setSubmitting(false);
  };

  const columns = [
    { accessorFn: (row) => row.transaction_number, id: 'number', header: 'Transfer #' },
    { accessorFn: (row) => row.product?.name || '-', id: 'product', header: 'Product' },
    { accessorFn: (row) => row.locationable?.name || '-', id: 'from', header: 'From' },
    { accessorFn: (row) => row.to_locationable?.name || '-', id: 'to', header: 'To' },
    { accessorFn: (row) => Math.abs(Number(row.quantity)).toLocaleString(), id: 'qty', header: 'Qty' },
    { accessorFn: (row) => row.created_at ? new Date(row.created_at).toLocaleString() : '-', id: 'date', header: 'Date' },
    { accessorFn: (row) => row.notes || '-', id: 'notes', header: 'Notes' },
  ];

  return (
    <div className="space-y-5 pb-10">
      <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Stock Transfers</h1>
          <p className="mt-1 text-sm text-gray-500">Transfer stock between locations</p>
        </div>
        <button onClick={() => setIsModalOpen(true)} className="inline-flex items-center gap-2 rounded-md bg-[#1a56db] px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
          <Plus className="h-4 w-4" /> New Transfer
        </button>
      </div>

      <div className="relative max-w-md">
        <Search className="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
        <input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search transfers..." className="w-full rounded-md border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm focus:border-[#1a56db] focus:outline-none focus:ring-1 focus:ring-[#1a56db]" />
      </div>

      {loading ? (
        <div className="rounded-lg border border-gray-200 bg-white p-8 text-center text-sm text-gray-500">Loading...</div>
      ) : (
        <DataTable columns={columns} data={transfers} globalFilter={search} onGlobalFilterChange={setSearch} />
      )}

      {isModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
          <div className="w-full max-w-lg rounded-lg bg-white shadow-xl">
            <div className="flex items-center justify-between border-b border-gray-100 px-5 py-4">
              <h2 className="text-lg font-semibold">New Stock Transfer</h2>
              <button onClick={() => setIsModalOpen(false)} className="text-gray-500 hover:text-gray-700">&times;</button>
            </div>
            <form onSubmit={handleTransfer} className="p-5 space-y-4">
              {error && <div className="rounded-md bg-red-50 p-3 text-sm text-red-700">{error}</div>}
              <div>
                <label className="mb-1 block text-sm font-medium text-gray-700">Product</label>
                <select value={form.product_id} onChange={(e) => setForm({ ...form, product_id: e.target.value })} className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" required>
                  <option value="">Select Product</option>
                  {products.map((p) => <option key={p.id} value={p.id}>{p.name} ({p.sku})</option>)}
                </select>
              </div>
              <div>
                <label className="mb-1 block text-sm font-medium text-gray-700">From Location</label>
                <select value={form.location_id ? `site_${form.location_id}` : ''} onChange={(e) => { const { location_type, location_id } = parseLocationId(e.target.value); setForm({ ...form, location_type, location_id }); }} className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" required>
                  <option value="">Select Source</option>
                  {locations.map((l) => <option key={l.id} value={l.id}>{l.name} ({l.type})</option>)}
                </select>
              </div>
              <div>
                <label className="mb-1 block text-sm font-medium text-gray-700">To Location</label>
                <select value={form.to_location_id ? `site_${form.to_location_id}` : ''} onChange={(e) => { const { location_type, location_id } = parseLocationId(e.target.value); setForm({ ...form, to_location_type: location_type, to_location_id: location_id }); }} className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" required>
                  <option value="">Select Destination</option>
                  {locations.filter((l) => l.location_id !== form.location_id || l.location_type !== form.location_type).map((l) => (
                    <option key={l.id} value={l.id}>{l.name} ({l.type})</option>
                  ))}
                </select>
              </div>
              <div>
                <label className="mb-1 block text-sm font-medium text-gray-700">Quantity</label>
                <input type="number" step="0.01" value={form.quantity} onChange={(e) => setForm({ ...form, quantity: e.target.value })} className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" required />
              </div>
              <div>
                <label className="mb-1 block text-sm font-medium text-gray-700">Notes</label>
                <textarea value={form.notes} onChange={(e) => setForm({ ...form, notes: e.target.value })} className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" rows={2} />
              </div>
              <div className="flex justify-end gap-3">
                <button type="button" onClick={() => setIsModalOpen(false)} className="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" disabled={submitting} className="rounded-md bg-[#1a56db] px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
                  {submitting ? 'Processing...' : 'Create Transfer'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
