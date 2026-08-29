import React, { useEffect, useState, useMemo } from 'react';
import {
  Plus, Trash2, Search, Flame, Building2, CalendarDays, CalendarClock,
  Info, X, AlertTriangle, CheckCircle2, ShieldAlert,
} from 'lucide-react';
import api from '../../services/api';

const iso = (d) => {
  if (!d) return '';
  if (typeof d === 'string' && /^\d{4}-\d{2}-\d{2}/.test(d)) return d.slice(0, 10);
  return '';
};

const isValidDate = (d) => d && !isNaN(new Date(d).getTime());

const EXTINGUISHER_TYPES = [
  'ABC',
  'CO2',
  'AFFF',
  'Water type',
  'DCP - Inside Cartridge',
  'Modular',
  'Clean agent',
  'Trolley type with inside cartridge - ABC',
  'Trolley type with inside cartridge - CO2',
  'Trolley type with inside cartridge - AFFF',
  'Trolley type with inside cartridge - Water type',
  'Trolley type with outside cartridge - ABC',
  'Trolley type with outside cartridge - CO2',
  'Trolley type with outside cartridge - AFFF',
  'Trolley type with outside cartridge - Water type',
];

export default function FireExtinguishers() {
  const [extinguishers, setExtinguishers] = useState([]);
  const [loading, setLoading] = useState(false);
  const [search, setSearch] = useState('');
  const [dueOnly, setDueOnly] = useState(false);

  // Add flow
  const [addOpen, setAddOpen] = useState(false);
  const [formError, setFormError] = useState('');
  const [buildingName, setBuildingName] = useState('');
  const [count, setCount] = useState('');
  const [rows, setRows] = useState([]);

  useEffect(() => {
    loadExtinguishers();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [dueOnly]);

  const loadExtinguishers = async () => {
    setLoading(true);
    try {
      const res = await api.get('/extinguishers', {
        params: { per_page: 500, due: dueOnly ? '1' : undefined },
      });
      setExtinguishers(Array.isArray(res.data) ? res.data : (res.data?.extinguishers ?? []));
    } catch {
      setExtinguishers([]);
    }
    setLoading(false);
  };

  // Dynamic rows based on count
  const handleCountChange = (value) => {
    const num = Math.max(0, parseInt(value || '0', 10) || 0);
    setCount(value);
    setRows(Array.from({ length: num }, (_, i) => ({
      label: `Extinguisher ${i + 1}`,
      location: '',
      type: '',
      capacity: '',
      installation_date: '',
      next_refill_date: '',
      year_of_manufacturing: '',
      remark: '',
    })));
  };

  const updateRow = (index, field, value) => {
    setRows(prev => prev.map((r, i) => (i === index ? { ...r, [field]: value } : r)));
  };

  const openAdd = () => {
    setFormError('');
    setBuildingName('');
    setCount('');
    setRows([]);
    setAddOpen(true);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setFormError('');

    if (!buildingName.trim()) {
      setFormError('Please enter a building name.');
      return;
    }
    const numCount = parseInt(count || '0', 10) || 0;
    if (numCount < 1) {
      setFormError('Please enter a valid number of extinguishers.');
      return;
    }

    const items = rows.map((r) => {
      const item = { label: r.label, location: r.location, type: r.type, remark: r.remark };
      if (r.capacity !== '' && r.capacity !== null && r.capacity !== undefined) item.capacity = parseInt(r.capacity, 10);
      if (isValidDate(r.installation_date)) item.installation_date = iso(r.installation_date);
      if (isValidDate(r.next_refill_date)) item.next_refill_date = iso(r.next_refill_date);
      if (r.year_of_manufacturing !== '' && r.year_of_manufacturing !== null && r.year_of_manufacturing !== undefined) {
        item.year_of_manufacturing = parseInt(r.year_of_manufacturing, 10);
      }
      return item;
    });

    try {
      await api.post('/extinguishers', {
        building_name: buildingName.trim(),
        count: numCount,
        items,
      });
      setAddOpen(false);
      loadExtinguishers();
    } catch (err) {
      const msg = err.response?.data?.message || err.message || 'Failed to save extinguishers.';
      setFormError(msg);
    }
  };

  const handleDelete = async (ext) => {
    if (!window.confirm('Delete this extinguisher?')) return;
    try {
      await api.delete(`/extinguishers/${ext.id}`);
      loadExtinguishers();
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to delete extinguisher.');
    }
  };

  const handleUpdate = async (ext, key, value) => {
    let payload;
    if (key === 'capacity' || key === 'year_of_manufacturing') {
      payload = { [key]: value === '' || value === null ? null : parseInt(value, 10) };
    } else if (key === 'installation_date' || key === 'next_refill_date') {
      payload = { [key]: iso(value) };
    } else {
      payload = { [key]: value };
    }
    try {
      const res = await api.put(`/extinguishers/${ext.id}`, payload);
      const updated = res.data?.extinguisher ?? res.data;
      if (updated && typeof updated === 'object' && !Array.isArray(updated)) {
        setExtinguishers(prev =>
          prev.map(x => (x.id === updated.id ? { ...x, ...updated } : x))
        );
      }
    } catch (err) {
      alert(err.response?.data?.message || 'Update failed.');
    }
  };

  const filtered = useMemo(() => {
    const q = search.toLowerCase().trim();
    return extinguishers.filter((x) => {
      const name = x.building?.name || '';
      if (q && !name.toLowerCase().includes(q) && !(x.label || '').toLowerCase().includes(q)) {
        return false;
      }
      return true;
    });
  }, [extinguishers, search]);

  const totalCount = extinguishers.length;
  const dueCount = extinguishers.filter((x) => {
    const d = x.next_refill_date;
    return d && new Date(d) <= new Date();
  }).length;

  return (
    <div className="w-full">
      <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
          <h1 className="text-3xl font-extrabold text-foreground tracking-tight">Fire Extinguishers</h1>
          <p className="text-gray-500 mt-1">Track extinguishers per building along with installation and next refill dates.</p>
        </div>
        <button
          onClick={openAdd}
          className="inline-flex items-center justify-center w-full sm:w-auto px-5 py-3 bg-primary text-white rounded-xl font-semibold hover:bg-primary/90 transition-all shadow-md shadow-blue-200"
        >
          <Plus className="w-5 h-5 mr-2" />
          Add Extinguishers
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center">
          <div className="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center mr-4 text-blue-600">
            <Flame className="w-6 h-6" />
          </div>
          <div>
            <div className="text-2xl font-bold text-gray-900">{totalCount}</div>
            <div className="text-sm text-gray-500 font-medium">Total Extinguishers</div>
          </div>
        </div>
        <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center">
          <div className="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center mr-4 text-red-600">
            <AlertTriangle className="w-6 h-6" />
          </div>
          <div>
            <div className="text-2xl font-bold text-gray-900">{dueCount}</div>
            <div className="text-sm text-gray-500 font-medium">Refill Due</div>
          </div>
        </div>
        <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center">
          <div className="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center mr-4 text-green-600">
            <CheckCircle2 className="w-6 h-6" />
          </div>
          <div>
            <div className="text-2xl font-bold text-gray-900">{totalCount - dueCount}</div>
            <div className="text-sm text-gray-500 font-medium">Up to Date</div>
          </div>
        </div>
      </div>

      <div className="bg-white p-4 rounded-xl border border-gray-100 shadow-sm mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
        <div className="w-full md:w-96 relative">
          <Search className="absolute left-3 top-3 w-5 h-5 text-gray-400" />
          <input
            type="text"
            placeholder="Search by building or label..."
            className="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
          />
        </div>
        <label className="flex items-center cursor-pointer gap-2 text-sm text-gray-700">
          <input
            type="checkbox"
            checked={dueOnly}
            onChange={(e) => setDueOnly(e.target.checked)}
            className="rounded border-gray-300"
          />
          <AlertTriangle className="w-4 h-4 text-red-500" />
          Refill due only
        </label>
      </div>

      {loading ? (
        <div className="flex justify-center items-center py-20">
          <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
        </div>
      ) : filtered.length === 0 ? (
        <div className="bg-white rounded-2xl border border-gray-100 p-12 text-center shadow-sm">
          <Info className="w-12 h-12 text-gray-400 mx-auto mb-4" />
          <h3 className="text-lg font-bold text-gray-900">No Extinguishers Found</h3>
          <p className="text-gray-500 mt-1">Add extinguishers for a building to get started.</p>
        </div>
      ) : (
        <div className="overflow-x-auto bg-white rounded-2xl border border-gray-100 shadow-sm">
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b border-gray-200 text-left text-xs font-bold text-gray-500 uppercase">
                <th className="py-3 px-4">Location</th>
                <th className="py-3 px-4">Building</th>
                <th className="py-3 px-4">SR No</th>
                <th className="py-3 px-4">Type</th>
                <th className="py-3 px-4">Capacity (kg)</th>
                <th className="py-3 px-4">Installation Date</th>
                <th className="py-3 px-4">Refilling Date</th>
                <th className="py-3 px-4">Year of Manufacturing</th>
                <th className="py-3 px-4">Remark</th>
                <th className="py-3 px-4 text-right">Status</th>
                <th className="py-3 px-4 text-right">Action</th>
              </tr>
            </thead>
            <tbody>
              {filtered.map((x) => {
                const due = x.next_refill_date && new Date(x.next_refill_date) <= new Date();
                return (
                  <tr key={x.id} className="border-b border-gray-50 hover:bg-gray-50/50">
                    <td className="py-3 px-4">
                      <input
                        type="text"
                        value={x.location || ''}
                        onChange={(e) => handleUpdate(x, 'location', e.target.value)}
                        placeholder="e.g. Ground Floor"
                        className="border border-gray-200 rounded-md px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full min-w-[110px]"
                      />
                    </td>
                    <td className="py-3 px-4">
                      <div className="flex items-center gap-2">
                        <Building2 className="w-4 h-4 text-gray-400" />
                        <span className="font-medium text-gray-800">{x.building?.name || '—'}</span>
                      </div>
                    </td>
                    <td className="py-3 px-4 text-gray-600">{x.label || '-'}</td>
                    <td className="py-3 px-4">
                      <select
                        value={x.type || ''}
                        onChange={(e) => handleUpdate(x, 'type', e.target.value)}
                        className="border border-gray-200 rounded-md px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 max-w-[200px]"
                      >
                        <option value="">—</option>
                        {EXTINGUISHER_TYPES.map((t) => (
                          <option key={t} value={t}>{t}</option>
                        ))}
                      </select>
                    </td>
                    <td className="py-3 px-4">
                      <input
                        type="number"
                        min="0"
                        value={x.capacity ?? ''}
                        onChange={(e) => handleUpdate(x, 'capacity', e.target.value)}
                        className="w-20 border border-gray-200 rounded-md px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                      />
                    </td>
                    <td className="py-3 px-4">
                      <div className="flex items-center gap-2">
                        <CalendarDays className="w-4 h-4 text-gray-400" />
                        <input
                          type="date"
                          value={iso(x.installation_date)}
                          onChange={(e) => handleUpdate(x, 'installation_date', e.target.value)}
                          className="border border-gray-200 rounded-md px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                      </div>
                    </td>
                    <td className="py-3 px-4">
                      <div className="flex items-center gap-2">
                        <CalendarClock className={`w-4 h-4 ${due ? 'text-red-500' : 'text-gray-400'}`} />
                        <input
                          type="date"
                          value={iso(x.next_refill_date)}
                          onChange={(e) => handleUpdate(x, 'next_refill_date', e.target.value)}
                          className="border border-gray-200 rounded-md px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                      </div>
                    </td>
                    <td className="py-3 px-4">
                      <input
                        type="number"
                        min="0"
                        max="2100"
                        value={x.year_of_manufacturing ?? ''}
                        onChange={(e) => handleUpdate(x, 'year_of_manufacturing', e.target.value)}
                        placeholder="e.g. 2020"
                        className="w-24 border border-gray-200 rounded-md px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                      />
                    </td>
                    <td className="py-3 px-4">
                      <input
                        type="text"
                        value={x.remark || ''}
                        onChange={(e) => handleUpdate(x, 'remark', e.target.value)}
                        placeholder="—"
                        className="border border-gray-200 rounded-md px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full min-w-[120px]"
                      />
                    </td>
                    <td className="py-3 px-4 text-right">
                      {due ? (
                        <span className="px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700">Due</span>
                      ) : (
                        <span className="px-2.5 py-1 rounded-full text-xs font-bold bg-green-50 text-green-700">OK</span>
                      )}
                    </td>
                    <td className="py-3 px-4 text-right">
                      <button
                        onClick={() => handleDelete(x)}
                        className="p-2 text-gray-600 hover:text-red-600 hover:bg-white rounded-lg transition-colors border border-transparent hover:border-gray-100"
                        title="Delete"
                      >
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      )}

      {addOpen && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50 overflow-y-auto">
          <div className="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
            <div className="flex justify-between items-center px-6 py-4 border-b border-gray-150">
              <h2 className="text-xl font-bold text-gray-900">Add Fire Extinguishers</h2>
              <button onClick={() => setAddOpen(false)} className="text-gray-400 hover:text-gray-600">
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleSubmit} className="p-6 space-y-6">
              {formError && (
                <div className="bg-red-50 text-red-700 p-3 rounded-lg text-sm flex items-center">
                  <ShieldAlert className="w-5 h-5 mr-2" />
                  {formError}
                </div>
              )}

              <div>
                <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Building Name</label>
                <div className="relative">
                  <Building2 className="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                  <input
                    type="text"
                    value={buildingName}
                    onChange={(e) => {
                      setBuildingName(e.target.value);
                      setFormError('');
                    }}
                    placeholder="Enter building name..."
                    className="w-full pl-10 pr-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Total Number of Extinguishers</label>
                <input
                  type="number"
                  min="1"
                  max="500"
                  value={count}
                  onChange={(e) => handleCountChange(e.target.value)}
                  placeholder="e.g. 5"
                  className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <p className="text-xs text-gray-400 mt-1">Enter the total count. You'll fill in each extinguisher's dates below.</p>
              </div>

              {rows.length > 0 && (
                <div className="bg-gray-50/50 p-4 rounded-xl border border-gray-100 space-y-3">
                  <h3 className="text-sm font-bold text-gray-800">Extinguisher Details</h3>
                  {rows.map((row, i) => (
                    <div key={i} className="space-y-3 bg-white p-3 rounded-lg border border-gray-100">
                      <div className="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                        <div>
                          <label className="block text-[10px] font-bold text-gray-500 uppercase mb-1">#{i + 1} SR No</label>
                          <input
                            type="text"
                            value={row.label}
                            onChange={(e) => updateRow(i, 'label', e.target.value)}
                            placeholder="e.g. EXT-001"
                            className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                          />
                        </div>
                        <div>
                          <label className="block text-[10px] font-bold text-gray-500 uppercase mb-1">Location</label>
                          <input
                            type="text"
                            value={row.location}
                            onChange={(e) => updateRow(i, 'location', e.target.value)}
                            placeholder="e.g. Ground Floor"
                            className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                          />
                        </div>
                        <div>
                          <label className="block text-[10px] font-bold text-gray-500 uppercase mb-1">Type</label>
                          <select
                            value={row.type}
                            onChange={(e) => updateRow(i, 'type', e.target.value)}
                            className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                          >
                            <option value="">Select type...</option>
                            {EXTINGUISHER_TYPES.map((t) => (
                              <option key={t} value={t}>{t}</option>
                            ))}
                          </select>
                        </div>
                        <div>
                          <label className="block text-[10px] font-bold text-gray-500 uppercase mb-1">Capacity (kg)</label>
                          <input
                            type="number"
                            min="0"
                            value={row.capacity}
                            onChange={(e) => updateRow(i, 'capacity', e.target.value)}
                            placeholder="e.g. 2"
                            className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                          />
                        </div>
                      </div>
                      <div className="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                        <div>
                          <label className="block text-[10px] font-bold text-gray-500 uppercase mb-1">Year of Manufacturing</label>
                          <input
                            type="number"
                            min="1900"
                            max="2100"
                            value={row.year_of_manufacturing}
                            onChange={(e) => updateRow(i, 'year_of_manufacturing', e.target.value)}
                            placeholder="e.g. 2020"
                            className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                          />
                        </div>
                        <div>
                          <label className="block text-[10px] font-bold text-gray-500 uppercase mb-1">Installation Date</label>
                          <input
                            type="date"
                            value={row.installation_date}
                            onChange={(e) => updateRow(i, 'installation_date', e.target.value)}
                            className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                          />
                        </div>
                        <div>
                          <label className="block text-[10px] font-bold text-gray-500 uppercase mb-1">Refiling Date</label>
                          <input
                            type="date"
                            value={row.next_refill_date}
                            onChange={(e) => updateRow(i, 'next_refill_date', e.target.value)}
                            className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                          />
                        </div>
                        <div>
                          <label className="block text-[10px] font-bold text-gray-500 uppercase mb-1">Remark</label>
                          <input
                            type="text"
                            value={row.remark}
                            onChange={(e) => updateRow(i, 'remark', e.target.value)}
                            placeholder="Any notes..."
                            className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                          />
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              )}

              <div className="flex justify-end gap-3 pt-4 border-t border-gray-150">
                <button
                  type="button"
                  onClick={() => setAddOpen(false)}
                  className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition-colors"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 bg-primary text-white rounded-lg text-sm hover:bg-primary/90 transition-colors font-semibold"
                >
                  Save Extinguishers
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
