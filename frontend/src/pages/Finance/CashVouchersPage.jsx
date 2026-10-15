import React, { useEffect, useMemo, useState } from 'react';
import {
  Plus, X, Edit2, Trash2, CheckCircle2, XCircle, Banknote, Paperclip, Search, RefreshCw,
} from 'lucide-react';
import { useCashVoucherStore, cashVoucherService } from '../../store/cashVoucherStore';
import { useAuthStore } from '../../store/authStore';
import PermissionGate from '../../components/Guards/PermissionGate';
import { usePermission } from '../../hooks/usePermission';
import { STORAGE_URL } from '../../services/api';

const STATUSES = ['Draft', 'Approved', 'Rejected', 'Paid'];
const PAYMENT_MODES = ['Cash', 'Bank Transfer', 'UPI', 'Cheque'];
const CATEGORIES = [
  'Travel', 'Transport', 'Office Supplies', 'Refreshments', 'Maintenance',
  'Repairs', 'Utilities', 'Miscellaneous',
];

const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-GB') : '--';
const toDateInput = (d) => d ? new Date(d).toISOString().split('T')[0] : '';

const money = (value) => {
  const n = parseFloat(value);
  if (!Number.isFinite(n)) return '₹0';
  return `₹${n.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
};

const today = () => new Date().toISOString().split('T')[0];

const statusBadgeClass = (status) => {
  switch (status) {
    case 'Approved': return 'bg-blue-50 text-blue-700';
    case 'Paid': return 'bg-green-50 text-green-700';
    case 'Rejected': return 'bg-red-50 text-red-700';
    default: return 'bg-gray-100 text-gray-700';
  }
};

const EMPTY_FORM = {
  voucher_date: today(),
  paid_to: '',
  purpose: '',
  category: '',
  amount: '',
  payment_mode: 'Cash',
  reference_no: '',
  remarks: '',
};

/** "Full Name (EMP-12)" for the reimbursement employee, or null. */
const employeeText = (emp) => {
  if (!emp) return null;
  const name = emp.full_name || emp.name;
  if (!name) return null;
  return emp.emp_id ? `${name} (${emp.emp_id})` : name;
};

/**
 * Renders the action for a user allowed for a reason other than the module
 * permission (the employee who raised the voucher), and otherwise falls back to
 * PermissionGate's disabled + tooltip state.
 */
const OwnerOrPermission = ({ allowed, module, action, subject, children }) =>
  allowed ? <>{children}</> : (
    <PermissionGate module={module} action={action} subject={subject}>{children}</PermissionGate>
  );

const CashVouchersPage = () => {
  const { items, loading, saving, error, fetchItems, createItem, updateItem, deleteItem } = useCashVoucherStore();
  const { user } = useAuthStore();
  const { canModule } = usePermission();
  // The reimbursement is always the employee raising the voucher.
  const myEmployee = user?.employee || null;

  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [showModal, setShowModal] = useState(false);
  const [editingId, setEditingId] = useState(null);
  const [form, setForm] = useState({ ...EMPTY_FORM });
  // Read-only "Reimbursement To": the signed-in employee on create, or the
  // original employee when an existing draft is being edited.
  const [reimbursement, setReimbursement] = useState(null);
  const [attachment, setAttachment] = useState(null);
  const [formError, setFormError] = useState(null);
  const [busyId, setBusyId] = useState(null);
  const [rejectTarget, setRejectTarget] = useState(null);
  const [rejectRemarks, setRejectRemarks] = useState('');
  const [rejectError, setRejectError] = useState(null);

  // Reload on filter change (small debounce so typing doesn't hammer the API).
  useEffect(() => {
    const timer = setTimeout(() => {
      fetchItems({ search: search || undefined, status: statusFilter || undefined });
    }, 300);
    return () => clearTimeout(timer);
  }, [search, statusFilter, fetchItems]);

  const counts = useMemo(() => {
    const base = { Draft: 0, Approved: 0, Paid: 0, Rejected: 0 };
    let paidTotal = 0;
    items.forEach((v) => {
      if (base[v.status] !== undefined) base[v.status] += 1;
      if (v.status === 'Paid') paidTotal += parseFloat(v.amount || 0);
    });
    return { ...base, paidTotal };
  }, [items]);

  const openCreate = () => {
    setEditingId(null);
    setForm({ ...EMPTY_FORM });
    setReimbursement(myEmployee);
    setAttachment(null);
    setFormError(null);
    setShowModal(true);
  };

  const openEdit = (voucher) => {
    setEditingId(voucher.id);
    setForm({
      voucher_date: toDateInput(voucher.voucher_date),
      paid_to: voucher.paid_to || '',
      purpose: voucher.purpose || '',
      category: voucher.category || '',
      amount: voucher.amount ?? '',
      payment_mode: voucher.payment_mode || 'Cash',
      reference_no: voucher.reference_no || '',
      remarks: voucher.remarks || '',
    });
    setReimbursement(voucher.employee || null);
    setAttachment(null);
    setFormError(null);
    setShowModal(true);
  };

  const closeModal = () => {
    setShowModal(false);
    setEditingId(null);
    setReimbursement(null);
    setAttachment(null);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (saving) return;
    setFormError(null);

    const base = {
      voucher_date: form.voucher_date,
      paid_to: form.paid_to,
      purpose: form.purpose,
      category: form.category,
      amount: form.amount,
      payment_mode: form.payment_mode || 'Cash',
      reference_no: form.reference_no,
      remarks: form.remarks,
    };

    let payload = base;
    if (attachment) {
      const fd = new FormData();
      Object.entries(base).forEach(([key, value]) => {
        if (value !== '' && value !== null && value !== undefined) fd.append(key, value);
      });
      fd.append('attachment', attachment);
      payload = fd;
    }

    try {
      if (editingId) {
        await updateItem(editingId, payload);
      } else {
        await createItem(payload);
      }
      closeModal();
    } catch (err) {
      const errors = err?.response?.data?.data?.errors || err?.response?.data?.errors;
      const first = errors ? Object.values(errors)[0]?.[0] : null;
      setFormError(first || err?.response?.data?.message || 'Could not save the voucher.');
    }
  };

  const runAction = async (id, action) => {
    setBusyId(id);
    try {
      await action();
      await fetchItems({ search: search || undefined, status: statusFilter || undefined });
    } catch (err) {
      window.alert(err?.response?.data?.message || 'The action could not be completed.');
    } finally {
      setBusyId(null);
    }
  };

  const confirmReject = async () => {
    if (!rejectRemarks.trim()) {
      setRejectError('Please give a reason for rejecting.');
      return;
    }
    await runAction(rejectTarget.id, () => cashVoucherService.reject(rejectTarget.id, rejectRemarks.trim()));
    setRejectTarget(null);
    setRejectRemarks('');
    setRejectError(null);
  };

  const inputClass = 'w-full border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500';

  return (
    <div className="p-6 max-w-7xl mx-auto">
      <div className="flex justify-between items-center mb-6">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Cash Vouchers</h1>
          <p className="text-sm text-gray-500">Petty-cash payments: record, approve, then mark as paid.</p>
        </div>
        <PermissionGate module="cash-vouchers" action="create" subject="cash vouchers">
          <button onClick={openCreate} className="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg flex items-center shadow-sm transition-colors">
            <Plus className="w-4 h-4 mr-2" /> New Voucher
          </button>
        </PermissionGate>
      </div>

      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        {['Draft', 'Approved', 'Paid', 'Rejected'].map((status) => (
          <button
            key={status}
            onClick={() => setStatusFilter(statusFilter === status ? '' : status)}
            className={`text-left bg-white rounded-lg shadow-sm border px-4 py-3 transition-colors ${statusFilter === status ? 'border-orange-400 ring-1 ring-orange-300' : 'border-gray-100 hover:border-gray-200'}`}
          >
            <div className="text-xs uppercase tracking-wide text-gray-500">{status}</div>
            <div className="text-xl font-bold text-gray-900">{counts[status]}</div>
          </button>
        ))}
      </div>

      <div className="flex flex-wrap gap-3 mb-4">
        <div className="relative flex-1 min-w-[220px]">
          <Search className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Search voucher no, paid to, purpose, reference…"
            className={`${inputClass} pl-9`}
          />
        </div>
        <select value={statusFilter} onChange={(e) => setStatusFilter(e.target.value)} className="border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500">
          <option value="">All statuses</option>
          {STATUSES.map((s) => <option key={s} value={s}>{s}</option>)}
        </select>
        <button onClick={() => fetchItems({ search: search || undefined, status: statusFilter || undefined })} className="px-3 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 inline-flex items-center">
          <RefreshCw className={`w-4 h-4 mr-1 ${loading ? 'animate-spin' : ''}`} /> Refresh
        </button>
      </div>

      {error && (
        <div className="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          Could not load cash vouchers.
        </div>
      )}

      <div className="bg-white rounded-lg shadow overflow-hidden">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Voucher</th>
              <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paid To / Purpose</th>
              <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
              <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
              <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mode</th>
              <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
              <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {items.map((v) => {
              const busy = busyId === v.id;
              const isDraft = v.status === 'Draft';
              // The employee who raised a draft can always fix or withdraw it,
              // even though that is not part of their baseline permissions.
              const isOwner = user?.id != null && Number(v.created_by) === Number(user.id);
              const mayEdit = isOwner || canModule('cash-vouchers', 'update');
              const mayDelete = isOwner || canModule('cash-vouchers', 'delete');
              return (
                <tr key={v.id} className={busy ? 'opacity-60' : ''}>
                  <td className="px-4 py-3 whitespace-nowrap text-sm">
                    <div className="font-medium text-gray-900">{v.voucher_no}</div>
                    <div className="text-gray-500">{fmtDate(v.voucher_date)}</div>
                  </td>
                  <td className="px-4 py-3 text-sm">
                    <div className="font-medium text-gray-900">{v.paid_to}</div>
                    {v.purpose && <div className="text-gray-500 max-w-xs truncate" title={v.purpose}>{v.purpose}</div>}
                  </td>
                  <td className="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{v.category || '--'}</td>
                  <td className="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold text-gray-900">{money(v.amount)}</td>
                  <td className="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                    <div>{v.payment_mode || 'Cash'}</div>
                    {v.reference_no && <div className="text-xs text-gray-400">{v.reference_no}</div>}
                    {v.attachment_path && (
                      <a
                        href={`${STORAGE_URL}/${v.attachment_path}`}
                        target="_blank"
                        rel="noreferrer"
                        className="text-xs text-orange-600 hover:text-orange-800 inline-flex items-center mt-1"
                      >
                        <Paperclip className="w-3 h-3 mr-1" /> Bill
                      </a>
                    )}
                  </td>
                  <td className="px-4 py-3 text-sm text-gray-600 max-w-[180px] truncate" title={employeeText(v.employee) || ''}>{employeeText(v.employee) || '--'}</td>
                  <td className="px-4 py-3 whitespace-nowrap text-sm">
                    <span className={`px-2 py-1 rounded-full text-xs font-semibold ${statusBadgeClass(v.status)}`}>{v.status}</span>
                    {v.approver?.name && !isDraft && (
                      <div className="text-xs text-gray-400 mt-1">by {v.approver.name}</div>
                    )}
                  </td>
                  <td className="px-4 py-3 whitespace-nowrap text-right text-sm">
                    <div className="inline-flex items-center gap-2">
                      {isDraft && (
                        <>
                          <OwnerOrPermission allowed={mayEdit} module="cash-vouchers" action="update" subject="this voucher">
                            <button onClick={() => openEdit(v)} title="Edit" className="text-blue-600 hover:text-blue-900">
                              <Edit2 className="w-4 h-4" />
                            </button>
                          </OwnerOrPermission>
                          <PermissionGate module="cash-vouchers" action="approve" subject="this voucher">
                            <button onClick={() => runAction(v.id, () => cashVoucherService.approve(v.id))} title="Approve" className="text-green-600 hover:text-green-800">
                              <CheckCircle2 className="w-4 h-4" />
                            </button>
                          </PermissionGate>
                          <PermissionGate module="cash-vouchers" action="reject" subject="this voucher">
                            <button onClick={() => { setRejectTarget(v); setRejectRemarks(''); setRejectError(null); }} title="Reject" className="text-red-600 hover:text-red-800">
                              <XCircle className="w-4 h-4" />
                            </button>
                          </PermissionGate>
                          <OwnerOrPermission allowed={mayDelete} module="cash-vouchers" action="delete" subject="this voucher">
                            <button onClick={() => { if (window.confirm(`Remove ${v.voucher_no}?`)) runAction(v.id, () => deleteItem(v.id)); }} title="Remove" className="text-gray-500 hover:text-gray-800">
                              <Trash2 className="w-4 h-4" />
                            </button>
                          </OwnerOrPermission>
                        </>
                      )}
                      {v.status === 'Approved' && (
                        <PermissionGate module="cash-vouchers" action="approve" subject="this voucher">
                          <button onClick={() => runAction(v.id, () => cashVoucherService.markPaid(v.id))} title="Mark paid" className="text-green-700 hover:text-green-900 inline-flex items-center">
                            <Banknote className="w-4 h-4 mr-1" /> Pay
                          </button>
                        </PermissionGate>
                      )}
                      {!isDraft && v.status !== 'Approved' && (
                        <span className="text-xs text-gray-400">
                          {v.status === 'Paid' ? fmtDate(v.paid_at) : 'decided'}
                        </span>
                      )}
                    </div>
                  </td>
                </tr>
              );
            })}
            {items.length === 0 && !loading && (
              <tr>
                <td colSpan="8" className="px-6 py-10 text-center text-sm text-gray-500">
                  No cash vouchers found. Create one to record a petty-cash payment.
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      {showModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl shadow-lg w-full max-w-3xl max-h-[90vh] overflow-y-auto">
            <div className="flex justify-between items-center p-6 border-b sticky top-0 bg-white z-10">
              <h2 className="text-xl font-bold text-gray-900">{editingId ? 'Edit Cash Voucher' : 'New Cash Voucher'}</h2>
              <button onClick={closeModal} className="text-gray-400 hover:text-gray-600"><X className="w-6 h-6" /></button>
            </div>

            <form onSubmit={handleSubmit} className="p-6">
              {formError && (
                <div className="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{formError}</div>
              )}

              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                  <input type="date" required className={inputClass}
                    value={form.voucher_date} onChange={(e) => setForm({ ...form, voucher_date: e.target.value })} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Amount *</label>
                  <input type="number" step="0.01" min="0" required className={inputClass}
                    value={form.amount} onChange={(e) => setForm({ ...form, amount: e.target.value })} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Paid To *</label>
                  <input type="text" required placeholder="Person / shop paid" className={inputClass}
                    value={form.paid_to} onChange={(e) => setForm({ ...form, paid_to: e.target.value })} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Category</label>
                  <input type="text" list="cash-voucher-categories" className={inputClass}
                    value={form.category} onChange={(e) => setForm({ ...form, category: e.target.value })} />
                  <datalist id="cash-voucher-categories">
                    {CATEGORIES.map((c) => <option key={c} value={c} />)}
                  </datalist>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Payment Mode</label>
                  <select className={inputClass}
                    value={form.payment_mode} onChange={(e) => setForm({ ...form, payment_mode: e.target.value })}>
                    {PAYMENT_MODES.map((m) => <option key={m} value={m}>{m}</option>)}
                  </select>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Reference No.</label>
                  <input type="text" className={inputClass}
                    value={form.reference_no} onChange={(e) => setForm({ ...form, reference_no: e.target.value })} />
                </div>
                <div className="md:col-span-2">
                  <label className="block text-sm font-medium text-gray-700 mb-1">Purpose</label>
                  <textarea rows="2" className={inputClass}
                    value={form.purpose} onChange={(e) => setForm({ ...form, purpose: e.target.value })} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Reimbursement To</label>
                  <div className={`${inputClass} bg-gray-50 text-gray-700`}>
                    {employeeText(reimbursement) || 'No employee profile linked to your account'}
                  </div>
                  <p className="text-xs text-gray-400 mt-1">Set automatically to the employee raising the voucher.</p>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Bill Attachment</label>
                  <input type="file" accept="image/*,.pdf" className="w-full text-sm text-gray-600"
                    onChange={(e) => setAttachment(e.target.files?.[0] || null)} />
                  {editingId && !attachment && <p className="text-xs text-gray-400 mt-1">Leave empty to keep the current file.</p>}
                </div>
                <div className="md:col-span-2">
                  <label className="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                  <textarea rows="2" className={inputClass}
                    value={form.remarks} onChange={(e) => setForm({ ...form, remarks: e.target.value })} />
                </div>
              </div>

              <div className="mt-8 flex justify-end gap-3 pt-6 border-t">
                <button type="button" onClick={closeModal} disabled={saving} className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-60">Cancel</button>
                <button type="submit" disabled={saving} className="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 disabled:bg-gray-300">
                  {saving ? 'Saving…' : (editingId ? 'Update Voucher' : 'Create Voucher')}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {rejectTarget && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl shadow-lg w-full max-w-md">
            <div className="flex justify-between items-center p-5 border-b">
              <h2 className="text-lg font-bold text-gray-900">Reject {rejectTarget.voucher_no}</h2>
              <button onClick={() => setRejectTarget(null)} className="text-gray-400 hover:text-gray-600"><X className="w-5 h-5" /></button>
            </div>
            <div className="p-5">
              {rejectError && <div className="mb-3 text-sm text-red-600">{rejectError}</div>}
              <label className="block text-sm font-medium text-gray-700 mb-1">Reason *</label>
              <textarea rows="3" className={`${inputClass} mb-4`}
                value={rejectRemarks} onChange={(e) => setRejectRemarks(e.target.value)} />
              <div className="flex justify-end gap-3">
                <button onClick={() => setRejectTarget(null)} className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                <button onClick={confirmReject} disabled={busyId === rejectTarget.id} className="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:bg-gray-300">Reject</button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default CashVouchersPage;
