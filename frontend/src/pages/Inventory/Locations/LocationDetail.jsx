import { useState, useEffect, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import {
  ArrowLeft, Edit2, Trash2, X, MapPin, Users, Package,
  Phone, Mail, User, Code2, Building2, AlertCircle,
} from 'lucide-react';
import { inventoryLocationService } from '../../../services/inventoryLocationService';

const FIELD_STYLE = "w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring";

function EditLocationModal({ location, onSave, onCancel, saving }) {
  const [form, setForm] = useState({
    name: location.name || '',
    code: location.code || '',
    address: location.address || '',
    contact_person: location.contact_person || '',
    phone: location.phone || '',
    email: location.email || '',
    status: location.status || 'active',
  });

  const set = (key, value) => setForm((cur) => ({ ...cur, [key]: value }));

  const handleSubmit = (e) => {
    e.preventDefault();
    onSave(form);
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 py-6 sm:py-12" onClick={onCancel}>
      <div className="flex w-full max-w-lg flex-col max-h-full rounded-lg bg-white shadow-xl" onClick={(e) => e.stopPropagation()}>
        <div className="flex items-center justify-between border-b border-gray-100 px-5 py-4">
          <h2 className="text-lg font-semibold text-gray-900">Edit Location</h2>
          <button type="button" onClick={onCancel} className="rounded-md p-1 text-gray-500 hover:bg-gray-100">
            <X className="h-5 w-5" />
          </button>
        </div>
        <form onSubmit={handleSubmit} className="flex flex-col overflow-hidden">
          <div className="overflow-y-auto p-5">
            <div className="grid gap-4 sm:grid-cols-2">
              <label className="sm:col-span-2">
                <span className="mb-1 block text-sm font-medium text-gray-700">Name</span>
                <input required className={FIELD_STYLE} value={form.name} onChange={(e) => set('name', e.target.value)} />
              </label>
              <label>
                <span className="mb-1 block text-sm font-medium text-gray-700">Code</span>
                <input className={FIELD_STYLE} value={form.code} onChange={(e) => set('code', e.target.value)} />
              </label>
              <label>
                <span className="mb-1 block text-sm font-medium text-gray-700">Status</span>
                <select className={FIELD_STYLE} value={form.status} onChange={(e) => set('status', e.target.value)}>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </label>
              <label className="sm:col-span-2">
                <span className="mb-1 block text-sm font-medium text-gray-700">Address</span>
                <input className={FIELD_STYLE} value={form.address} onChange={(e) => set('address', e.target.value)} />
              </label>
              <label>
                <span className="mb-1 block text-sm font-medium text-gray-700">Contact Person</span>
                <input className={FIELD_STYLE} value={form.contact_person} onChange={(e) => set('contact_person', e.target.value)} />
              </label>
              <label>
                <span className="mb-1 block text-sm font-medium text-gray-700">Phone</span>
                <input className={FIELD_STYLE} value={form.phone} onChange={(e) => set('phone', e.target.value)} />
              </label>
              <label className="sm:col-span-2">
                <span className="mb-1 block text-sm font-medium text-gray-700">Email</span>
                <input type="email" className={FIELD_STYLE} value={form.email} onChange={(e) => set('email', e.target.value)} />
              </label>
            </div>
          </div>
          <div className="flex shrink-0 justify-end gap-3 border-t border-gray-100 bg-gray-50 p-4 rounded-b-lg">
            <button type="button" onClick={onCancel} className="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
              Cancel
            </button>
            <button type="submit" disabled={saving} className="rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-primary/90 disabled:opacity-50">
              {saving ? 'Saving...' : 'Save'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

export default function LocationDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [location, setLocation] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [showEdit, setShowEdit] = useState(false);
  const [saving, setSaving] = useState(false);
  const [deleting, setDeleting] = useState(false);

  const load = useCallback(async () => {
    setLoading(true);
    try {
      setLocation(await inventoryLocationService.get(id));
      setError(null);
    } catch (err) {
      setError(err?.response?.data?.message || err?.message || 'Could not load location details.');
    } finally {
      setLoading(false);
    }
  }, [id]);

  useEffect(() => { load(); }, [load]);

  const handleSave = async (form) => {
    setSaving(true);
    try {
      const updated = await inventoryLocationService.update(id, form);
      setLocation(updated);
      setShowEdit(false);
    } catch (err) {
      alert(err?.response?.data?.message || err?.message || 'Could not save location.');
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async () => {
    if (!window.confirm(`Delete ${location?.name}? This will remove the location and its links.`)) return;
    setDeleting(true);
    try {
      await inventoryLocationService.remove(id);
      navigate('/dashboard/inventory/locations');
    } catch (err) {
      alert(err?.response?.data?.message || err?.message || 'Could not delete location.');
      setDeleting(false);
    }
  };

  if (loading) {
    return (
      <div className="space-y-5 pb-10">
        <div className="h-8 w-48 animate-pulse rounded bg-gray-200" />
        <div className="grid gap-4 sm:grid-cols-3">
          {[0, 1, 2].map((i) => <div key={i} className="h-24 animate-pulse rounded-xl border border-gray-200 bg-white" />)}
        </div>
        <div className="h-40 animate-pulse rounded-xl border border-gray-200 bg-white" />
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex flex-col items-center justify-center rounded-xl border border-gray-200 bg-white p-12 text-center">
        <AlertCircle className="h-10 w-10 text-red-400" />
        <p className="mt-3 text-sm text-gray-600">{error}</p>
        <button onClick={() => navigate('/dashboard/inventory/locations')} className="mt-4 rounded-md border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
          Back to Locations
        </button>
      </div>
    );
  }

  const employees = location?.employees || [];
  const products = location?.products || [];

  const infoRows = [
    { icon: Code2, label: 'Code', value: location.code },
    { icon: MapPin, label: 'Address', value: [location.address, location.city, location.state].filter(Boolean).join(', ') || (location.pincode ? `Pincode: ${location.pincode}` : '') },
    { icon: User, label: 'Contact Person', value: location.contact_person },
    { icon: Phone, label: 'Phone', value: location.phone },
    { icon: Mail, label: 'Email', value: location.email },
  ];

  return (
    <div className="space-y-5 pb-10">
      <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div className="flex items-center gap-3">
          <button onClick={() => navigate('/dashboard/inventory/locations')} className="rounded-lg border border-gray-200 bg-white p-2 text-gray-600 hover:bg-gray-50">
            <ArrowLeft className="h-4 w-4" />
          </button>
          <div>
            <h1 className="text-2xl font-bold text-gray-900">{location.name}</h1>
            <div className="mt-1 flex items-center gap-2">
              <span className="inline-flex items-center rounded-full bg-purple-50 px-2.5 py-0.5 text-xs font-medium capitalize text-purple-700">
                {location.type}
              </span>
              {location.code && (
                <span className="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">
                  {location.code}
                </span>
              )}
              <span className={`inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium ${
                location.status === 'active' ? 'border-green-200 bg-green-50 text-green-700' : 'border-gray-200 bg-gray-50 text-gray-500'
              }`}>
                {location.status}
              </span>
            </div>
          </div>
        </div>
        <div className="flex items-center gap-2">
          <button onClick={() => setShowEdit(true)} className="inline-flex items-center gap-2 rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            <Edit2 className="h-4 w-4" /> Edit
          </button>
          <button onClick={handleDelete} disabled={deleting} className="inline-flex items-center gap-2 rounded-md border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 disabled:opacity-50">
            <Trash2 className="h-4 w-4" /> {deleting ? 'Deleting...' : 'Delete'}
          </button>
        </div>
      </div>

      <div className="grid gap-4 sm:grid-cols-3">
        <div className="rounded-xl border border-gray-200 bg-white p-5">
          <Package className="h-5 w-5 text-blue-600" />
          <p className="mt-2 text-2xl font-bold text-gray-900">{products.length}</p>
          <p className="text-xs font-medium text-gray-500">Products in Stock</p>
        </div>
        <div className="rounded-xl border border-gray-200 bg-white p-5">
          <Users className="h-5 w-5 text-green-600" />
          <p className="mt-2 text-2xl font-bold text-gray-900">{employees.length}</p>
          <p className="text-xs font-medium text-gray-500">Employees Assigned</p>
        </div>
        <div className="rounded-xl border border-gray-200 bg-white p-5">
          <Building2 className="h-5 w-5 text-purple-600" />
          <p className="mt-2 text-2xl font-bold text-gray-900">{location.stock_count ?? products.length}</p>
          <p className="text-xs font-medium text-gray-500">Stock Entries</p>
        </div>
      </div>

      <div className="rounded-xl border border-gray-200 bg-white">
        <div className="border-b border-gray-100 px-5 py-4">
          <h3 className="text-sm font-semibold text-gray-900">Location Details</h3>
        </div>
        <dl className="grid grid-cols-1 gap-x-8 gap-y-4 p-5 sm:grid-cols-2">
          {infoRows.map(({ icon: Icon, label, value }) => (
            <div key={label} className="flex items-start gap-3">
              <div className="mt-0.5 rounded-lg bg-gray-100 p-2 text-gray-500">
                <Icon className="h-4 w-4" />
              </div>
              <div>
                <dt className="text-xs font-medium text-gray-500">{label}</dt>
                <dd className="text-sm font-medium text-gray-900">{value || '—'}</dd>
              </div>
            </div>
          ))}
        </dl>
      </div>

      <div className="rounded-xl border border-gray-200 bg-white">
        <div className="flex items-center justify-between border-b border-gray-100 px-5 py-4">
          <h3 className="flex items-center gap-2 text-sm font-semibold text-gray-900">
            <Users className="h-4 w-4 text-green-600" /> Employees ({employees.length})
          </h3>
        </div>
        {employees.length === 0 ? (
          <p className="px-5 py-8 text-center text-sm text-gray-400">No employees assigned to this location.</p>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-100">
              <thead className="bg-gray-50/50">
                <tr>
                  <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Name</th>
                  <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Emp ID</th>
                  <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Designation</th>
                  <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Department</th>
                  <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Role</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {employees.map((emp) => (
                  <tr key={emp.id} className="hover:bg-gray-50">
                    <td className="px-5 py-3 text-sm font-medium text-gray-900">{emp.name}</td>
                    <td className="px-5 py-3 text-sm text-gray-600">{emp.emp_id || '—'}</td>
                    <td className="px-5 py-3 text-sm text-gray-600">{emp.designation || '—'}</td>
                    <td className="px-5 py-3 text-sm text-gray-600">{emp.department || '—'}</td>
                    <td className="px-5 py-3 text-sm text-gray-600">{emp.role || '—'}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      <div className="rounded-xl border border-gray-200 bg-white">
        <div className="flex items-center justify-between border-b border-gray-100 px-5 py-4">
          <h3 className="flex items-center gap-2 text-sm font-semibold text-gray-900">
            <Package className="h-4 w-4 text-blue-600" /> Products ({products.length})
          </h3>
        </div>
        {products.length === 0 ? (
          <p className="px-5 py-8 text-center text-sm text-gray-400">No products stocked at this location.</p>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-100">
              <thead className="bg-gray-50/50">
                <tr>
                  <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Product</th>
                  <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">SKU</th>
                  <th className="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Quantity</th>
                  <th className="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Available</th>
                  <th className="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Reserved</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {products.map((p) => (
                  <tr key={p.id} className="hover:bg-gray-50">
                    <td className="px-5 py-3 text-sm font-medium text-gray-900">{p.product_name || '—'}</td>
                    <td className="px-5 py-3 text-sm text-gray-600">{p.sku || '—'}</td>
                    <td className="px-5 py-3 text-right text-sm font-medium text-gray-900">{p.quantity ?? 0}</td>
                    <td className="px-5 py-3 text-right text-sm text-gray-600">{p.available_quantity ?? 0}</td>
                    <td className="px-5 py-3 text-right text-sm text-gray-600">{p.reserved_quantity ?? 0}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {showEdit && location && (
        <EditLocationModal
          location={location}
          saving={saving}
          onSave={handleSave}
          onCancel={() => setShowEdit(false)}
        />
      )}
    </div>
  );
}
