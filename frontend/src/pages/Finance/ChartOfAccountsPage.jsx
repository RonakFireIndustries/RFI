import React, { useEffect, useMemo, useState } from 'react';
import {
  Plus, X, Edit2, Trash2, Search, RefreshCw, Landmark, Wallet, Star,
} from 'lucide-react';
import { useAccountGroupStore, useLedgerStore } from '../../store/accountingStore';
import PermissionGate from '../../components/Guards/PermissionGate';
import { formatMoney } from '../../utils/finance';
import { customersService } from '../../services/customersService';
import { suppliersService } from '../../services/suppliersService';

const NATURES = ['Assets', 'Liabilities', 'Income', 'Expenses'];

const NATURE_BADGE = {
  Assets: 'bg-blue-50 text-blue-700',
  Liabilities: 'bg-amber-50 text-amber-700',
  Income: 'bg-green-50 text-green-700',
  Expenses: 'bg-rose-50 text-rose-700',
};

const GST_TYPES = ['Regular', 'Composition', 'Unregistered'];

const EMPTY_GROUP = {
  name: '',
  parent_id: '',
  nature: 'Assets',
  affects_gross_profit: false,
  is_party_group: false,
  party_type: '',
  sort_order: 0,
};

const EMPTY_LEDGER = {
  name: '',
  group_id: '',
  opening_balance: '',
  opening_type: 'Dr',
  is_bank: false,
  is_cash: false,
  bank_name: '',
  bank_account_number: '',
  bank_ifsc: '',
  bank_branch: '',
  gst_number: '',
  gst_type: '',
  pan_number: '',
  party_type: '',
  party_id: '',
  status: 'active',
  notes: '',
};

const inputClass = 'w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 text-sm focus:ring-orange-500 focus:border-orange-500';
const labelClass = 'block text-xs font-medium text-gray-600 mb-1';

/** Turn '' / undefined into null so the API receives clean optional values. */
const nullable = (value) => (value === '' || value === undefined ? null : value);

const firstError = (err) => {
  const errors = err?.response?.data?.data?.errors || err?.response?.data?.errors;
  if (errors) return Object.values(errors)[0]?.[0];
  return err?.response?.data?.data?.message || err?.response?.data?.message || 'Could not save.';
};

