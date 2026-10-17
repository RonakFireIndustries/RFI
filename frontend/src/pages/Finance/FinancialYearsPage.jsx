import React, { useEffect, useState } from 'react';
import { Plus, X, Edit2, Trash2, CheckCircle2, CalendarRange, RefreshCw } from 'lucide-react';
import { useFinancialYearStore } from '../../store/accountingStore';
import { financialYearService } from '../../services/accountingService';
import PermissionGate from '../../components/Guards/PermissionGate';

const fmtDate = (d) => (d ? new Date(d).toLocaleDateString('en-GB') : '--');

const inputClass = 'w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 text-sm focus:ring-orange-500 focus:border-orange-500';
const labelClass = 'block text-xs font-medium text-gray-600 mb-1';

const today = () => new Date().toISOString().split('T')[0];

const EMPTY_FORM = {
  name: '',
  start_date: today(),
  end_date: today(),
  is_active: false,
  is_closed: false,
};

const firstError = (err) => {
  const errors = err?.response?.data?.data?.errors || err?.response?.data?.errors;
  if (errors) return Object.values(errors)[0]?.[0];
  return err?.response?.data?.data?.message || err?.response?.data?.message || 'Could not save.';
};

const FinancialYearsPage = () => {
  const store = useFinancialYearStore();
  const { items, loading, saving, error, fetchItems, createItem, updateItem, deleteItem } = store;

  const [showModal, setShowModal] = useState(false);
  const [editingId, setEditingId] = useState(null);
  const [form, setForm] = useState({ ...EMPTY_FORM });
  const [formError, setFormError] = useState(null);
  const [busyId, setBusyId] = useState(null);

  const load = () => fetchItems({}).catch(() => {});

  useEffect(() => { load(); /* eslint-disable-next-line */ }, []);

  const openCreate = () => {
    setEditingId(null);
    setForm({ ...EMPTY_FORM });
    setFormError(null);
    setShowModal(true);
  };

  const openEdit = (fy) => {
    setEditingId(fy.id);
    setForm({
      name: fy.name,
      start_date: fy.start_date ? new Date(fy.start_date).toISOString().split('T')[0] : '',
      end_date: fy.end_date ? new Date(fy.end_date).toISOString().split('T')[0] : '',
      is_active: Boolean(fy.is_active),
      is_closed: Boolean(fy.is_closed),
    });
    setFormError(null);
    setShowModal(true);
  };

  const submit = async (e) => {
    e.preventDefault();
    if (saving) return;
    setFormError(null);

    const payload = {
      name: form.name.trim(),
      start_date: form.start_date,
      end_date: form.end_date,
      is_active: Boolean(form.is_active),
      is_closed: Boolean(form.is_closed),
    };

    try {
      if (editingId) {
        await updateItem(editingId, payload);
      } else {
        await createItem(payload);
      }
      setShowModal(false);
      load();
    } catch (err) {
      setFormError(firstError(err));
    }
  };

  const activate = async (fy) => {
    setBusyId(fy.id);
    try {
      await financialYearService.activate(fy.id);
      load();
    } catch (err) {
      window.alert(firstError(err));
    } finally {
      setBusyId(null);
    }
  };

  const remove = async (fy) => {
    if (!window.confirm(`Remove financial year "${fy.name}"?`)) return;
    setBusyId(fy.id);
    try {
      await deleteItem(fy.id);
    } catch (err) {
      window.alert(firstError(err));
    } finally {
      setBusyId(null);
    }
  };

  return (
    <div className="p-6 max-w-5xl mx-auto">
      <div className="flex justify-between items-center mb-6">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Financial Years</h1>
          <p className="text-sm text-gray-500">One year is active at a time; new vouchers default to it.</p>
        </div>
        <PermissionGate module="financial-years" action="create" subject="financial years">
          <button onClick={openCreate} className="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg flex items-center shadow-sm transition-colors">
            <Plus className="w-4 h-4 mr-2" /> New Year
          </button>
        </PermissionGate>
      </div>

      {error && (
        <div className="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          Could not load financial years. You may not have access.
        </div>
      )}

      <div className="bg-white rounded-lg shadow overflow-hidden">
        <div className="overflow-x-auto max-w-full">
          <table className="min-w-full divide-y divide-gray-200 text-sm">
            <thead className="bg-gray-50">
              <tr>
                {['Financial year', 'From', 'To', 'Status', ''].map((h) => (
                  <th key={h} className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{h}</th>
                ))}
                <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {items.map((fy) => {
                const busy = busyId === fy.id;
                return (
                  <tr key={fy.id} className={busy ? 'opacity-60' : ''}>
                    <td className="px-4 py-3 font-medium text-gray-900 flex items-center gap-2">
                      <CalendarRange className="w-4 h-4 text-gray-400" /> {fy.name}
                    </td>
                    <td className="px-4 py-3 text-gray-600">{fmtDate(fy.start_date)}</td>
                    <td className="px-4 py-3 text-gray-600">{fmtDate(fy.end_date)}</td>
                    <td className="px-4 py-3">
                      {fy.is_active && <span className="px-2 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700">Active</span>}
                      {fy.is_closed && <span className="ml-1 px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Closed</span>}
                      {!fy.is_active && !fy.is_closed && <span className="text-xs text-gray-400">—</span>}
                    </td>
                    <td className="px-4 py-3 text-xs text-gray-400">
                      {fy.is_active ? 'Current books' : (fy.is_closed ? 'Locked' : 'Inactive')}
                    </td>
                    <td className="px-4 py-3 text-right">
                      <div className="inline-flex items-center gap-3">
                        {!fy.is_active && !fy.is_closed && (
                          <PermissionGate module="financial-years" action="update" subject="this year">
                            <button onClick={() => activate(fy)} title="Set active" className="text-green-600 hover:text-green-800">
                              <CheckCircle2 className="w-4 h-4" />
                            </button>
                          </PermissionGate>
                        )}
                        <PermissionGate module="financial-years" action="update" subject="this year">
                          <button onClick={() => openEdit(fy)} title="Edit" className="text-blue-600 hover:text-blue-900">
                            <Edit2 className="w-4 h-4" />
                          </button>
                        </PermissionGate>
                        <PermissionGate module="financial-years" action="delete" subject="this year">
                          <button
                            onClick={() => remove(fy)}
                            disabled={fy.is_active || fy.is_closed}
                            title={fy.is_active ? 'The active year cannot be removed' : (fy.is_closed ? 'A closed year cannot be removed' : 'Remove')}
                            className="text-red-500 hover:text-red-700 disabled:opacity-30 disabled:cursor-not-allowed"
                          >
                            <Trash2 className="w-4 h-4" />
                          </button>
                        </PermissionGate>
                      </div>
                    </td>
                  </tr>
                );
              })}
              {items.length === 0 && !loading && (
                <tr><td colSpan={6} className="px-4 py-8 text-center text-gray-500">No financial years yet.</td></tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {showModal && (
        <div className="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4">
          <div className="bg-white rounded-xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100">
              <h2 className="text-lg font-semibold text-gray-900">{editingId ? 'Edit Financial Year' : 'New Financial Year'}</h2>
              <button onClick={() => setShowModal(false)} className="text-gray-400 hover:text-gray-600"><X className="w-5 h-5" /></button>
            </div>
            <form onSubmit={submit} className="px-6 py-5 space-y-4">
              {formError && <div className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{formError}</div>}
              <div>
                <label className={labelClass}>Name *</label>
                <input required value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} className={inputClass} placeholder="2026-2027" />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className={labelClass}>Start date *</label>
                  <input required type="date" value={form.start_date} onChange={(e) => setForm({ ...form, start_date: e.target.value })} className={inputClass} />
                </div>
                <div>
                  <label className={labelClass}>End date *</label>
                  <input required type="date" value={form.end_date} onChange={(e) => setForm({ ...form, end_date: e.target.value })} className={inputClass} />
                </div>
              </div>
              <div className="flex flex-wrap gap-5">
                <label className="inline-flex items-center gap-2 text-sm text-gray-700">
                  <input type="checkbox" checked={form.is_active} onChange={(e) => setForm({ ...form, is_active: e.target.checked })} className="rounded text-orange-500 focus:ring-orange-500" />
                  Set as active year
                </label>
                <label className="inline-flex items-center gap-2 text-sm text-gray-700">
                  <input type="checkbox" checked={form.is_closed} onChange={(e) => setForm({ ...form, is_closed: e.target.checked })} className="rounded text-orange-500 focus:ring-orange-500" />
                  Closed
                </label>
              </div>
              <div className="flex justify-end gap-2 pt-2">
                <button type="button" onClick={() => setShowModal(false)} className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" disabled={saving} className="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg disabled:opacity-60">
                  {saving ? 'Saving…' : 'Save Year'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      <div className="mt-4 text-xs text-gray-400 flex items-center gap-1">
        <RefreshCw className="w-3 h-3" /> Tip: the active year frames Day Book, Ledger and financial statements.
      </div>
    </div>
  );
};

export default FinancialYearsPage;
