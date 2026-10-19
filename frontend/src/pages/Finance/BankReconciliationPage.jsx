import React, { useCallback, useEffect, useMemo, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import {
  Landmark, RefreshCw, CheckCircle2, CircleDashed, Info, CalendarCheck,
} from 'lucide-react';
import { bankReconciliationService } from '../../services/accountingService';
import { formatMoney } from '../../utils/finance';
import PermissionGate from '../../components/Guards/PermissionGate';
import { formatDate } from '../../utils/date';

const inputClass = 'border border-gray-300 rounded-lg shadow-sm px-3 py-2 text-sm focus:ring-orange-500 focus:border-orange-500';
const labelClass = 'block text-xs font-medium text-gray-600 mb-1';

const today = () => new Date().toISOString().split('T')[0];
const fmtDate = (d) => formatDate(d, '—');

const firstError = (err) => {
  const errors = err?.response?.data?.data?.errors || err?.response?.data?.errors;
  if (errors) return Object.values(errors)[0]?.[0];
  return err?.response?.data?.data?.message || err?.response?.data?.message || 'Something went wrong.';
};

/**
 * Bank Reconciliation Statement (slice 4).
 *
 * Left: the bank accounts with their book balances. Right: one account's
 * statement — every posted line that moved it, with a running book balance and
 * a cleared / in-transit flag the user toggles manually.
 */
const BankReconciliationPage = () => {
  const navigate = useNavigate();
  const { ledgerId } = useParams();

  const [ledgers, setLedgers] = useState([]);
  const [selectedId, setSelectedId] = useState(ledgerId ? Number(ledgerId) : null);
  const [statement, setStatement] = useState(null);
  const [filters, setFilters] = useState({ from: '', to: '', status: 'all' });
  const [selected, setSelected] = useState(() => new Set());

  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);
  const [reconcileDate, setReconcileDate] = useState(today());
  const [note, setNote] = useState('');

  const loadLedgers = useCallback(async () => {
    const list = await bankReconciliationService.ledgers();
    setLedgers(list || []);
    return list || [];
  }, []);

  const loadStatement = useCallback(async (id, activeFilters) => {
    if (!id) { setStatement(null); return; }
    setLoading(true);
    setError(null);
    try {
      const data = await bankReconciliationService.statement(id, activeFilters);
      setStatement(data);
      setSelected(new Set());
    } catch (err) {
      setError(firstError(err));
      setStatement(null);
    } finally {
      setLoading(false);
    }
  }, []);

  // Initial load: bank accounts, then default the selection.
  useEffect(() => {
    let active = true;
    (async () => {
      try {
        const list = await loadLedgers();
        if (!active) return;
        if (!selectedId && list.length) setSelectedId(list[0].id);
      } catch (err) {
        if (active) setError(firstError(err));
      }
    })();
    return () => { active = false; };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    if (selectedId) loadStatement(selectedId, filters);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedId, filters]);

  const pickLedger = (id) => {
    setSelectedId(id);
    navigate(`/dashboard/accounting/bank-reconciliation/${id}`, { replace: true });
  };

  const entries = statement?.entries || [];
  const summary = statement?.summary;

  const selectableIds = useMemo(
    () => entries.filter((e) => !e.reconciled).map((e) => e.id),
    [entries]
  );
  const selectedUnreconciled = useMemo(
    () => [...selected].filter((id) => selectableIds.includes(id)),
    [selected, selectableIds]
  );
  const selectedReconciled = useMemo(
    () => entries.filter((e) => e.reconciled && selected.has(e.id)).map((e) => e.id),
    [entries, selected]
  );
  const allSelected = selectableIds.length > 0 && selectableIds.every((id) => selected.has(id));

  const toggle = (id) => {
    setSelected((prev) => {
      const next = new Set(prev);
      if (next.has(id)) next.delete(id); else next.add(id);
      return next;
    });
  };

  const toggleAll = () => {
    setSelected((prev) => {
      if (selectableIds.every((id) => prev.has(id))) {
        const next = new Set(prev);
        selectableIds.forEach((id) => next.delete(id));
        return next;
      }
      return new Set([...prev, ...selectableIds]);
    });
  };

  const afterMutation = async () => {
    await loadStatement(selectedId, filters);
    await loadLedgers().catch(() => {});
  };

  const reconcile = async () => {
    if (saving || selectedUnreconciled.length === 0) return;
    setSaving(true);
    setError(null);
    try {
      await bankReconciliationService.reconcile(selectedId, {
        entry_ids: selectedUnreconciled,
        reconciled_at: reconcileDate,
        note: note || null,
      });
      setNote('');
      await afterMutation();
    } catch (err) {
      setError(firstError(err));
    } finally {
      setSaving(false);
    }
  };

  const unreconcile = async () => {
    if (saving || selectedReconciled.length === 0) return;
    setSaving(true);
    setError(null);
    try {
      await bankReconciliationService.unreconcile(selectedId, { entry_ids: selectedReconciled });
      await afterMutation();
    } catch (err) {
      setError(firstError(err));
    } finally {
      setSaving(false);
    }
  };

  const refresh = () => { loadLedgers().catch(() => {}); if (selectedId) loadStatement(selectedId, filters); };

  return (
    <div className="p-6 max-w-7xl mx-auto">
      <div className="flex items-center justify-between gap-3 mb-6">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center">
            <Landmark className="w-5 h-5 text-orange-500" />
          </div>
          <div>
            <h1 className="text-2xl font-bold text-gray-900">Bank Reconciliation</h1>
            <p className="text-sm text-gray-500">Match the bank book against the statement, line by line.</p>
          </div>
        </div>
        <button onClick={refresh} className="inline-flex items-center text-sm text-gray-600 hover:text-gray-900" title="Refresh">
          <RefreshCw className="w-4 h-4 mr-1" /> Refresh
        </button>
      </div>

      {error && <div className="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{error}</div>}

      {ledgers.length === 0 ? (
        <div className="bg-white rounded-lg shadow p-10 text-center">
          <Landmark className="w-8 h-8 text-gray-300 mx-auto mb-3" />
          <p className="text-gray-700 font-medium">No bank accounts yet.</p>
          <p className="text-sm text-gray-500 mt-1">
            Mark a ledger as a bank account under Chart of Accounts, then reconcile it here.
          </p>
        </div>
      ) : (
        <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
          {/* Bank accounts */}
          <div className="lg:col-span-1 space-y-3">
            {ledgers.map((l) => {
              const active = l.id === selectedId;
              return (
                <button
                  key={l.id}
                  onClick={() => pickLedger(l.id)}
                  className={`w-full text-left rounded-lg border p-3 transition-colors ${
                    active ? 'border-orange-400 bg-orange-50' : 'border-gray-200 bg-white hover:border-gray-300'
                  }`}
                >
                  <div className="flex items-center justify-between gap-2">
                    <span className="font-medium text-gray-900 text-sm truncate">{l.name}</span>
                    {l.unreconciled_count > 0 && (
                      <span className="shrink-0 text-[10px] font-semibold bg-amber-100 text-amber-700 rounded-full px-2 py-0.5">
                        {l.unreconciled_count}
                      </span>
                    )}
                  </div>
                  {l.bank_account_number && <div className="text-xs text-gray-400 mt-0.5">A/c {l.bank_account_number}</div>}
                  <div className="mt-2 flex items-baseline justify-between">
                    <span className="text-[11px] uppercase tracking-wide text-gray-400">Book</span>
                    <span className="text-sm font-semibold text-gray-800">{formatMoney(l.book_balance)}</span>
                  </div>
                </button>
              );
            })}
          </div>

          {/* Statement */}
          <div className="lg:col-span-3 space-y-4">
            {statement && (
              <>
                {/* Summary */}
                <div className="bg-white rounded-lg shadow p-5">
                  <div className="flex items-center justify-between mb-4">
                    <div>
                      <h2 className="font-semibold text-gray-900">{statement.ledger.name}</h2>
                      <p className="text-xs text-gray-500">
                        Opening balance {formatMoney(statement.opening_balance)}
                      </p>
                    </div>
                    <div className="flex items-end gap-3">
                      <div>
                        <label className={labelClass}>From</label>
                        <input type="date" value={filters.from} onChange={(e) => setFilters({ ...filters, from: e.target.value })} className={inputClass} />
                      </div>
                      <div>
                        <label className={labelClass}>To</label>
                        <input type="date" value={filters.to} onChange={(e) => setFilters({ ...filters, to: e.target.value })} className={inputClass} />
                      </div>
                      <div>
                        <label className={labelClass}>Show</label>
                        <select value={filters.status} onChange={(e) => setFilters({ ...filters, status: e.target.value })} className={inputClass}>
                          <option value="all">All lines</option>
                          <option value="unreconciled">In transit</option>
                          <option value="reconciled">Cleared</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <SummaryTile label="As per books" value={formatMoney(summary?.book_balance)} tone="gray" />
                    <SummaryTile label="Less: deposits in transit" value={formatMoney(summary?.unreconciled_dr)} tone="amber" />
                    <SummaryTile label="Add: cheques not presented" value={formatMoney(summary?.unreconciled_cr)} tone="amber" />
                    <SummaryTile label="As per bank" value={formatMoney(summary?.statement_balance)} tone="green" />
                  </div>
                </div>

                {/* Reconcile toolbar */}
                <div className="bg-white rounded-lg shadow p-4 flex flex-wrap items-end gap-3">
                  <div>
                    <label className={labelClass}>Statement date</label>
                    <input type="date" value={reconcileDate} onChange={(e) => setReconcileDate(e.target.value)} className={inputClass} />
                  </div>
                  <div className="flex-1 min-w-[180px]">
                    <label className={labelClass}>Note (cheque no. / UTR)</label>
                    <input value={note} onChange={(e) => setNote(e.target.value)} className={inputClass} placeholder="Optional" />
                  </div>
                  <PermissionGate module="bank-reconciliation" action="update" subject="reconcile transactions">
                    <button
                      onClick={reconcile}
                      disabled={saving || selectedUnreconciled.length === 0}
                      className="inline-flex items-center bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm disabled:opacity-50"
                    >
                      <CalendarCheck className="w-4 h-4 mr-2" />
                      Reconcile selected ({selectedUnreconciled.length})
                    </button>
                  </PermissionGate>
                  <PermissionGate module="bank-reconciliation" action="update" subject="reconcile transactions">
                    <button
                      onClick={unreconcile}
                      disabled={saving || selectedReconciled.length === 0}
                      className="inline-flex items-center border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm disabled:opacity-50"
                    >
                      <CircleDashed className="w-4 h-4 mr-2" />
                      Unreconcile ({selectedReconciled.length})
                    </button>
                  </PermissionGate>
                </div>

                {/* Entries */}
                <div className="bg-white rounded-lg shadow overflow-hidden">
                  <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200 text-sm">
                      <thead className="bg-gray-50">
                        <tr>
                          <th className="px-3 py-3 w-10">
                            <input
                              type="checkbox"
                              checked={allSelected}
                              onChange={toggleAll}
                              disabled={selectableIds.length === 0}
                              className="rounded text-orange-500 focus:ring-orange-500"
                            />
                          </th>
                          {['Date', 'Voucher', 'Particulars', 'Debit', 'Credit', 'Balance', 'Status'].map((h) => (
                            <th key={h} className={`px-3 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider ${
                              ['Debit', 'Credit', 'Balance'].includes(h) ? 'text-right' : 'text-left'
                            }`}>{h}</th>
                          ))}
                        </tr>
                      </thead>
                      <tbody className="bg-white divide-y divide-gray-100">
                        {entries.map((e) => (
                          <tr key={e.id} className={e.reconciled ? 'bg-green-50/40' : ''}>
                            <td className="px-3 py-2">
                              <input
                                type="checkbox"
                                checked={selected.has(e.id)}
                                onChange={() => toggle(e.id)}
                                className="rounded text-orange-500 focus:ring-orange-500"
                              />
                            </td>
                            <td className="px-3 py-2 text-gray-600 whitespace-nowrap">{fmtDate(e.voucher_date)}</td>
                            <td className="px-3 py-2">
                              <div className="font-medium text-gray-800">{e.voucher_no}</div>
                              {e.reference_no && <div className="text-xs text-gray-400">Ref {e.reference_no}</div>}
                            </td>
                            <td className="px-3 py-2 text-gray-700 max-w-[16rem] truncate" title={e.narration || e.voucher_type}>
                              {e.narration || '—'}
                            </td>
                            <td className="px-3 py-2 text-right text-gray-800">{e.entry_type === 'Dr' ? formatMoney(e.amount) : ''}</td>
                            <td className="px-3 py-2 text-right text-gray-800">{e.entry_type === 'Cr' ? formatMoney(e.amount) : ''}</td>
                            <td className="px-3 py-2 text-right font-medium text-gray-900">{formatMoney(e.balance)}</td>
                            <td className="px-3 py-2">
                              {e.reconciled ? (
                                <span className="inline-flex items-center gap-1 text-xs text-green-700" title={[e.reconciled_by, e.reconciliation_note].filter(Boolean).join(' · ')}>
                                  <CheckCircle2 className="w-3.5 h-3.5" /> {fmtDate(e.reconciled_at)}
                                </span>
                              ) : (
                                <span className="inline-flex items-center gap-1 text-xs text-amber-600">
                                  <CircleDashed className="w-3.5 h-3.5" /> In transit
                                </span>
                              )}
                            </td>
                          </tr>
                        ))}
                        {entries.length === 0 && !loading && (
                          <tr>
                            <td colSpan={8} className="px-4 py-10 text-center text-gray-500">
                              No transactions in this view.
                            </td>
                          </tr>
                        )}
                      </tbody>
                    </table>
                  </div>
                </div>

                <div className="text-xs text-gray-400 flex items-center gap-1">
                  <Info className="w-3 h-3" />
                  Reconciliation is manual: tick the lines that appear on the bank statement and set the statement date.
                </div>
              </>
            )}

            {loading && !statement && <div className="p-6 text-gray-500">Loading statement…</div>}
          </div>
        </div>
      )}
    </div>
  );
};

const TONES = {
  gray: 'bg-gray-50 text-gray-900',
  amber: 'bg-amber-50 text-amber-800',
  green: 'bg-green-50 text-green-800',
};

const SummaryTile = ({ label, value, tone = 'gray' }) => (
  <div className={`rounded-lg p-3 ${TONES[tone]}`}>
    <div className="text-[11px] uppercase tracking-wide opacity-70">{label}</div>
    <div className="text-lg font-semibold mt-1">{value}</div>
  </div>
);

export default BankReconciliationPage;