const ChartOfAccountsPage = ({ defaultTab = 'groups' }) => {
  const groups = useAccountGroupStore();
  const ledgers = useLedgerStore();

  const [tab, setTab] = useState(defaultTab);
  const [search, setSearch] = useState('');
  const [natureFilter, setNatureFilter] = useState('');
  const [groupFilter, setGroupFilter] = useState('');

  // The Groups and Ledgers sidebar entries render this same page; keep the tab
  // in sync when navigating between them.
  useEffect(() => { setTab(defaultTab); }, [defaultTab]);

  // Group modal
  const [showGroupModal, setShowGroupModal] = useState(false);
  const [editingGroupId, setEditingGroupId] = useState(null);
  const [groupForm, setGroupForm] = useState({ ...EMPTY_GROUP });
  const [groupError, setGroupError] = useState(null);

  // Ledger modal
  const [showLedgerModal, setShowLedgerModal] = useState(false);
  const [editingLedgerId, setEditingLedgerId] = useState(null);
  const [ledgerForm, setLedgerForm] = useState({ ...EMPTY_LEDGER });
  const [ledgerError, setLedgerError] = useState(null);
  const [partyOptions, setPartyOptions] = useState({ customer: [], supplier: [] });

  const loadGroups = () => groups.fetchItems({}).catch(() => {});
  const loadLedgers = () => ledgers.fetchItems({}).catch(() => {});

  useEffect(() => {
    loadGroups();
    loadLedgers();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const groupById = useMemo(() => {
    const map = new Map();
    groups.items.forEach((g) => map.set(g.id, g));
    return map;
  }, [groups.items]);

  const filteredGroups = useMemo(() => {
    const term = search.trim().toLowerCase();
    return groups.items.filter((g) => {
      if (natureFilter && g.nature !== natureFilter) return false;
      if (term && !g.name.toLowerCase().includes(term)) return false;
      return true;
    });
  }, [groups.items, natureFilter, search]);

  const filteredLedgers = useMemo(() => {
    const term = search.trim().toLowerCase();
    return ledgers.items.filter((l) => {
      if (groupFilter && String(l.group_id) !== String(groupFilter)) return false;
      if (natureFilter && l.group?.nature !== natureFilter) return false;
      if (term && !l.name.toLowerCase().includes(term)) return false;
      return true;
    });
  }, [ledgers.items, groupFilter, natureFilter, search]);

  const groupsByNature = useMemo(() => {
    const buckets = { Assets: [], Liabilities: [], Income: [], Expenses: [] };
    groups.items.forEach((g) => {
      if (buckets[g.nature]) buckets[g.nature].push(g);
    });
    return buckets;
  }, [groups.items]);

  const selectedLedgerGroup = groupById.get(ledgerForm.group_id);
  const ledgerIsPartyGroup = Boolean(selectedLedgerGroup?.is_party_group);

  /* ----------------------------- Group actions ---------------------------- */

  const openCreateGroup = () => {
    setEditingGroupId(null);
    setGroupForm({ ...EMPTY_GROUP });
    setGroupError(null);
    setShowGroupModal(true);
  };

  const openEditGroup = (group) => {
    setEditingGroupId(group.id);
    setGroupForm({
      name: group.name,
      parent_id: group.parent_id ?? '',
      nature: group.nature,
      affects_gross_profit: Boolean(group.affects_gross_profit),
      is_party_group: Boolean(group.is_party_group),
      party_type: group.party_type ?? '',
      sort_order: group.sort_order ?? 0,
    });
    setGroupError(null);
    setShowGroupModal(true);
  };

  const submitGroup = async (e) => {
    e.preventDefault();
    if (groups.saving) return;
    setGroupError(null);

    const payload = {
      name: groupForm.name.trim(),
      parent_id: nullable(groupForm.parent_id),
      nature: nullable(groupForm.nature),
      is_primary: !groupForm.parent_id,
      affects_gross_profit: Boolean(groupForm.affects_gross_profit),
      is_party_group: Boolean(groupForm.is_party_group),
      party_type: groupForm.is_party_group ? nullable(groupForm.party_type) : null,
      sort_order: Number(groupForm.sort_order) || 0,
    };

    try {
      if (editingGroupId) {
        await groups.updateItem(editingGroupId, payload);
      } else {
        await groups.createItem(payload);
      }
      setShowGroupModal(false);
      loadGroups();
    } catch (err) {
      setGroupError(firstError(err));
    }
  };

  const removeGroup = async (group) => {
    if (!window.confirm(`Remove the group "${group.name}"?`)) return;
    try {
      await groups.deleteItem(group.id);
    } catch (err) {
      window.alert(firstError(err));
    }
  };

  /* ---------------------------- Ledger actions ---------------------------- */

  const loadPartyOptions = async () => {
    const [customers, suppliers] = await Promise.allSettled([
      customersService.list({}),
      suppliersService.list({}),
    ]);
    setPartyOptions({
      customer: customers.status === 'fulfilled' ? customers.value : [],
      supplier: suppliers.status === 'fulfilled' ? suppliers.value : [],
    });
  };

  const openCreateLedger = () => {
    setEditingLedgerId(null);
    setLedgerForm({ ...EMPTY_LEDGER });
    setLedgerError(null);
    setShowLedgerModal(true);
    loadPartyOptions();
  };

  const openEditLedger = (ledger) => {
    setEditingLedgerId(ledger.id);
    setLedgerForm({
      name: ledger.name,
      group_id: ledger.group_id ?? '',
      opening_balance: ledger.opening_balance ?? '',
      opening_type: ledger.opening_type ?? 'Dr',
      is_bank: Boolean(ledger.is_bank),
      is_cash: Boolean(ledger.is_cash),
      bank_name: ledger.bank_name ?? '',
      bank_account_number: ledger.bank_account_number ?? '',
      bank_ifsc: ledger.bank_ifsc ?? '',
      bank_branch: ledger.bank_branch ?? '',
      gst_number: ledger.gst_number ?? '',
      gst_type: ledger.gst_type ?? '',
      pan_number: ledger.pan_number ?? '',
      party_type: ledger.party_type ?? '',
      party_id: ledger.party_id ?? '',
      status: ledger.status ?? 'active',
      notes: ledger.notes ?? '',
    });
    setLedgerError(null);
    setShowLedgerModal(true);
    loadPartyOptions();
  };

  const submitLedger = async (e) => {
    e.preventDefault();
    if (ledgers.saving) return;
    setLedgerError(null);

    const partyType = selectedLedgerGroup?.is_party_group
      ? selectedLedgerGroup.party_type
      : nullable(ledgerForm.party_type);

    const payload = {
      name: ledgerForm.name.trim(),
      group_id: ledgerForm.group_id,
      opening_balance: Number(ledgerForm.opening_balance) || 0,
      opening_type: ledgerForm.opening_type || 'Dr',
      is_bank: Boolean(ledgerForm.is_bank),
      is_cash: Boolean(ledgerForm.is_cash),
      bank_name: nullable(ledgerForm.bank_name),
      bank_account_number: nullable(ledgerForm.bank_account_number),
      bank_ifsc: nullable(ledgerForm.bank_ifsc),
      bank_branch: nullable(ledgerForm.bank_branch),
      gst_number: nullable(ledgerForm.gst_number),
      gst_type: nullable(ledgerForm.gst_type),
      pan_number: nullable(ledgerForm.pan_number),
      party_type: partyType,
      party_id: partyType ? nullable(ledgerForm.party_id) : null,
      status: ledgerForm.status || 'active',
      notes: nullable(ledgerForm.notes),
    };

    try {
      if (editingLedgerId) {
        await ledgers.updateItem(editingLedgerId, payload);
      } else {
        await ledgers.createItem(payload);
      }
      setShowLedgerModal(false);
      loadLedgers();
    } catch (err) {
      setLedgerError(firstError(err));
    }
  };

  const removeLedger = async (ledger) => {
    if (!window.confirm(`Remove the ledger "${ledger.name}"?`)) return;
    try {
      await ledgers.deleteItem(ledger.id);
    } catch (err) {
      window.alert(firstError(err));
    }
  };

  const activeList = tab === 'groups' ? groups : ledgers;
  const isSystemRow = (row) => Boolean(row.is_system);

  return (
    <div className="p-6 max-w-7xl mx-auto">
      <div className="flex flex-wrap justify-between items-center gap-3 mb-6">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Chart of Accounts</h1>
          <p className="text-sm text-gray-500">Tally-style account groups and ledgers that every voucher posts against.</p>
        </div>
        <div className="flex gap-2">
          {tab === 'groups' ? (
            <PermissionGate module="account-groups" action="create" subject="account groups">
              <button onClick={openCreateGroup} className="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg flex items-center shadow-sm transition-colors">
                <Plus className="w-4 h-4 mr-2" /> New Group
              </button>
            </PermissionGate>
          ) : (
            <PermissionGate module="ledgers" action="create" subject="ledgers">
              <button onClick={openCreateLedger} className="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg flex items-center shadow-sm transition-colors">
                <Plus className="w-4 h-4 mr-2" /> New Ledger
              </button>
            </PermissionGate>
          )}
        </div>
      </div>

      <div className="flex gap-2 mb-6 border-b border-gray-200">
        {[
          { key: 'groups', label: `Groups (${groups.items.length})` },
          { key: 'ledgers', label: `Ledgers (${ledgers.items.length})` },
        ].map((t) => (
          <button
            key={t.key}
            onClick={() => { setTab(t.key); setSearch(''); }}
            className={`px-4 py-2 text-sm font-medium -mb-px border-b-2 transition-colors ${tab === t.key ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700'}`}
          >
            {t.label}
          </button>
        ))}
      </div>

      <div className="flex flex-wrap gap-3 mb-4">
        <div className="relative flex-1 min-w-[220px]">
          <Search className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder={`Search ${tab}…`}
            className={`${inputClass} pl-9`}
          />
        </div>
        <select value={natureFilter} onChange={(e) => setNatureFilter(e.target.value)} className={`${inputClass} max-w-[180px]`}>
          <option value="">All natures</option>
          {NATURES.map((n) => <option key={n} value={n}>{n}</option>)}
        </select>
        {tab === 'ledgers' && (
          <select value={groupFilter} onChange={(e) => setGroupFilter(e.target.value)} className={`${inputClass} max-w-[220px]`}>
            <option value="">All groups</option>
            {groups.items.map((g) => <option key={g.id} value={g.id}>{g.name}</option>)}
          </select>
        )}
        <button
          onClick={() => (tab === 'groups' ? loadGroups() : loadLedgers())}
          className="px-3 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 inline-flex items-center text-sm"
        >
          <RefreshCw className={`w-4 h-4 mr-1 ${activeList.loading ? 'animate-spin' : ''}`} /> Refresh
        </button>
      </div>

      {activeList.error && (
        <div className="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          Could not load {tab}. You may not have access.
        </div>
      )}

      {tab === 'groups' ? (
        <div className="bg-white rounded-lg shadow overflow-hidden">
          <div className="overflow-x-auto max-w-full">
            <table className="min-w-full divide-y divide-gray-200 text-sm">
              <thead className="bg-gray-50">
                <tr>
                  {['Group', 'Nature', 'Parent', 'Ledgers', ''].map((h) => (
                    <th key={h} className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{h}</th>
                  ))}
                  <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {filteredGroups.map((g) => (
                  <tr key={g.id}>
                    <td className="px-4 py-3">
                      <div className="font-medium text-gray-900 flex items-center gap-1">
                        {g.name}
                        {g.is_system && <Star className="w-3 h-3 text-amber-400" title="Default group" />}
                      </div>
                      {g.is_party_group && <div className="text-xs text-gray-400">Party group · {g.party_type}</div>}
                    </td>
                    <td className="px-4 py-3">
                      <span className={`px-2 py-1 rounded-full text-xs font-semibold ${NATURE_BADGE[g.nature] || 'bg-gray-100 text-gray-700'}`}>{g.nature}</span>
                      {g.affects_gross_profit && <div className="text-xs text-gray-400 mt-1">Direct</div>}
                    </td>
                    <td className="px-4 py-3 text-gray-600">{g.parent?.name || '—'}</td>
                    <td className="px-4 py-3 text-gray-600">{g.ledgers_count ?? 0}</td>
                    <td className="px-4 py-3 text-gray-400 text-xs">{g.is_primary ? 'Primary' : 'Sub-group'}</td>
                    <td className="px-4 py-3 text-right">
                      <div className="inline-flex items-center gap-3">
                        <PermissionGate module="account-groups" action="update" subject="this group">
                          <button onClick={() => openEditGroup(g)} title="Edit" className="text-blue-600 hover:text-blue-900 disabled:opacity-40">
                            <Edit2 className="w-4 h-4" />
                          </button>
                        </PermissionGate>
                        <PermissionGate module="account-groups" action="delete" subject="this group">
                          <button
                            onClick={() => removeGroup(g)}
                            disabled={isSystemRow(g)}
                            title={g.is_system ? 'Default groups cannot be removed' : 'Remove'}
                            className="text-red-500 hover:text-red-700 disabled:opacity-30 disabled:cursor-not-allowed"
                          >
                            <Trash2 className="w-4 h-4" />
                          </button>
                        </PermissionGate>
                      </div>
                    </td>
                  </tr>
                ))}
                {filteredGroups.length === 0 && !activeList.loading && (
                  <tr><td colSpan={6} className="px-4 py-8 text-center text-gray-500">No groups found.</td></tr>
                )}
              </tbody>
            </table>
          </div>
        </div>
      ) : (
        <div className="bg-white rounded-lg shadow overflow-hidden">
          <div className="overflow-x-auto max-w-full">
            <table className="min-w-full divide-y divide-gray-200 text-sm">
              <thead className="bg-gray-50">
                <tr>
                  {['Ledger', 'Group', 'Opening', 'Flags', 'GST No.', 'Party'].map((h) => (
                    <th key={h} className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{h}</th>
                  ))}
                  <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {filteredLedgers.map((l) => (
                  <tr key={l.id}>
                    <td className="px-4 py-3">
                      <div className="font-medium text-gray-900 flex items-center gap-1">
                        {l.name}
                        {l.is_system && <Star className="w-3 h-3 text-amber-400" title="Default ledger" />}
                      </div>
                      {l.status === 'inactive' && <div className="text-xs text-gray-400">Inactive</div>}
                    </td>
                    <td className="px-4 py-3 text-gray-600">
                      {l.group?.name || '—'}
                      {l.group?.nature && (
                        <span className={`ml-2 px-2 py-0.5 rounded-full text-[10px] font-semibold ${NATURE_BADGE[l.group.nature] || 'bg-gray-100 text-gray-700'}`}>{l.group.nature}</span>
                      )}
                    </td>
                    <td className="px-4 py-3 whitespace-nowrap text-right font-medium text-gray-900">
                      {formatMoney(l.opening_balance)}
                      <span className="ml-1 text-xs text-gray-400">{l.opening_type}</span>
                    </td>
                    <td className="px-4 py-3">
                      <div className="flex flex-wrap gap-1">
                        {l.is_cash && <span className="inline-flex items-center text-xs text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full"><Wallet className="w-3 h-3 mr-1" />Cash</span>}
                        {l.is_bank && <span className="inline-flex items-center text-xs text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full"><Landmark className="w-3 h-3 mr-1" />Bank</span>}
                        {!l.is_cash && !l.is_bank && <span className="text-xs text-gray-300">—</span>}
                      </div>
                    </td>
                    <td className="px-4 py-3 text-gray-600 text-xs">{l.gst_number || '—'}</td>
                    <td className="px-4 py-3 text-gray-600 text-xs">
                      {l.party_type ? `${l.party_type}${l.party_id ? ` #${l.party_id}` : ''}` : '—'}
                    </td>
                    <td className="px-4 py-3 text-right">
                      <div className="inline-flex items-center gap-3">
                        <PermissionGate module="ledgers" action="update" subject="this ledger">
                          <button onClick={() => openEditLedger(l)} title="Edit" className="text-blue-600 hover:text-blue-900">
                            <Edit2 className="w-4 h-4" />
                          </button>
                        </PermissionGate>
                        <PermissionGate module="ledgers" action="delete" subject="this ledger">
                          <button
                            onClick={() => removeLedger(l)}
                            disabled={isSystemRow(l)}
                            title={l.is_system ? 'Default ledgers cannot be removed' : 'Remove'}
                            className="text-red-500 hover:text-red-700 disabled:opacity-30 disabled:cursor-not-allowed"
                          >
                            <Trash2 className="w-4 h-4" />
                          </button>
                        </PermissionGate>
                      </div>
                    </td>
                  </tr>
                ))}
                {filteredLedgers.length === 0 && !activeList.loading && (
                  <tr><td colSpan={7} className="px-4 py-8 text-center text-gray-500">No ledgers found.</td></tr>
                )}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* ------------------------------ Group modal ----------------------------- */}
      {showGroupModal && (
        <div className="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4">
          <div className="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100">
              <h2 className="text-lg font-semibold text-gray-900">{editingGroupId ? 'Edit Group' : 'New Account Group'}</h2>
              <button onClick={() => setShowGroupModal(false)} className="text-gray-400 hover:text-gray-600"><X className="w-5 h-5" /></button>
            </div>
            <form onSubmit={submitGroup} className="px-6 py-5 space-y-4">
              {groupError && <div className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{groupError}</div>}
              <div>
                <label className={labelClass}>Name *</label>
                <input required value={groupForm.name} onChange={(e) => setGroupForm({ ...groupForm, name: e.target.value })} className={inputClass} placeholder="e.g. Current Assets" />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className={labelClass}>Parent group</label>
                  <select value={groupForm.parent_id} onChange={(e) => setGroupForm({ ...groupForm, parent_id: e.target.value })} className={inputClass}>
                    <option value="">— None (primary group) —</option>
                    {groups.items.filter((g) => g.id !== editingGroupId).map((g) => <option key={g.id} value={g.id}>{g.name}</option>)}
                  </select>
                </div>
                <div>
                  <label className={labelClass}>Nature</label>
                  <select value={groupForm.nature} onChange={(e) => setGroupForm({ ...groupForm, nature: e.target.value })} className={inputClass}>
                    {NATURES.map((n) => <option key={n} value={n}>{n}</option>)}
                  </select>
                </div>
              </div>
              <div className="flex flex-wrap gap-5">
                <label className="inline-flex items-center gap-2 text-sm text-gray-700">
                  <input type="checkbox" checked={groupForm.affects_gross_profit} onChange={(e) => setGroupForm({ ...groupForm, affects_gross_profit: e.target.checked })} className="rounded text-orange-500 focus:ring-orange-500" />
                  Affects gross profit (direct)
                </label>
                <label className="inline-flex items-center gap-2 text-sm text-gray-700">
                  <input type="checkbox" checked={groupForm.is_party_group} onChange={(e) => setGroupForm({ ...groupForm, is_party_group: e.target.checked, party_type: e.target.checked ? groupForm.party_type : '' })} className="rounded text-orange-500 focus:ring-orange-500" />
                  Party group
                </label>
              </div>
              {groupForm.is_party_group && (
                <div>
                  <label className={labelClass}>Party type</label>
                  <select value={groupForm.party_type} onChange={(e) => setGroupForm({ ...groupForm, party_type: e.target.value })} className={inputClass}>
                    <option value="">— Select —</option>
                    <option value="customer">Customer (Sundry Debtor)</option>
                    <option value="supplier">Supplier (Sundry Creditor)</option>
                  </select>
                </div>
              )}
              <div className="flex justify-end gap-2 pt-2">
                <button type="button" onClick={() => setShowGroupModal(false)} className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" disabled={groups.saving} className="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg disabled:opacity-60">
                  {groups.saving ? 'Saving…' : 'Save Group'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* ----------------------------- Ledger modal ----------------------------- */}
      {showLedgerModal && (
        <div className="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4">
          <div className="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100">
              <h2 className="text-lg font-semibold text-gray-900">{editingLedgerId ? 'Edit Ledger' : 'New Ledger Account'}</h2>
              <button onClick={() => setShowLedgerModal(false)} className="text-gray-400 hover:text-gray-600"><X className="w-5 h-5" /></button>
            </div>
            <form onSubmit={submitLedger} className="px-6 py-5 space-y-4">
              {ledgerError && <div className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{ledgerError}</div>}
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className={labelClass}>Name *</label>
                  <input required value={ledgerForm.name} onChange={(e) => setLedgerForm({ ...ledgerForm, name: e.target.value })} className={inputClass} placeholder="e.g. HDFC Bank" />
                </div>
                <div>
                  <label className={labelClass}>Under group *</label>
                  <select required value={ledgerForm.group_id} onChange={(e) => setLedgerForm({ ...ledgerForm, group_id: e.target.value })} className={inputClass}>
                    <option value="">— Select group —</option>
                    {NATURES.map((n) => (
                      groupsByNature[n].length > 0 && (
                        <optgroup key={n} label={n}>
                          {groupsByNature[n].map((g) => <option key={g.id} value={g.id}>{g.name}</option>)}
                        </optgroup>
                      )
                    ))}
                  </select>
                </div>
              </div>

              <div className="grid grid-cols-3 gap-4">
                <div className="col-span-2">
                  <label className={labelClass}>Opening balance</label>
                  <input type="number" step="0.01" min="0" value={ledgerForm.opening_balance} onChange={(e) => setLedgerForm({ ...ledgerForm, opening_balance: e.target.value })} className={inputClass} placeholder="0.00" />
                </div>
                <div>
                  <label className={labelClass}>Dr / Cr</label>
                  <select value={ledgerForm.opening_type} onChange={(e) => setLedgerForm({ ...ledgerForm, opening_type: e.target.value })} className={inputClass}>
                    <option value="Dr">Dr</option>
                    <option value="Cr">Cr</option>
                  </select>
                </div>
              </div>

              <div className="flex flex-wrap gap-5">
                <label className="inline-flex items-center gap-2 text-sm text-gray-700">
                  <input type="checkbox" checked={ledgerForm.is_cash} onChange={(e) => setLedgerForm({ ...ledgerForm, is_cash: e.target.checked })} className="rounded text-orange-500 focus:ring-orange-500" />
                  Cash account
                </label>
                <label className="inline-flex items-center gap-2 text-sm text-gray-700">
                  <input type="checkbox" checked={ledgerForm.is_bank} onChange={(e) => setLedgerForm({ ...ledgerForm, is_bank: e.target.checked })} className="rounded text-orange-500 focus:ring-orange-500" />
                  Bank account
                </label>
              </div>

              {ledgerForm.is_bank && (
                <div className="grid grid-cols-2 gap-4 rounded-lg bg-gray-50 p-4">
                  <div>
                    <label className={labelClass}>Bank name</label>
                    <input value={ledgerForm.bank_name} onChange={(e) => setLedgerForm({ ...ledgerForm, bank_name: e.target.value })} className={inputClass} />
                  </div>
                  <div>
                    <label className={labelClass}>Account number</label>
                    <input value={ledgerForm.bank_account_number} onChange={(e) => setLedgerForm({ ...ledgerForm, bank_account_number: e.target.value })} className={inputClass} />
                  </div>
                  <div>
                    <label className={labelClass}>IFSC</label>
                    <input value={ledgerForm.bank_ifsc} onChange={(e) => setLedgerForm({ ...ledgerForm, bank_ifsc: e.target.value })} className={inputClass} />
                  </div>
                  <div>
                    <label className={labelClass}>Branch</label>
                    <input value={ledgerForm.bank_branch} onChange={(e) => setLedgerForm({ ...ledgerForm, bank_branch: e.target.value })} className={inputClass} />
                  </div>
                </div>
              )}

              <div className="grid grid-cols-3 gap-4">
                <div>
                  <label className={labelClass}>GSTIN</label>
                  <input value={ledgerForm.gst_number} onChange={(e) => setLedgerForm({ ...ledgerForm, gst_number: e.target.value })} className={inputClass} />
                </div>
                <div>
                  <label className={labelClass}>GST registration</label>
                  <select value={ledgerForm.gst_type} onChange={(e) => setLedgerForm({ ...ledgerForm, gst_type: e.target.value })} className={inputClass}>
                    <option value="">— None —</option>
                    {GST_TYPES.map((t) => <option key={t} value={t}>{t}</option>)}
                  </select>
                </div>
                <div>
                  <label className={labelClass}>PAN</label>
                  <input value={ledgerForm.pan_number} onChange={(e) => setLedgerForm({ ...ledgerForm, pan_number: e.target.value })} className={inputClass} />
                </div>
              </div>

              {ledgerIsPartyGroup && (
                <div>
                  <label className={labelClass}>Link to {selectedLedgerGroup.party_type}</label>
                  <select value={ledgerForm.party_id} onChange={(e) => setLedgerForm({ ...ledgerForm, party_id: e.target.value })} className={inputClass}>
                    <option value="">— Not linked —</option>
                    {(partyOptions[selectedLedgerGroup.party_type] || []).map((p) => <option key={p.id} value={p.id}>{p.name}</option>)}
                  </select>
                </div>
              )}

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className={labelClass}>Status</label>
                  <select value={ledgerForm.status} onChange={(e) => setLedgerForm({ ...ledgerForm, status: e.target.value })} className={inputClass}>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </div>
                <div>
                  <label className={labelClass}>Notes</label>
                  <input value={ledgerForm.notes} onChange={(e) => setLedgerForm({ ...ledgerForm, notes: e.target.value })} className={inputClass} />
                </div>
              </div>

              <div className="flex justify-end gap-2 pt-2">
                <button type="button" onClick={() => setShowLedgerModal(false)} className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" disabled={ledgers.saving} className="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg disabled:opacity-60">
                  {ledgers.saving ? 'Saving…' : 'Save Ledger'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};

export default ChartOfAccountsPage;
