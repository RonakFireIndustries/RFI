import React, { useEffect, useState, useMemo } from 'react';
import {
  Plus, Trash2, Search, Flame, Building2, CalendarDays, CalendarClock,
  Info, X, AlertTriangle, CheckCircle2, ShieldAlert, Paperclip, Download, FileText, Upload, ArrowLeft, User, Phone,
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

  // Certificates keyed by building_id
  const [certificates, setCertificates] = useState({});
  const [certsLoading, setCertsLoading] = useState(false);

  // Add flow
  const [addOpen, setAddOpen] = useState(false);
  const [formError, setFormError] = useState('');
  const [buildingName, setBuildingName] = useState('');
  const [addContactName, setAddContactName] = useState('');
  const [addContactNumber, setAddContactNumber] = useState('');
  const [count, setCount] = useState('');
  const [rows, setRows] = useState([]);
  const [addCertFile, setAddCertFile] = useState(null);
  const [addCertExpiry, setAddCertExpiry] = useState('');
  const [addCertRemarks, setAddCertRemarks] = useState('');

  // Row certificate upload UI
  const [certRow, setCertRow] = useState(null);
  const [certFile, setCertFile] = useState(null);
  const [certExpiry, setCertExpiry] = useState('');
  const [certRemarks, setCertRemarks] = useState('');

  // Submission guards to prevent duplicate entries
  const [saving, setSaving] = useState(false);
  const [uploadingCert, setUploadingCert] = useState(false);

  // Detail panel: building id currently being viewed (null = list)
  const [selectedBuildingId, setSelectedBuildingId] = useState(null);

  useEffect(() => {
    loadExtinguishers();
    loadCertificates();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [dueOnly]);

  const loadCertificates = async () => {
    setCertsLoading(true);
    try {
      const res = await api.get('/extinguishers/certificates', { params: { per_page: 500 } });
      const raw = Array.isArray(res.data) ? res.data : (res.data?.certificates ?? []);
      const map = {};
      (raw || []).forEach((c) => {
        const bId = c.building_id;
        if (!bId) return;
        if (!map[bId]) map[bId] = [];
        if (!map[bId].some((existing) => existing.id === c.id)) map[bId].push(c);
      });
      setCertificates(map);
    } catch {
      setCertificates({});
    }
    setCertsLoading(false);
  };

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

  const addCertificateToBuilding = async (buildingId, file, expiry, remarks) => {
    if (!file) return null;
    const fd = new FormData();
    fd.append('building_id', buildingId);
    fd.append('file', file);
    if (expiry) fd.append('expiry_date', iso(expiry));
    if (remarks) fd.append('remarks', remarks);
    const res = await api.post('/extinguishers/certificates', fd);
    const cert = res.data?.certificate ?? res.data;
    if (cert && typeof cert === 'object' && !Array.isArray(cert)) {
      setCertificates((prev) => {
        const list = [...(prev[buildingId] || [])];
        if (!list.some((existing) => existing.id === cert.id)) {
          list.push(cert);
          return { ...prev, [buildingId]: list };
        }
        return prev;
      });
    }
    return cert;
  };

  const downloadCertificate = async (certObj) => {
    try {
      const blob = await api.getBlob(`/extinguishers/certificates/${certObj.id}/download`);
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', certObj.file_name || 'certificate');
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to download certificate.');
    }
  };

  const deleteCertificate = async (certObj) => {
    if (!window.confirm('Delete this certificate?')) return;
    try {
      await api.delete(`/extinguishers/certificates/${certObj.id}`);
      const buildingId = certObj.building_id;
      setCertificates((prev) => ({
        ...prev,
        [buildingId]: (prev[buildingId] || []).filter((c) => c.id !== certObj.id),
      }));
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to delete certificate.');
    }
  };

  const openCertUpload = (ext) => {
    setCertRow(ext);
    setCertFile(null);
    setCertExpiry('');
    setCertRemarks('');
  };

  const submitRowCert = async (e) => {
    e.preventDefault();
    if (!certRow || uploadingCert) return;
    setUploadingCert(true);
    try {
      await addCertificateToBuilding(certRow.building_id, certFile, certExpiry, certRemarks);
      setCertRow(null);
      setCertFile(null);
      setCertExpiry('');
      setCertRemarks('');
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to upload certificate.');
    }
    setUploadingCert(false);
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
    setAddContactName('');
    setAddContactNumber('');
    setCount('');
    setRows([]);
    setAddCertFile(null);
    setAddCertExpiry('');
    setAddCertRemarks('');
    setAddOpen(true);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (saving) return;
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

    setSaving(true);
    try {
      const res = await api.post('/extinguishers', {
        building_name: buildingName.trim(),
        contact_name: addContactName.trim() || null,
        contact_number: addContactNumber.trim() || null,
        count: numCount,
        items,
      });
      const buildingId = res.data?.building?.id;
      if (addCertFile && buildingId) {
        try {
          await addCertificateToBuilding(buildingId, addCertFile, addCertExpiry, addCertRemarks);
        } catch (certErr) {
          setFormError('Extinguishers saved, but certificate upload failed: ' + (certErr.response?.data?.message || certErr.message));
          setAddOpen(false);
          setSaving(false);
          loadExtinguishers();
          return;
        }
      }
      setAddOpen(false);
      setSaving(false);
      loadExtinguishers();
    } catch (err) {
      setSaving(false);
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

  const handleDeleteBuilding = async (buildingId, name) => {
    if (!window.confirm(`Delete all extinguishers in "${name}"? This cannot be undone.`)) return;
    const items = extinguishers.filter((x) => (x.building_id ?? x.building?.id) === buildingId);
    try {
      for (const item of items) {
        await api.delete(`/extinguishers/${item.id}`);
      }
      if (selectedBuildingId === buildingId) setSelectedBuildingId(null);
      loadExtinguishers();
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to delete extinguishers.');
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
        if (key === 'contact_name' || key === 'contact_number') {
          const buildingId = ext.building_id;
          const siblings = extinguishers.filter(x => x.building_id === buildingId && x.id !== ext.id);
          for (const sib of siblings) {
            try { await api.put(`/extinguishers/${sib.id}`, payload); } catch { /* ignore */ }
          }
          setExtinguishers(prev =>
            prev.map(x => (x.building_id === buildingId && x.id !== updated.id ? { ...x, ...payload } : x))
          );
        }
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

  const groupedBuildings = useMemo(() => {
    const map = {};
    filtered.forEach((x) => {
      const bId = x.building_id ?? x.building?.id;
      if (bId === undefined || bId === null) return;
      if (!map[bId]) {
        map[bId] = { id: bId, name: x.building?.name || 'Unnamed Building', items: [] };
      }
      map[bId].items.push(x);
    });
    return Object.values(map).sort((a, b) => a.name.localeCompare(b.name));
  }, [filtered]);

  const selectedBuilding = useMemo(() => {
    if (selectedBuildingId === null) return null;
    return groupedBuildings.find((b) => b.id === selectedBuildingId)
      || {
        id: selectedBuildingId,
        name: extinguishers.find((x) => (x.building_id ?? x.building?.id) === selectedBuildingId)?.building?.name || 'Building',
        items: extinguishers.filter((x) => (x.building_id ?? x.building?.id) === selectedBuildingId),
      };
  }, [selectedBuildingId, groupedBuildings, extinguishers]);

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
      ) : selectedBuildingId === null ? (
        groupedBuildings.length === 0 ? (
          <div className="bg-white rounded-2xl border border-gray-100 p-12 text-center shadow-sm">
            <Info className="w-12 h-12 text-gray-400 mx-auto mb-4" />
            <h3 className="text-lg font-bold text-gray-900">No Extinguishers Found</h3>
            <p className="text-gray-500 mt-1">Add extinguishers for a building to get started.</p>
          </div>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            {groupedBuildings.map((b) => {
              const bDue = b.items.filter((x) => x.next_refill_date && new Date(x.next_refill_date) <= new Date()).length;
              const bCerts = (certificates[b.id] || []).length;
              return (
                <div key={b.id} className="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow p-5 flex flex-col hover:border-blue-200 group">
                  <div className="flex items-start justify-between mb-3">
                    <div className="flex items-center gap-3">
                      <div className="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <Building2 className="w-5 h-5" />
                      </div>
                      <div>
                        <h3 className="font-bold text-gray-900 leading-tight">{b.name}</h3>
                        <p className="text-xs text-gray-500">{b.items.length} extinguisher(s)</p>
                      </div>
                    </div>
                  </div>
                  <div className="flex flex-wrap items-center gap-2 mb-4">
                    {bDue > 0 ? (
                      <span className="px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700">{bDue} refill due</span>
                    ) : (
                      <span className="px-2.5 py-1 rounded-full text-xs font-bold bg-green-50 text-green-700">Up to date</span>
                    )}
                    {bCerts > 0 && (
                      <span className="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700">
                        <FileText className="inline w-3 h-3 mr-1" />{bCerts} cert(s)
                      </span>
                    )}
                  </div>
                  <div className="mt-auto flex items-center justify-between gap-2 pt-3 border-t border-gray-100">
                    <button
                      onClick={() => setSelectedBuildingId(b.id)}
                      className="flex-1 inline-flex items-center justify-center px-4 py-2 bg-primary text-white text-sm rounded-lg hover:bg-primary/90 transition-colors font-semibold"
                    >
                      View Details
                    </button>
                    <button
                      onClick={() => handleDeleteBuilding(b.id, b.name)}
                      className="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                      title="Delete all extinguishers in this building"
                    >
                      <Trash2 className="w-4 h-4" />
                    </button>
                  </div>
                </div>
              );
            })}
          </div>
        )
      ) : selectedBuilding ? (
        <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div className="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <div className="flex items-center gap-3">
              <button
                onClick={() => setSelectedBuildingId(null)}
                className="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                title="Back to list"
              >
                <ArrowLeft className="w-5 h-5" />
              </button>
              <div className="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                <Building2 className="w-5 h-5" />
              </div>
              <div>
                <h2 className="text-xl font-bold text-gray-900">{selectedBuilding.name}</h2>
                <p className="text-sm text-gray-500">{selectedBuilding.items.length} extinguisher(s)</p>
              </div>
            </div>
            <div className="flex items-center gap-2">
              <button
                onClick={() => openCertUpload(selectedBuilding.items[0])}
                className="inline-flex items-center px-4 py-2 border border-blue-200 text-blue-700 text-sm rounded-lg hover:bg-blue-50 transition-colors font-semibold"
              >
                <Paperclip className="w-4 h-4 mr-2" />
                Add Certificate
              </button>
              <button
                onClick={() => handleDeleteBuilding(selectedBuilding.id, selectedBuilding.name)}
                className="inline-flex items-center px-4 py-2 border border-red-200 text-red-600 text-sm rounded-lg hover:bg-red-50 transition-colors font-semibold"
              >
                <Trash2 className="w-4 h-4 mr-2" />
                Delete All
              </button>
            </div>
          </div>

          <div className="px-6 py-4 border-b border-gray-100 bg-white">
            <h3 className="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
              <User className="w-4 h-4 text-gray-500" />
              Contact Information
            </h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-[10px] font-bold text-gray-500 uppercase mb-1">Contact Name</label>
                <input
                  type="text"
                  value={selectedBuilding.items[0]?.contact_name || ''}
                  onChange={(e) => handleUpdate(selectedBuilding.items[0], 'contact_name', e.target.value)}
                  placeholder="Contact name"
                  className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label className="block text-[10px] font-bold text-gray-500 uppercase mb-1">Contact Number</label>
                <input
                  type="text"
                  value={selectedBuilding.items[0]?.contact_number || ''}
                  onChange={(e) => handleUpdate(selectedBuilding.items[0], 'contact_number', e.target.value)}
                  placeholder="Contact number"
                  className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>

          {(certificates[selectedBuilding.id] || []).length > 0 && (
            <div className="px-6 py-4 border-b border-gray-100">
              <h3 className="text-sm font-bold text-gray-800 mb-2 flex items-center gap-2">
                <FileText className="w-4 h-4 text-gray-500" />
                Certificates
              </h3>
              <div className="flex flex-wrap gap-2">
                {(certificates[selectedBuilding.id] || []).map((c) => (
                  <div key={c.id} className="inline-flex items-center gap-2 bg-blue-50 text-blue-700 text-sm rounded-md pl-3 pr-1.5 py-1.5">
                    <FileText className="w-4 h-4" />
                    <span className="max-w-[180px] truncate" title={c.file_name}>{c.file_name}</span>
                    {c.expiry_date && (
                      <span className="text-xs text-blue-500">exp {c.expiry_date}</span>
                    )}
                    <button onClick={() => downloadCertificate(c)} className="p-1 hover:text-blue-900" title="Download">
                      <Download className="w-4 h-4" />
                    </button>
                    <button onClick={() => deleteCertificate(c)} className="p-1 hover:text-red-600" title="Delete">
                      <Trash2 className="w-4 h-4" />
                    </button>
                  </div>
                ))}
              </div>
            </div>
          )}

          <div className="divide-y divide-gray-100">
            {selectedBuilding.items.map((x) => {
              const due = x.next_refill_date && new Date(x.next_refill_date) <= new Date();
              return (
                <div key={x.id} className="p-6">
                  <div className="flex items-center justify-between mb-4">
                    <div className="flex items-center gap-3">
                      <div className="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center text-orange-600">
                        <Flame className="w-5 h-5" />
                      </div>
                      <div>
                        <h3 className="font-bold text-gray-900">{x.label || 'Extinguisher'}</h3>
                        <p className="text-xs text-gray-500">{x.location || 'No location set'}</p>
                      </div>
                    </div>
                    <div className="flex items-center gap-2">
                      {due ? (
                        <span className="px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700">Due</span>
                      ) : (
                        <span className="px-2.5 py-1 rounded-full text-xs font-bold bg-green-50 text-green-700">OK</span>
                      )}
                      <button
                        onClick={() => handleDelete(x)}
                        className="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                        title="Delete this extinguisher"
                      >
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </div>
                  </div>

                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                      <label className="block text-[10px] font-bold text-gray-500 uppercase mb-1">SR No</label>
                      <input
                        type="text"
                        value={x.label || ''}
                        onChange={(e) => handleUpdate(x, 'label', e.target.value)}
                        className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                      />
                    </div>
                    <div>
                      <label className="block text-[10px] font-bold text-gray-500 uppercase mb-1">Location</label>
                      <input
                        type="text"
                        value={x.location || ''}
                        onChange={(e) => handleUpdate(x, 'location', e.target.value)}
                        placeholder="e.g. Ground Floor"
                        className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                      />
                    </div>
                    <div>
                      <label className="block text-[10px] font-bold text-gray-500 uppercase mb-1">Type</label>
                      <select
                        value={x.type || ''}
                        onChange={(e) => handleUpdate(x, 'type', e.target.value)}
                        className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                      >
                        <option value="">—</option>
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
                        value={x.capacity ?? ''}
                        onChange={(e) => handleUpdate(x, 'capacity', e.target.value)}
                        className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                      />
                    </div>
                    <div>
                      <label className="block text-[10px] font-bold text-gray-500 uppercase mb-1">Year of Manufacturing</label>
                      <input
                        type="number"
                        min="0"
                        max="2100"
                        value={x.year_of_manufacturing ?? ''}
                        onChange={(e) => handleUpdate(x, 'year_of_manufacturing', e.target.value)}
                        placeholder="e.g. 2020"
                        className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                      />
                    </div>
                    <div>
                      <label className="block text-[10px] font-bold text-gray-500 uppercase mb-1">Installation Date</label>
                      <input
                        type="date"
                        value={iso(x.installation_date)}
                        onChange={(e) => handleUpdate(x, 'installation_date', e.target.value)}
                        className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                      />
                    </div>
                    <div>
                      <label className="block text-[10px] font-bold text-gray-500 uppercase mb-1">Refilling Date</label>
                      <input
                        type="date"
                        value={iso(x.next_refill_date)}
                        onChange={(e) => handleUpdate(x, 'next_refill_date', e.target.value)}
                        className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                      />
                    </div>
                    <div className="md:col-span-2">
                      <label className="block text-[10px] font-bold text-gray-500 uppercase mb-1">Remark</label>
                      <input
                        type="text"
                        value={x.remark || ''}
                        onChange={(e) => handleUpdate(x, 'remark', e.target.value)}
                        placeholder="Any notes..."
                        className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                      />
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      ) : (
        <div className="flex justify-center items-center py-20">
          <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
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

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Contact Name</label>
                  <div className="relative">
                    <User className="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                    <input
                      type="text"
                      value={addContactName}
                      onChange={(e) => setAddContactName(e.target.value)}
                      placeholder="Enter contact name..."
                      className="w-full pl-10 pr-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                </div>
                <div>
                  <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Contact Number</label>
                  <div className="relative">
                    <Phone className="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                    <input
                      type="text"
                      value={addContactNumber}
                      onChange={(e) => setAddContactNumber(e.target.value)}
                      placeholder="Enter contact number..."
                      className="w-full pl-10 pr-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                </div>
              </div>

              <div className="bg-gray-50/50 p-4 rounded-xl border border-gray-100 space-y-3">
                <h3 className="text-sm font-bold text-gray-800 flex items-center gap-2">
                  <Paperclip className="w-4 h-4 text-gray-500" />
                  Certificate (Optional)
                </h3>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-3 items-end">
                  <div className="md:col-span-2">
                    <label className="block text-[10px] font-bold text-gray-500 uppercase mb-1">Certificate File</label>
                    <input
                      type="file"
                      accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                      onChange={(e) => setAddCertFile(e.target.files?.[0] || null)}
                      className="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:font-semibold hover:file:bg-blue-100"
                    />
                  </div>
                  <div>
                    <label className="block text-[10px] font-bold text-gray-500 uppercase mb-1">Expiry Date</label>
                    <input
                      type="date"
                      value={addCertExpiry}
                      onChange={(e) => setAddCertExpiry(e.target.value)}
                      className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                  <div>
                    <label className="block text-[10px] font-bold text-gray-500 uppercase mb-1">Remarks</label>
                    <input
                      type="text"
                      value={addCertRemarks}
                      onChange={(e) => setAddCertRemarks(e.target.value)}
                      placeholder="Any notes..."
                      className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
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
                  disabled={saving}
                  className="inline-flex items-center px-5 py-2 bg-primary text-white rounded-lg text-sm hover:bg-primary/90 transition-colors font-semibold disabled:opacity-60 disabled:cursor-not-allowed"
                >
                  {saving && <span className="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent mr-2"></span>}
                  {saving ? 'Saving...' : 'Save Extinguishers'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {certRow && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-2xl max-w-lg w-full shadow-2xl">
            <div className="flex justify-between items-center px-6 py-4 border-b border-gray-150">
              <h2 className="text-xl font-bold text-gray-900 flex items-center gap-2">
                <Paperclip className="w-5 h-5 text-gray-500" />
                Attach Certificate
              </h2>
              <button onClick={() => setCertRow(null)} className="text-gray-400 hover:text-gray-600">
                <X className="w-5 h-5" />
              </button>
            </div>
            <form onSubmit={submitRowCert} className="p-6 space-y-4">
              <div>
                <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Building</label>
                <div className="flex items-center gap-2 text-sm text-gray-700">
                  <Building2 className="w-4 h-4 text-gray-400" />
                  <span className="font-medium">{certRow.building?.name || '—'}</span>
                </div>
              </div>
              <div>
                <label className="block text-[10px] font-bold text-gray-500 uppercase mb-1">Certificate File</label>
                <input
                  type="file"
                  accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                  required
                  onChange={(e) => setCertFile(e.target.files?.[0] || null)}
                  className="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:font-semibold hover:file:bg-blue-100"
                />
              </div>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                  <label className="block text-[10px] font-bold text-gray-500 uppercase mb-1">Expiry Date</label>
                  <input
                    type="date"
                    value={certExpiry}
                    onChange={(e) => setCertExpiry(e.target.value)}
                    className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label className="block text-[10px] font-bold text-gray-500 uppercase mb-1">Remarks</label>
                  <input
                    type="text"
                    value={certRemarks}
                    onChange={(e) => setCertRemarks(e.target.value)}
                    placeholder="Any notes..."
                    className="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
              </div>
              <div className="flex justify-end gap-3 pt-4 border-t border-gray-150">
                <button
                  type="button"
                  onClick={() => setCertRow(null)}
                  className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition-colors"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={uploadingCert}
                  className="inline-flex items-center px-5 py-2 bg-primary text-white rounded-lg text-sm hover:bg-primary/90 transition-colors font-semibold disabled:opacity-60 disabled:cursor-not-allowed"
                >
                  {uploadingCert ? (
                    <span className="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent mr-2"></span>
                  ) : (
                    <Upload className="w-4 h-4 mr-2" />
                  )}
                  {uploadingCert ? 'Uploading...' : 'Upload Certificate'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
