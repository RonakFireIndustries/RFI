import { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import DataTable from '../../../components/Shared/DataTable';
import { Search, Plus, CheckCircle, Send, Download } from 'lucide-react';
import { useStockRequestStore } from '../../../store/stockRequestStore';
import { useProductsStore } from '../../../store/productsStore';
import { useInventoryLocationStore } from '../../../store/inventoryLocationStore';
import { useEmployeesStore } from '../../../store/employeesStore';

const parseLocationId = (compositeId) => {
  const match = compositeId.match(/^site_(\d+)$/);
  if (!match) return { location_type: '', location_id: '' };
  return { location_type: 'App\\Models\\Site', location_id: parseInt(match[1], 10) };
};

const statusColors = {
  requested: 'bg-yellow-50 text-yellow-700',
  approved: 'bg-blue-50 text-blue-700',
  issued: 'bg-purple-50 text-purple-700',
  received: 'bg-green-50 text-green-700',
};

export default function StockRequestsPage() {
  const { items, loading, fetchItems, approve, issue, receive, createItem } = useStockRequestStore();
  const { items: products, fetchItems: fetchProducts } = useProductsStore();
  const { items: locations, fetchItems: fetchLocations } = useInventoryLocationStore();
  const { items: employees, fetchItems: fetchEmployees } = useEmployeesStore();
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [form, setForm] = useState({ product_id: '', from_location_type: 'App\\Models\\Site', from_location_id: '', to_location_type: 'App\\Models\\Site', to_location_id: '', quantity: '', approved_by: '', notes: '' });
  const navigate = useNavigate();

  useEffect(() => {
    const params = {};
    if (statusFilter) params.status = statusFilter;
    fetchItems(params);
    fetchProducts();
    fetchLocations();
    fetchEmployees();
  }, [fetchItems, fetchProducts, fetchLocations, fetchEmployees, statusFilter]);

  const handleCreate = async (e) => {
    e.preventDefault();
    try {
      await createItem(form);
      setIsModalOpen(false);
      setForm({ product_id: '', from_location_type: 'App\\Models\\Site', from_location_id: '', to_location_type: 'App\\Models\\Site', to_location_id: '', quantity: '', approved_by: '', notes: '' });
    } catch (err) {
      alert(err.response?.data?.message || err.message);
    }
  };

  const columns = [
    { accessorFn: (row) => row.request_number, id: 'number', header: 'Req #' },
    { accessorFn: (row) => row.product?.name || '-', id: 'product', header: 'Product' },
    { accessorFn: (row) => row.from_locationable?.name || '-', id: 'from', header: 'From' },
    { accessorFn: (row) => row.to_locationable?.name || '-', id: 'to', header: 'To' },
    { accessorFn: (row) => Number(row.quantity).toLocaleString(), id: 'qty', header: 'Qty' },
    {
      id: 'status',
      header: 'Status',
      cell: ({ row }) => (
        <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium capitalize ${statusColors[row.original.status]}`}>
          {row.original.status}
        </span>
      ),
    },
    { accessorFn: (row) => row.approver || '-', id: 'approver', header: 'Approver' },
    {
      id: 'actions',
      header: 'Actions',
      enableSorting: false,
      cell: ({ row }) => (
        <div className="flex gap-2">
          <Link to={`/dashboard/inventory/requests/${row.original.id}`} className="rounded-md border border-gray-200 px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50">
            View
          </Link>
          {row.original.status === 'requested' && (
            <button onClick={() => approve(row.original.id)} className="rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 hover:bg-blue-100">
              Approve
            </button>
          )}
          {row.original.status === 'approved' && (
            <button onClick={() => issue(row.original.id)} className="rounded-md bg-purple-50 px-2 py-1 text-xs font-medium text-purple-700 hover:bg-purple-100">
              Issue
            </button>
          )}
          {row.original.status === 'issued' && (
            <button onClick={() => receive(row.original.id)} className="rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 hover:bg-green-100">
              Receive
            </button>
          )}
        </div>
      ),
    },
  ];

  return (
    <div className="space-y-5 pb-10">
      <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Stock Requests</h1>
          <p className="mt-1 text-sm text-gray-500">Request stock between locations</p>
        </div>
        <div className="flex gap-2">
          <select value={statusFilter} onChange={(e) => setStatusFilter(e.target.value)} className="rounded-md border border-gray-300 px-3 py-2 text-sm">
            <option value="">All Status</option>
            <option value="requested">Requested</option>
            <option value="approved">Approved</option>
            <option value="issued">Issued</option>
            <option value="received">Received</option>
          </select>
          <button onClick={() => setIsModalOpen(true)} className="inline-flex items-center gap-2 rounded-md bg-[#1a56db] px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
            <Plus className="h-4 w-4" /> New Request
          </button>
        </div>
      </div>

      <div className="relative max-w-md">
        <Search className="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
        <input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search requests..." className="w-full rounded-md border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm focus:border-[#1a56db] focus:outline-none focus:ring-1 focus:ring-[#1a56db]" />
      </div>

      {loading ? (
        <div className="rounded-lg border border-gray-200 bg-white p-8 text-center text-sm text-gray-500">Loading...</div>
      ) : (
        <DataTable columns={columns} data={items} globalFilter={search} onGlobalFilterChange={setSearch} />
      )}

      {isModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
          <div className="w-full max-w-lg rounded-lg bg-white shadow-xl">
            <div className="flex items-center justify-between border-b border-gray-100 px-5 py-4">
              <h2 className="text-lg font-semibold">New Stock Request</h2>
              <button onClick={() => setIsModalOpen(false)} className="text-gray-500 hover:text-gray-700">&times;</button>
            </div>
            <form onSubmit={handleCreate} className="p-5 space-y-4">
              <div>
                <label className="mb-1 block text-sm font-medium text-gray-700">Product</label>
                <select value={form.product_id} onChange={(e) => setForm({ ...form, product_id: e.target.value })} className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" required>
                  <option value="">Select Product</option>
                  {products.map((p) => <option key={p.id} value={p.id}>{p.name} ({p.sku})</option>)}
                </select>
              </div>
              <div>
                <label className="mb-1 block text-sm font-medium text-gray-700">From Location</label>
                <select value={form.from_location_id ? `site_${form.from_location_id}` : ''} onChange={(e) => { const { location_type, location_id } = parseLocationId(e.target.value); setForm({ ...form, from_location_type: location_type, from_location_id: location_id }); }} className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" required>
                  <option value="">Select Source</option>
                  {locations.map((l) => <option key={l.id} value={l.id}>{l.name} ({l.type})</option>)}
                </select>
              </div>
              <div>
                <label className="mb-1 block text-sm font-medium text-gray-700">To Location</label>
                <select value={form.to_location_id ? `site_${form.to_location_id}` : ''} onChange={(e) => { const { location_type, location_id } = parseLocationId(e.target.value); setForm({ ...form, to_location_type: location_type, to_location_id: location_id }); }} className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" required>
                  <option value="">Select Destination</option>
                  {locations.filter((l) => l.location_id !== form.from_location_id || l.location_type !== form.from_location_type).map((l) => (
                    <option key={l.id} value={l.id}>{l.name} ({l.type})</option>
                  ))}
                </select>
              </div>
              <div>
                <label className="mb-1 block text-sm font-medium text-gray-700">Quantity</label>
                <input type="number" step="0.01" value={form.quantity} onChange={(e) => setForm({ ...form, quantity: e.target.value })} className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" required />
              </div>
              <div>
                <label className="mb-1 block text-sm font-medium text-gray-700">Approver</label>
                <select value={form.approved_by} onChange={(e) => setForm({ ...form, approved_by: e.target.value })} className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                  <option value="">Select Approver (optional)</option>
                  {employees.map((emp) => <option key={emp.id} value={emp.user_id}>{emp.full_name}</option>)}
                </select>
              </div>
              <div>
                <label className="mb-1 block text-sm font-medium text-gray-700">Notes</label>
                <textarea value={form.notes} onChange={(e) => setForm({ ...form, notes: e.target.value })} className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" rows={2} />
              </div>
              <div className="flex justify-end gap-3">
                <button type="button" onClick={() => setIsModalOpen(false)} className="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" className="rounded-md bg-[#1a56db] px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Submit Request</button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
