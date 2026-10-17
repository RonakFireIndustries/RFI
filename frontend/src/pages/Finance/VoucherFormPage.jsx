import React, { useEffect, useMemo, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { ArrowLeft, Plus, Trash2, RefreshCw, Save, Info } from 'lucide-react';
import { useLedgerStore } from '../../store/accountingStore';
import { voucherService } from '../../services/accountingService';
import { VOUCHER_TYPES, isPartyVoucher, partyTypeForVoucher } from '../../config/vouchers';
import { formatMoney } from '../../utils/finance';

const inputClass = 'w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 text-sm focus:ring-orange-500 focus:border-orange-500';
const labelClass = 'block text-xs font-medium text-gray-600 mb-1';

const today = () => new Date().toISOString().split('T')[0];
const nullable = (value) => (value === '' || value === undefined || value === null ? null : value);
const round2 = (n) => Math.round((Number(n) || 0) * 100) / 100;

const firstError = (err) => {
  const errors = err?.response?.data?.data?.errors || err?.response?.data?.errors;
  if (errors) return Object.values(errors)[0]?.[0];
  return err?.response?.data?.data?.message || err?.response?.data?.message || 'Could not save the voucher.';
};

const newRow = (entryType) => ({ ledger_id: '', entry_type: entryType, amount: '', narration: '' });

/**
 * Create / edit a single Tally voucher.
 *
 * Contra and Journal use a free Dr/Cr grid; Payment and Receipt use the classic
 * party + bank layout with a bill-wise allocation panel underneath.
 */
const VoucherFormPage = () => {
  const { id, type: typeParam } = useParams();
  const navigate = useNavigate();
  const ledgersStore = useLedgerStore();
  const isEdit = Boolean(id);

  const [type, setType] = useState(typeParam || '');
  const [loading, setLoading] = useState(isEdit);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);

  const [form, setForm] = useState({ voucher_date: today(), reference_no: '', narration: '' });
  const [entries, setEntries] = useState([newRow('Dr'), newRow('Cr')]);

  // Party voucher state (Payment / Receipt).
  const [partyLedgerId, setPartyLedgerId] = useState('');
  const [bankLedgerId, setBankLedgerId] = useState('');
  const [amount, setAmount] = useState('');
  const [allocations, setAllocations] = useState({});
  const [loadingBills, setLoadingBills] = useState(false);

  const config = VOUCHER_TYPES[type] || { label: type };
  const partyVoucher = isPartyVoucher(type);

  /* ------------------------------ data load ----------------------------- */

  useEffect(() => {
    ledgersStore.fetchItems({ status: 'active' }).catch(() => {});
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    if (!isEdit) return;
    let active = true;

    (async () => {
      setLoading(true);
      try {
        const voucher = await voucherService.get(id);
        if (!active) return;
        setType(voucher.voucher_type);
        setForm({
          voucher_date: (voucher.voucher_date || '').slice(0, 10),
          reference_no: voucher.reference_no || '',
          narration: voucher.narration || '',
        });

        if (isPartyVoucher(voucher.voucher_type)) {
          const party = voucher.entries?.find((e) => e.ledger?.party_type);
          const bank = voucher.entries?.find((e) => e.ledger?.is_bank || e.ledger?.is_cash);
          setPartyLedgerId(party ? String(party.ledger_id) : (voucher.party_ledger_id ? String(voucher.party_ledger_id) : ''));
          setBankLedgerId(bank ? String(bank.ledger_id) : '');
          setAmount(String(voucher.amount ?? ''));

          const seeded = {};
          (voucher.bill_allocations || []).forEach((a) => {
            const key = `${a.bill_type}:${a.bill_id ?? ''}`;
            seeded[key] = {
              key,
              bill_type: a.bill_type,
              bill_id: a.bill_id ?? null,
              bill_name: a.bill_name || a.bill_type,
              balance: null,
              amount: String(a.amount ?? ''),
            };
          });
          setAllocations(seeded);
        } else {
          setEntries(
            (voucher.entries || []).map((e) => ({
              ledger_id: String(e.ledger_id),
              entry_type: e.entry_type,
              amount: String(e.amount ?? ''),
              narration: e.narration || '',
            }))
          );
        }
      } catch (err) {
        if (active) setError(firstError(err));
      } finally {
        if (active) setLoading(false);
      }
    })();

    return () => { active = false; };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [id, isEdit]);

  /* --------------------------- outstanding bills ------------------------ */

  useEffect(() => {
    if (!partyVoucher || !partyLedgerId) return;
    let active = true;

    (async () => {
      setLoadingBills(true);
      try {
        const data = await voucherService.partyOutstanding(partyLedgerId);
        if (!active) return;
        const bills = data.bills || [];
        setAllocations((prev) => {
          const rows = {};
          bills.forEach((b) => {
            const key = `${b.bill_type}:${b.bill_id ?? ''}`;
            rows[key] = {
              key,
              bill_type: b.bill_type,
              bill_id: b.bill_id ?? null,
              bill_name: b.bill_name,
              balance: b.balance,
              amount: prev[key]?.amount ?? '',
            };
          });
          // Keep allocations that no longer appear as outstanding (already
          // settled) so an edit never silently drops them.
          Object.values(prev).forEach((row) => {
            if (!rows[row.key]) rows[row.key] = row;
          });
          if (!rows['advance:']) {
            rows['advance:'] = {
              key: 'advance:',
              bill_type: 'advance',
              bill_id: null,
              bill_name: 'On Account / Advance',
              balance: null,
              amount: '',
            };
          }
          return rows;
        });
      } catch {
        if (active) setAllocations((prev) => prev);
      } finally {
        if (active) setLoadingBills(false);
      }
    })();

    return () => { active = false; };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [partyVoucher, partyLedgerId]);

  /* ------------------------------ ledgers ------------------------------- */

  const ledgers = ledgersStore.items;
  const partyOptionType = partyTypeForVoucher(type);
  const partyLedgers = useMemo(
    () => ledgers.filter((l) => l.party_type === partyOptionType),
    [ledgers, partyOptionType]
  );
  const bankCashLedgers = useMemo(
    () => ledgers.filter((l) => l.is_bank || l.is_cash),
    [ledgers]
  );
  // Contra moves money between cash and bank accounts only; a Journal may use
  // any ledger in the chart of accounts.
  const gridLedgers = type === 'contra' ? bankCashLedgers : ledgers;

  /* ------------------------------- totals ------------------------------- */

  const gridTotals = useMemo(() => {
    const dr = round2(entries.filter((e) => e.entry_type === 'Dr').reduce((s, e) => s + (Number(e.amount) || 0), 0));
    const cr = round2(entries.filter((e) => e.entry_type === 'Cr').reduce((s, e) => s + (Number(e.amount) || 0), 0));
    return { dr, cr, diff: round2(dr - cr), balanced: Math.abs(dr - cr) < 0.005 && dr > 0 };
  }, [entries]);

  const allocatedTotal = useMemo(
    () => round2(Object.values(allocations).reduce((s, row) => s + (Number(row.amount) || 0), 0)),
    [allocations]
  );
  const remainder = round2((Number(amount) || 0) - allocatedTotal);

  /* ------------------------------ handlers ------------------------------ */

  const updateEntry = (index, patch) => {
    setEntries((prev) => prev.map((row, i) => (i === index ? { ...row, ...patch } : row)));
  };
  const addEntry = () => setEntries((prev) => [...prev, newRow(prev.filter((r) => r.entry_type === 'Dr').length <= prev.filter((r) => r.entry_type === 'Cr').length ? 'Dr' : 'Cr')]);
  const removeEntry = (index) => setEntries((prev) => (prev.length <= 2 ? prev : prev.filter((_, i) => i !== index)));

  const updateAllocation = (key, value) => {
    setAllocations((prev) => ({ ...prev, [key]: { ...prev[key], amount: value } }));
  };

  const putRemainderOnAccount = () => {
    if (remainder <= 0) return;
    setAllocations((prev) => ({
      ...prev,
      'advance:': { ...prev['advance:'], amount: String(remainder) },
    }));
  };

  const changeParty = (value) => {
    setPartyLedgerId(value);
    setAllocations({});
  };

  /* ------------------------------- submit ------------------------------- */

  const buildEntries = () => {
    if (partyVoucher) {
      const amt = round2(amount);
      const party = Number(partyLedgerId);
      const bank = Number(bankLedgerId);
      return type === 'payment'
        ? [
            { ledger_id: party, entry_type: 'Dr', amount: amt },
            { ledger_id: bank, entry_type: 'Cr', amount: amt },
          ]
        : [
            { ledger_id: bank, entry_type: 'Dr', amount: amt },
            { ledger_id: party, entry_type: 'Cr', amount: amt },
          ];
    }

    return entries
      .filter((e) => e.ledger_id || Number(e.amount) > 0)
      .map((e) => ({
        ledger_id: Number(e.ledger_id),
        entry_type: e.entry_type,
        amount: round2(e.amount),
        narration: nullable(e.narration),
      }));
  };

  const validate = () => {
    if (!form.voucher_date) return 'Pick a voucher date.';

    if (partyVoucher) {
      if (!partyLedgerId) return 'Choose the party ledger.';
      if (!bankLedgerId) return 'Choose the bank / cash ledger.';
      if (!(Number(amount) > 0)) return 'Enter an amount greater than zero.';
      if (allocatedTotal - Number(amount) >= 0.01) return 'Bill allocations cannot exceed the voucher amount.';
      return null;
    }

    const rows = entries.filter((e) => e.ledger_id || Number(e.amount) > 0);
    if (rows.length < 2) return 'Add at least one debit and one credit line.';
    if (rows.some((e) => !e.ledger_id)) return 'Every line needs a ledger.';
    if (rows.some((e) => !(Number(e.amount) > 0))) return 'Every line needs an amount greater than zero.';
    if (!gridTotals.balanced) return 'The voucher is not balanced: debits must equal credits.';
    return null;
  };

  const submit = async (e) => {
    e.preventDefault();
    if (saving) return;

    const problem = validate();
    if (problem) {
      setError(problem);
      return;
    }

    setError(null);
    setSaving(true);

    const payload = {
      voucher_type: type,
      voucher_date: form.voucher_date,
      reference_no: nullable(form.reference_no),
      narration: nullable(form.narration),
      entries: buildEntries(),
    };

    if (partyVoucher) {
      payload.party_ledger_id = Number(partyLedgerId);
      payload.bill_allocations = Object.values(allocations)
        .filter((row) => Number(row.amount) > 0)
        .map((row) => ({
          bill_type: row.bill_type,
          bill_id: row.bill_id ?? null,
          bill_name: row.bill_name || null,
          amount: round2(row.amount),
        }));
    }

    try {
      if (isEdit) {
        await voucherService.update(id, payload);
      } else {
        await voucherService.create(payload);
      }
      navigate(`/dashboard/accounting/${type}`);
    } catch (err) {
      setError(firstError(err));
      setSaving(false);
    }
  };

  /* -------------------------------- render ------------------------------ */

  if (loading) {
    return <div className="p-6 text-gray-500">Loading voucher…</div>;
  }

  return (
    <div className="p-6 max-w-4xl mx-auto">
      <div className="flex items-center justify-between gap-3 mb-6">
        <div className="flex items-center gap-3">
          <button onClick={() => navigate(`/dashboard/accounting/${type}`)} className="text-gray-500 hover:text-gray-800" title="Back to register">
            <ArrowLeft className="w-5 h-5" />
          </button>
          <div>
            <h1 className="text-2xl font-bold text-gray-900">{isEdit ? 'Edit' : 'New'} {config.label} Voucher</h1>
            <p className="text-sm text-gray-500">
              {partyVoucher
                ? 'Settle a party, and split the amount across the bills it pays.'
                : 'Enter the debit and credit lines — the two sides must agree.'}
            </p>
          </div>
        </div>
      </div>

      {error && <div className="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{error}</div>}

      {ledgers.length === 0 && !ledgersStore.loading && (
        <div className="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 flex items-start gap-2">
          <Info className="w-4 h-4 mt-0.5" />
          <span>No ledgers found. Create ledgers under Chart of Accounts before entering vouchers.</span>
        </div>
      )}

      <form onSubmit={submit} className="space-y-6">
        <div className="bg-white rounded-lg shadow p-5 space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label className={labelClass}>Voucher date *</label>
              <input type="date" required value={form.voucher_date} onChange={(e) => setForm({ ...form, voucher_date: e.target.value })} className={inputClass} />
            </div>
            <div>
              <label className={labelClass}>Reference no.</label>
              <input value={form.reference_no} onChange={(e) => setForm({ ...form, reference_no: e.target.value })} className={inputClass} placeholder="Cheque / instrument no." />
            </div>
            <div>
              <label className={labelClass}>Voucher type</label>
              <input value={config.label} disabled className={`${inputClass} bg-gray-50`} />
            </div>
          </div>
          <div>
            <label className={labelClass}>Narration</label>
            <input value={form.narration} onChange={(e) => setForm({ ...form, narration: e.target.value })} className={inputClass} placeholder="Notes for this voucher" />
          </div>
        </div>

        {partyVoucher ? (
          <div className="bg-white rounded-lg shadow p-5 space-y-5">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label className={labelClass}>Party ledger *</label>
                <select required value={partyLedgerId} onChange={(e) => changeParty(e.target.value)} className={inputClass}>
                  <option value="">— Select {partyOptionType} —</option>
                  {partyLedgers.map((l) => <option key={l.id} value={l.id}>{l.name}</option>)}
                </select>
              </div>
              <div>
                <label className={labelClass}>{type === 'payment' ? 'Paid from (bank / cash) *' : 'Received in (bank / cash) *'}</label>
                <select required value={bankLedgerId} onChange={(e) => setBankLedgerId(e.target.value)} className={inputClass}>
                  <option value="">— Select bank / cash —</option>
                  {bankCashLedgers.map((l) => <option key={l.id} value={l.id}>{l.name}</option>)}
                </select>
              </div>
              <div>
                <label className={labelClass}>Amount *</label>
                <input type="number" step="0.01" min="0" required value={amount} onChange={(e) => setAmount(e.target.value)} className={inputClass} placeholder="0.00" />
              </div>
            </div>

            <div>
              <div className="flex items-center justify-between mb-2">
                <h3 className="text-sm font-semibold text-gray-800">Bill-wise details</h3>
                {loadingBills && <span className="text-xs text-gray-400 inline-flex items-center"><RefreshCw className="w-3 h-3 mr-1 animate-spin" />loading outstanding…</span>}
              </div>

              {!partyLedgerId ? (
                <p className="text-sm text-gray-400">Choose a party to see its outstanding bills.</p>
              ) : (
                <div className="border border-gray-200 rounded-lg overflow-hidden">
                  <table className="min-w-full text-sm">
                    <thead className="bg-gray-50">
                      <tr>
                        {['Bill', 'Balance', 'Allocate'].map((h) => (
                          <th key={h} className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{h}</th>
                        ))}
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                      {Object.values(allocations).map((row) => (
                        <tr key={row.key}>
                          <td className="px-3 py-2 text-gray-800">{row.bill_name}</td>
                          <td className="px-3 py-2 text-gray-600">{row.balance === null || row.balance === undefined ? '—' : formatMoney(row.balance)}</td>
                          <td className="px-3 py-2 w-40">
                            <input type="number" step="0.01" min="0" value={row.amount} onChange={(e) => updateAllocation(row.key, e.target.value)} className={inputClass} placeholder="0.00" />
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}

              <div className="flex flex-wrap items-center justify-between gap-2 mt-3 text-sm">
                <span className="text-gray-600">Allocated {formatMoney(allocatedTotal)} of {formatMoney(amount || 0)}</span>
                <div className="flex items-center gap-3">
                  <span className={remainder < -0.005 ? 'text-red-600 font-medium' : 'text-gray-600'}>
                    Unallocated {formatMoney(Math.max(remainder, 0))}
                  </span>
                  {remainder > 0.005 && (
                    <button type="button" onClick={putRemainderOnAccount} className="text-orange-600 hover:text-orange-700 font-medium">
                      Put on account
                    </button>
                  )}
                </div>
              </div>
            </div>
          </div>
        ) : (
          <div className="bg-white rounded-lg shadow p-5">
            <div className="overflow-x-auto">
              <table className="min-w-full text-sm">
                <thead>
                  <tr className="text-xs text-gray-500 uppercase tracking-wider">
                    <th className="px-2 py-2 text-left">Ledger</th>
                    <th className="px-2 py-2 text-left w-28">Dr / Cr</th>
                    <th className="px-2 py-2 text-left w-36">Amount</th>
                    <th className="px-2 py-2 text-left">Narration</th>
                    <th className="px-2 py-2 w-10"></th>
                  </tr>
                </thead>
                <tbody>
                  {entries.map((row, index) => (
                    <tr key={index}>
                      <td className="px-2 py-2">
                        <select value={row.ledger_id} onChange={(e) => updateEntry(index, { ledger_id: e.target.value })} className={inputClass}>
                          <option value="">— Select ledger —</option>
                          {gridLedgers.map((l) => <option key={l.id} value={l.id}>{l.name}</option>)}
                        </select>
                      </td>
                      <td className="px-2 py-2">
                        <select value={row.entry_type} onChange={(e) => updateEntry(index, { entry_type: e.target.value })} className={inputClass}>
                          <option value="Dr">Dr</option>
                          <option value="Cr">Cr</option>
                        </select>
                      </td>
                      <td className="px-2 py-2">
                        <input type="number" step="0.01" min="0" value={row.amount} onChange={(e) => updateEntry(index, { amount: e.target.value })} className={inputClass} placeholder="0.00" />
                      </td>
                      <td className="px-2 py-2">
                        <input value={row.narration} onChange={(e) => updateEntry(index, { narration: e.target.value })} className={inputClass} placeholder="Optional" />
                      </td>
                      <td className="px-2 py-2 text-center">
                        <button type="button" onClick={() => removeEntry(index)} disabled={entries.length <= 2} className="text-red-500 hover:text-red-700 disabled:opacity-30" title="Remove line">
                          <Trash2 className="w-4 h-4" />
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            <button type="button" onClick={addEntry} className="mt-3 inline-flex items-center text-sm text-orange-600 hover:text-orange-700 font-medium">
              <Plus className="w-4 h-4 mr-1" /> Add line
            </button>

            <div className="flex flex-wrap items-center justify-end gap-6 mt-4 pt-4 border-t border-gray-100 text-sm">
              <span className="text-gray-600">Dr {formatMoney(gridTotals.dr)}</span>
              <span className="text-gray-600">Cr {formatMoney(gridTotals.cr)}</span>
              <span className={gridTotals.balanced ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'}>
                {gridTotals.balanced ? 'Balanced' : `Difference ${formatMoney(Math.abs(gridTotals.diff))}`}
              </span>
            </div>
          </div>
        )}

        <div className="flex justify-end gap-2">
          <button type="button" onClick={() => navigate(`/dashboard/accounting/${type}`)} className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
            Cancel
          </button>
          <button type="submit" disabled={saving} className="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg inline-flex items-center disabled:opacity-60">
            <Save className="w-4 h-4 mr-2" /> {saving ? 'Saving…' : 'Save Voucher'}
          </button>
        </div>
      </form>
    </div>
  );
};

export default VoucherFormPage;
