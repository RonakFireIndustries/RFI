import { useState, useEffect, useCallback, useMemo } from 'react';
import api from '../../services/api';
import { buildingService } from '../../services/buildingService';
import { productsService } from '../../services/productsService';
import {
  Plus, Search, Edit3, Trash2, FileDown, ArrowLeft, FileText, Building2, Loader2,
  Eye, CheckCircle2, XCircle, Clock, Send, Layers, ChevronDown, ChevronUp,
} from 'lucide-react';

const STATUS_META = {
  draft: { label: 'Draft', color: 'bg-gray-100 text-gray-700', icon: Clock },
  sent: { label: 'Sent', color: 'bg-blue-100 text-blue-700', icon: Send },
  accepted: { label: 'Accepted', color: 'bg-green-100 text-green-700', icon: CheckCircle2 },
  rejected: { label: 'Rejected', color: 'bg-red-100 text-red-700', icon: XCircle },
};

const fm = (n) =>
  new Intl.NumberFormat('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(n || 0));

const emptyItem = () => ({ id: null, product_id: null, description: '', unit: '', qty: '', rate: '' });
const emptySection = () => ({ key: Date.now() + Math.random(), id: null, name: 'Ground Floor', items: [emptyItem()] });

let uid = 0;
const nextKey = () => `s${Date.now()}_${uid++}`;

export default function QuotationsPage() {
  const [mode, setMode] = useState('list'); // list | form | detail
  const [quotations, setQuotations] = useState([]);
  const [pagination, setPagination] = useState(null);
  const [detail, setDetail] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [downloadKey, setDownloadKey] = useState(null);
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');

  // form state
  const [buildings, setBuildings] = useState([]);
  const [products, setProducts] = useState([]);
  const [form, setForm] = useState({
    building_id: '', building_name: '', quotation_date: new Date().toISOString().slice(0, 10),
    status: 'draft', discount: '', gst_percent: '', terms: '', notes: '',
  });
  const [sections, setSections] = useState([emptySection()]);
  const [editingId, setEditingId] = useState(null);
  const [buildingSearch, setBuildingSearch] = useState('');
  const [showBuildingList, setShowBuildingList] = useState(false);
  const [productSearch, setProductSearch] = useState('');
  const [activeSearch, setActiveSearch] = useState(null); // { section: idx, item: idx }
  const [expanded, setExpanded] = useState(null);
  const [collapsedDetailSections, setCollapsedDetailSections] = useState({});

  const fetchList = useCallback(async () => {
    setLoading(true);
    setError('');
    try {
      const res = await api.get('/quotations', {
        params: { search: search || undefined, status: statusFilter || undefined },
      });
      setQuotations(res.data?.quotations || []);
      setPagination(res.data?.pagination || null);
    } catch (err) {
      setError(err?.response?.data?.message || 'Could not load quotations.');
    } finally {
      setLoading(false);
    }
  }, [search, statusFilter]);

  useEffect(() => { fetchList(); }, [fetchList]);

  const loadFormOptions = useCallback(async () => {
    try {
      const [b, p] = await Promise.all([
        buildingService.list(),
        productsService.list({ per_page: 500 }),
      ]);
      setBuildings(b || []);
      setProducts(p || []);
    } catch (e) {
      // non-fatal
    }
  }, []);

  const openCreate = async () => {
    setEditingId(null);
    setForm({
      building_id: '', building_name: '', quotation_date: new Date().toISOString().slice(0, 10),
      status: 'draft', discount: '', gst_percent: '', terms: '', notes: '',
    });
    setSections([emptySection()]);
    setBuildingSearch('');
    setProductSearch('');
    setActiveSearch(null);
    await loadFormOptions();
    setMode('form');
  };

  const openEdit = async (q) => {
    setEditingId(q.id);
    setForm({
      building_id: q.building_id || '',
      building_name: q.building_name || '',
      quotation_date: q.quotation_date || new Date().toISOString().slice(0, 10),
      status: q.status || 'draft',
      discount: q.discount ?? '',
      gst_percent: q.gst_percent ?? '',
      terms: q.terms || '',
      notes: q.notes || '',
    });
    setSections(
      (q.sections || []).map((s) => ({
        key: nextKey(),
        id: s.id ?? null,
        name: s.name || '',
        items: (s.items || []).map((i) => ({
          id: i.id, product_id: i.product_id || '', description: i.description || '',
          unit: i.unit || '', qty: i.qty, rate: i.rate,
        })),
      })),
    );
    setBuildingSearch(q.display_building_name || '');
    setProductSearch('');
    setActiveSearch(null);
    await loadFormOptions();
    setMode('form');
  };

  const openDetail = async (id) => {
    setLoading(true);
    setError('');
    try {
      const res = await api.get(`/quotations/${id}`);
      setDetail(res.data?.quotation || null);
      setCollapsedDetailSections({});
      setMode('detail');
    } catch (err) {
      setError(err?.response?.data?.message || 'Could not load quotation.');
    } finally {
      setLoading(false);
    }
  };

  // ---- section helpers ----
  const addSection = () => setSections((prev) => [...prev, emptySection()]);
  const updateSectionName = (si, v) =>
    setSections((prev) => prev.map((s, i) => (i === si ? { ...s, name: v } : s)));
  const removeSection = (si) =>
    setSections((prev) => (prev.length > 1 ? prev.filter((_, i) => i !== si) : prev));

  const updateSectionItem = (si, ii, key, value) =>
    setSections((prev) => prev.map((s, i) =>
      i !== si ? s : { ...s, items: s.items.map((it, j) => (j === ii ? { ...it, [key]: value } : it)) }));

  const addItemToSection = (si) =>
    setSections((prev) => prev.map((s, i) => (i === si ? { ...s, items: [...s.items, emptyItem()] } : s)));

  const removeItemFromSection = (si, ii) =>
    setSections((prev) => prev.map((s, i) =>
      i !== si ? s : { ...s, items: s.items.length > 1 ? s.items.filter((_, j) => j !== ii) : s.items }));

  const selectProduct = (si, ii, p) => {
    updateSectionItem(si, ii, 'product_id', String(p.id));
    updateSectionItem(si, ii, 'description', p.name || '');
    setActiveSearch(null);
  };

  // ---- totals ----
  const lineAmount = (r) => Number(r.qty || 0) * Number(r.rate || 0);
  const sectionSubtotal = (s) => s.items.reduce((sum, r) => sum + lineAmount(r), 0);
  const subtotal = sections.reduce((sum, s) => sum + sectionSubtotal(s), 0);
  const discount = Number(form.discount || 0);
  const gstPct = Number(form.gst_percent || 0);
  const afterDiscount = subtotal - discount;
  const grandTotal = afterDiscount + (afterDiscount * gstPct) / 100;

  // ---- building helpers ----
  const selectedBuilding = useMemo(
    () => buildings.find((b) => b.id === Number(form.building_id)),
    [buildings, form.building_id],
  );

  const filteredBuildings = useMemo(() => {
    const t = buildingSearch.trim().toLowerCase();
    const list = t ? buildings.filter((b) => (b.name || '').toLowerCase().includes(t)) : buildings;
    return list.slice(0, 15);
  }, [buildings, buildingSearch]);

  const selectBuilding = (b) => {
    setForm((f) => ({ ...f, building_id: String(b.id), building_name: '' }));
    setBuildingSearch(b.name || '');
    setShowBuildingList(false);
  };

  const clearBuilding = () => {
    setForm((f) => ({ ...f, building_id: '', building_name: '' }));
    setBuildingSearch('');
  };

  const handleSave = async (e) => {
    e.preventDefault();
    setSaving(true);
    setError('');
    setNotice('');
    try {
      const payload = {
        ...form,
        building_id: form.building_id ? Number(form.building_id) : null,
        discount: form.discount === '' ? null : Number(form.discount),
        gst_percent: form.gst_percent === '' ? null : Number(form.gst_percent),
        sections: sections.map((s) => ({
          id: s.id || undefined,
          name: s.name,
          items: s.items.map((r) => ({
            id: r.id || undefined,
            product_id: r.product_id ? Number(r.product_id) : null,
            description: r.description,
            unit: r.unit,
            qty: Number(r.qty),
            rate: Number(r.rate),
          })),
        })),
      };
      if (editingId) {
        await api.put(`/quotations/${editingId}`, payload);
        setNotice('Quotation updated.');
      } else {
        const res = await api.post('/quotations', payload);
        setNotice(`Quotation ${res.data?.quotation?.quotation_no || ''} created.`);
      }
      await fetchList();
      setMode('list');
    } catch (err) {
      setError(err?.response?.data?.message || 'Could not save quotation.');
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async (q) => {
    if (!window.confirm(`Delete ${q.quotation_no}? This cannot be undone.`)) return;
    try {
      await api.delete(`/quotations/${q.id}`);
      await fetchList();
    } catch (err) {
      setError(err?.response?.data?.message || 'Could not delete quotation.');
    }
  };

  const downloadPdf = async (q) => {
    setDownloadKey(q.id);
    try {
      const blob = await api.getBlob(`/quotations/${q.id}/pdf`);
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `BOQ_${q.quotation_no}.pdf`);
      document.body.appendChild(link);
      link.click();
      link.parentNode.removeChild(link);
      window.URL.revokeObjectURL(url);
    } catch (err) {
      setError('Failed to download PDF.');
    } finally {
      setDownloadKey(null);
    }
  };

  const gotoForm = (id) => {
    const q = quotations.find((x) => x.id === id);
    if (q) openEdit(q);
  };

  // ---------------- RENDER ----------------
  if (mode === 'detail' && detail) {
    return (
      <div className="p-4 md:p-6 max-w-4xl mx-auto">
        <div className="flex items-center justify-between mb-5">
          <button
            onClick={() => { setMode('list'); setDetail(null); }}
            className="inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-gray-900"
          >
            <ArrowLeft className="w-4 h-4" /> Back
          </button>
          <div className="flex gap-2">
            <button
              onClick={() => gotoForm(detail.id)}
              className="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50"
            >
              <Edit3 className="w-4 h-4" /> Edit
            </button>
            <button
              onClick={() => downloadPdf(detail)}
              disabled={downloadKey === detail.id}
              className="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg disabled:opacity-50"
            >
              {downloadKey === detail.id ? <Loader2 className="w-4 h-4 animate-spin" /> : <FileDown className="w-4 h-4" />}
              Download PDF
            </button>
          </div>
        </div>

        <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div className="border-b border-gray-100 px-6 py-5 flex items-start justify-between">
            <div>
              <p className="text-xs font-semibold text-gray-400 uppercase tracking-wide">Bill of Quantities</p>
              <h1 className="text-2xl font-bold text-gray-900 mt-1">{detail.quotation_no}</h1>
            </div>
            <span className={`inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold ${STATUS_META[detail.status]?.color || 'bg-gray-100 text-gray-700'}`}>
              {STATUS_META[detail.status]?.label || detail.status}
            </span>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 px-6 py-5 text-sm">
            <div>
              <p className="text-gray-400 text-xs font-medium uppercase">Building / Site</p>
              <p className="font-medium text-gray-800 mt-0.5 flex items-center gap-2">
                <Building2 className="w-4 h-4 text-gray-400" /> {detail.display_building_name || '—'}
              </p>
            </div>
            <div>
              <p className="text-gray-400 text-xs font-medium uppercase">Quotation Date</p>
              <p className="font-medium text-gray-800 mt-0.5">{detail.quotation_date}</p>
            </div>
            {detail.created_by && (
              <div>
                <p className="text-gray-400 text-xs font-medium uppercase">Prepared By</p>
                <p className="font-medium text-gray-800 mt-0.5">{detail.created_by}</p>
              </div>
            )}
            <div>
              <p className="text-gray-400 text-xs font-medium uppercase">{detail.sections?.length || 0} Section(s)</p>
              <p className="font-medium text-gray-800 mt-0.5">{detail.item_count || 0} item(s)</p>
            </div>
          </div>

          {(detail.sections || []).map((section, si) => {
            const isCollapsed = collapsedDetailSections[si];
            return (
              <div key={section.id ?? si} className="border-t border-gray-100">
                <div className="px-6 py-3.5 bg-indigo-50/50 flex items-center justify-between">
                  <button
                    type="button"
                    onClick={() => setCollapsedDetailSections((c) => ({ ...c, [si]: !c[si] }))}
                    className="inline-flex items-center gap-2 font-semibold text-indigo-800 uppercase tracking-wide text-xs"
                  >
                    <Layers className="w-4 h-4 text-indigo-500" />
                    {section.name || 'Section'}
                    {isCollapsed ? <ChevronDown className="w-4 h-4 text-indigo-400" /> : <ChevronUp className="w-4 h-4 text-indigo-400" />}
                  </button>
                  <span className="text-sm font-semibold text-gray-800">{fm(section.subtotal)}</span>
                </div>
                {!isCollapsed && (
                  <div className="overflow-x-auto">
                    <table className="w-full text-sm">
                      <thead>
                        <tr className="border-b border-gray-100 text-left text-xs font-bold text-gray-500 uppercase bg-gray-50">
                          <th className="py-3 px-6">#</th>
                          <th className="py-3 px-4">Description</th>
                          <th className="py-3 px-4">Unit</th>
                          <th className="py-3 px-4 text-right">Qty</th>
                          <th className="py-3 px-4 text-right">Rate</th>
                          <th className="py-3 px-6 text-right">Amount</th>
                        </tr>
                      </thead>
                      <tbody>
                        {(section.items || []).map((it, i) => (
                          <tr key={it.id ?? i} className="border-b border-gray-50">
                            <td className="py-3 px-6 text-gray-400">{i + 1}</td>
                            <td className="py-3 px-4 text-gray-800">{it.description || '—'}</td>
                            <td className="py-3 px-4 text-gray-500">{it.unit || '—'}</td>
                            <td className="py-3 px-4 text-right text-gray-700">{Number(it.qty)}</td>
                            <td className="py-3 px-4 text-right text-gray-700">{fm(it.rate)}</td>
                            <td className="py-3 px-6 text-right font-medium text-gray-800">{fm(it.amount)}</td>
                          </tr>
                        ))}
                        <tr className="bg-indigo-50/40">
                          <td colSpan="5" className="py-2 px-6 text-right text-xs font-bold text-gray-500 uppercase">
                            {section.name || 'Section'} Subtotal
                          </td>
                          <td className="py-2 px-6 text-right font-semibold text-gray-800">{fm(section.subtotal)}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                )}
              </div>
            );
          })}

          <div className="px-6 py-5 flex justify-end">
            <div className="w-72 space-y-1.5 text-sm">
              <div className="flex justify-between text-gray-600"><span>Subtotal</span><span>{fm(detail.subtotal)}</span></div>
              {Number(detail.discount) > 0 && (
                <div className="flex justify-between text-gray-600"><span>Discount</span><span>- {fm(detail.discount)}</span></div>
              )}
              {Number(detail.gst_percent) > 0 && (
                <div className="flex justify-between text-gray-600"><span>GST ({Number(detail.gst_percent)}%)</span><span>{fm(detail.grand_total - (detail.subtotal - Number(detail.discount || 0)))}</span></div>
              )}
              <div className="flex justify-between font-bold text-gray-900 text-lg border-t border-gray-200 pt-2">
                <span>Grand Total</span><span>{fm(detail.grand_total)}</span>
              </div>
            </div>
          </div>

          {detail.terms && (
            <div className="px-6 py-4 border-t border-gray-100">
              <p className="text-xs font-bold text-gray-400 uppercase mb-1">Terms &amp; Conditions</p>
              <p className="text-sm text-gray-600 whitespace-pre-wrap">{detail.terms}</p>
            </div>
          )}
        </div>
      </div>
    );
  }

  if (mode === 'form') {
    return (
      <div className="p-4 md:p-6 max-w-5xl mx-auto">
        <div className="flex items-center gap-3 mb-5">
          <button
            onClick={() => { setMode('list'); setEditingId(null); }}
            className="inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-gray-900"
          >
            <ArrowLeft className="w-4 h-4" /> Back
          </button>
          <h1 className="text-2xl font-bold text-gray-900">
            {editingId ? 'Edit Quotation / BOQ' : 'New Quotation / BOQ'}
          </h1>
        </div>

        {error && <div className="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">{error}</div>}
        {notice && <div className="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">{notice}</div>}

        <form onSubmit={handleSave} className="space-y-6">
          {/* Header card */}
          <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 className="text-sm font-semibold text-gray-800 mb-4">Quotation Details</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div className="lg:col-span-1 relative">
                <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Building / Site</label>
                <div className="flex gap-2">
                  <input
                    type="text"
                    value={buildingSearch}
                    onFocus={() => setShowBuildingList(true)}
                    onChange={(e) => { setBuildingSearch(e.target.value); setShowBuildingList(true); }}
                    placeholder="Type to search buildings..."
                    className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                  {(form.building_id || form.building_name) && (
                    <button type="button" onClick={clearBuilding} className="shrink-0 px-2 text-gray-400 hover:text-gray-600">✕</button>
                  )}
                </div>
                {showBuildingList && (
                  <>
                    <div className="absolute inset-0 z-0" onClick={() => setShowBuildingList(false)} />
                    <div className="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-56 overflow-y-auto">
                      {filteredBuildings.length === 0 ? (
                        <button
                          type="button"
                          onClick={() => { setForm((f) => ({ ...f, building_name: buildingSearch.trim() })); setShowBuildingList(false); }}
                          className="w-full text-left px-3 py-2 text-sm text-blue-600 hover:bg-blue-50"
                        >
                          + Use &quot;{buildingSearch.trim()}&quot; as custom name
                        </button>
                      ) : (
                        filteredBuildings.map((b) => (
                          <button
                            key={b.id}
                            type="button"
                            onClick={() => selectBuilding(b)}
                            className="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 text-gray-800"
                          >
                            {b.name}
                          </button>
                        ))
                      )}
                    </div>
                  </>
                )}
                {form.building_name && !form.building_id && (
                  <p className="text-xs text-blue-600 mt-1">Using custom building: {form.building_name}</p>
                )}
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Quotation Date</label>
                <input
                  type="date"
                  value={form.quotation_date}
                  onChange={(e) => setForm((f) => ({ ...f, quotation_date: e.target.value }))}
                  className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Status</label>
                <select
                  value={form.status}
                  onChange={(e) => setForm((f) => ({ ...f, status: e.target.value }))}
                  className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="draft">Draft</option>
                  <option value="sent">Sent</option>
                  <option value="accepted">Accepted</option>
                  <option value="rejected">Rejected</option>
                </select>
              </div>
            </div>
          </div>

          {/* Sections */}
          <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-sm font-semibold text-gray-800">BOQ Sections</h3>
              <button type="button" onClick={addSection} className="inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:text-blue-700">
                <Plus className="w-4 h-4" /> Add Section (Floor / Shop / Etc.)
              </button>
            </div>

            <div className="space-y-4">
              {sections.map((section, si) => (
                <div key={section.key} className="rounded-xl border border-indigo-100 overflow-hidden">
                  <div className="flex items-center gap-2 px-3 py-2.5 bg-indigo-50/60">
                    <Layers className="w-4 h-4 text-indigo-500 shrink-0" />
                    <input
                      type="text"
                      value={section.name}
                      onChange={(e) => updateSectionName(si, e.target.value)}
                      placeholder="Section name (e.g. Ground Floor, Shops, Parking...)"
                      className="flex-1 px-3 py-1.5 border border-indigo-200 rounded-lg text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                    />
                    <button
                      type="button"
                      onClick={() => removeSection(si)}
                      disabled={sections.length <= 1}
                      className="shrink-0 p-1.5 text-gray-400 hover:text-red-500 disabled:opacity-40"
                      title="Remove section"
                    >
                      <Trash2 className="w-4 h-4" />
                    </button>
                  </div>

                  <div className="p-3 space-y-3">
                    {section.items.map((row, ii) => {
                      const isActive = activeSearch?.section === si && activeSearch?.item === ii;
                      return (
                        <div key={ii} className="grid grid-cols-1 md:grid-cols-12 gap-3 items-end bg-gray-50/60 p-3 rounded-lg border border-gray-100">
                          <div className="md:col-span-5 relative">
                            <label className="block text-[10px] font-bold text-gray-500 uppercase mb-1">Item / Description</label>
                            <input
                              type="text"
                              value={row.description}
                              onFocus={() => { setActiveSearch({ section: si, item: ii }); setProductSearch(''); }}
                              onChange={(e) => { updateSectionItem(si, ii, 'description', e.target.value); setActiveSearch({ section: si, item: ii }); }}
                              placeholder="Search product or type custom..."
                              className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            {isActive && (
                              <>
                                <div className="absolute inset-0 z-0" onClick={() => setActiveSearch(null)} />
                                <div className="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                  {products
                                    .filter((p) => !productSearch || (p.name || '').toLowerCase().includes(productSearch.toLowerCase()))
                                    .slice(0, 15)
                                    .map((p) => (
                                      <button
                                        key={p.id}
                                        type="button"
                                        onClick={() => selectProduct(si, ii, p)}
                                        className="w-full text-left px-3 py-2 text-sm hover:bg-blue-50 text-gray-800"
                                      >
                                        {p.name}
                                      </button>
                                    ))}
                                </div>
                              </>
                            )}
                          </div>
                          <div className="md:col-span-2">
                            <label className="block text-[10px] font-bold text-gray-500 uppercase mb-1">Unit</label>
                            <input type="text" value={row.unit} onChange={(e) => updateSectionItem(si, ii, 'unit', e.target.value)} placeholder="Nos" className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                          </div>
                          <div className="md:col-span-2">
                            <label className="block text-[10px] font-bold text-gray-500 uppercase mb-1">Qty</label>
                            <input type="number" min="0" step="any" value={row.qty} onChange={(e) => updateSectionItem(si, ii, 'qty', e.target.value)} className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                          </div>
                          <div className="md:col-span-2">
                            <label className="block text-[10px] font-bold text-gray-500 uppercase mb-1">Rate</label>
                            <input type="number" min="0" step="any" value={row.rate} onChange={(e) => updateSectionItem(si, ii, 'rate', e.target.value)} className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                          </div>
                          <div className="md:col-span-1 text-right">
                            <div className="text-sm font-semibold text-gray-800">{fm(lineAmount(row))}</div>
                            <button type="button" onClick={() => removeItemFromSection(si, ii)} disabled={section.items.length <= 1} className="mt-1 text-gray-400 hover:text-red-500 disabled:opacity-40">
                              <Trash2 className="w-4 h-4" />
                            </button>
                          </div>
                        </div>
                      );
                    })}
                    <div className="flex items-center justify-between">
                      <button type="button" onClick={() => addItemToSection(si)} className="inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:text-blue-700">
                        <Plus className="w-4 h-4" /> Add Item
                      </button>
                      <span className="text-sm font-semibold text-gray-700">
                        {section.name || 'Section'} subtotal: <span className="text-gray-900">{fm(sectionSubtotal(section))}</span>
                      </span>
                    </div>
                  </div>
                </div>
              ))}
            </div>

            <div className="flex justify-end mt-4">
              <div className="w-72 space-y-1.5 text-sm">
                <div className="flex justify-between text-gray-600"><span>Subtotal</span><span>{fm(subtotal)}</span></div>
                <div className="flex justify-between items-center text-gray-600">
                  <span>Discount</span>
                  <input type="number" min="0" step="any" value={form.discount}
                    onChange={(e) => setForm((f) => ({ ...f, discount: e.target.value }))}
                    className="w-28 text-right px-2 py-1 border border-gray-200 rounded-md text-sm" />
                </div>
                <div className="flex justify-between items-center text-gray-600">
                  <span>GST (%)</span>
                  <input type="number" min="0" max="100" step="any" value={form.gst_percent}
                    onChange={(e) => setForm((f) => ({ ...f, gst_percent: e.target.value }))}
                    className="w-28 text-right px-2 py-1 border border-gray-200 rounded-md text-sm" />
                </div>
                <div className="flex justify-between font-bold text-gray-900 text-base border-t border-gray-200 pt-2">
                  <span>Grand Total</span><span>{fm(grandTotal)}</span>
                </div>
              </div>
            </div>
          </div>

          {/* Terms */}
          <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Terms &amp; Notes</label>
            <textarea
              value={form.terms}
              onChange={(e) => setForm((f) => ({ ...f, terms: e.target.value }))}
              rows="3"
              placeholder="Terms &amp; conditions for this quotation..."
              className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div className="flex justify-end gap-3">
            <button
              type="button"
              onClick={() => { setMode('list'); setEditingId(null); }}
              className="px-5 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={saving}
              className="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 disabled:opacity-50"
            >
              {saving ? 'Saving...' : editingId ? 'Update Quotation' : 'Create Quotation'}
            </button>
          </div>
        </form>
      </div>
    );
  }

  // ---------------- LIST ----------------
  return (
    <div className="p-4 md:p-6 max-w-6xl mx-auto">
      <div className="flex items-center justify-between mb-5 gap-3 flex-wrap">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Quotations / BOQ</h1>
          <p className="text-gray-500 mt-1 text-sm">Prepare Bills of Quantities for clients.</p>
        </div>
        <button
          onClick={() => setExpanded((x) => !x)}
          className="inline-flex items-center gap-1 px-4 py-2 border border-gray-200 text-sm text-gray-700 rounded-lg hover:bg-gray-50 md:hidden"
        >
          <Search className="w-4 h-4" /> Filters
        </button>
        <button
          onClick={openCreate}
          className="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700"
        >
          <Plus className="w-4 h-4" /> New Quotation
        </button>
      </div>

      <div className={`bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-4 gap-3 ${expanded ? 'block' : 'hidden'} md:flex`}>
        <div className="relative flex-1">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input
            type="text"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Search quotation no or building..."
            className="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <select
          value={statusFilter}
          onChange={(e) => setStatusFilter(e.target.value)}
          className="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 md:w-44"
        >
          <option value="">All statuses</option>
          <option value="draft">Draft</option>
          <option value="sent">Sent</option>
          <option value="accepted">Accepted</option>
          <option value="rejected">Rejected</option>
        </select>
      </div>

      {error && <div className="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">{error}</div>}

      {loading ? (
        <div className="flex items-center justify-center py-20">
          <Loader2 className="w-8 h-8 animate-spin text-blue-600" />
        </div>
      ) : quotations.length === 0 ? (
        <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
          <FileText className="w-12 h-12 text-gray-300 mx-auto mb-3" />
          <h3 className="text-lg font-semibold text-gray-700">No quotations yet</h3>
          <p className="text-gray-400 mt-1">Create your first BOQ for a building.</p>
        </div>
      ) : (
        <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="border-b border-gray-100 text-left text-xs font-bold text-gray-500 uppercase bg-gray-50">
                  <th className="py-3 px-4">Quotation No</th>
                  <th className="py-3 px-4">Building / Site</th>
                  <th className="py-3 px-4">Date</th>
                  <th className="py-3 px-4">Status</th>
                  <th className="py-3 px-4 text-right">Sections</th>
                  <th className="py-3 px-4 text-right">Items</th>
                  <th className="py-3 px-4 text-right">Grand Total</th>
                  <th className="py-3 px-4 text-right">Action</th>
                </tr>
              </thead>
              <tbody>
                {quotations.map((q) => {
                  const st = STATUS_META[q.status] || STATUS_META.draft;
                  const StatIcon = st.icon;
                  return (
                    <tr key={q.id} className="border-b border-gray-50 hover:bg-gray-50/50">
                      <td className="py-3 px-4 font-semibold text-blue-700">{q.quotation_no}</td>
                      <td className="py-3 px-4 text-gray-700">
                        <span className="inline-flex items-center gap-2">
                          <Building2 className="w-4 h-4 text-gray-400" /> {q.display_building_name || '—'}
                        </span>
                      </td>
                      <td className="py-3 px-4 text-gray-600">{q.quotation_date}</td>
                      <td className="py-3 px-4">
                        <span className={`inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium ${st.color}`}>
                          <StatIcon className="w-3.5 h-3.5" /> {st.label}
                        </span>
                      </td>
                      <td className="py-3 px-4 text-right text-gray-600">{q.sections?.length || 0}</td>
                      <td className="py-3 px-4 text-right text-gray-600">{q.item_count || 0}</td>
                      <td className="py-3 px-4 text-right font-semibold text-gray-800">{fm(q.grand_total)}</td>
                      <td className="py-3 px-4">
                        <div className="flex items-center justify-end gap-1">
                          <button
                            onClick={() => openDetail(q.id)}
                            title="View"
                            className="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg"
                          >
                            <Eye className="w-4 h-4" />
                          </button>
                          <button
                            onClick={() => gotoForm(q.id)}
                            title="Edit"
                            className="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg"
                          >
                            <Edit3 className="w-4 h-4" />
                          </button>
                          <button
                            onClick={() => downloadPdf(q)}
                            title="Download PDF"
                            className="p-2 text-gray-500 hover:text-green-600 hover:bg-green-50 rounded-lg"
                          >
                            {downloadKey === q.id ? <Loader2 className="w-4 h-4 animate-spin" /> : <FileDown className="w-4 h-4" />}
                          </button>
                          <button
                            onClick={() => handleDelete(q)}
                            title="Delete"
                            className="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg"
                          >
                            <Trash2 className="w-4 h-4" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
          {pagination && (
            <div className="px-4 py-3 border-t border-gray-100 text-sm text-gray-500">
              Page {pagination.current_page} of {pagination.last_page} · {pagination.total} quotation(s)
            </div>
          )}
        </div>
      )}
    </div>
  );
}
