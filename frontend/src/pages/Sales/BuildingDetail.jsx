import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, Edit2, Save, X, Plus, Trash2, MapPin, Building2, Flame, Users, Calendar, FileText, Download, ExternalLink } from 'lucide-react';
import { useBuildingStore } from '../../store/buildingStore';
import { buildingDetailService } from '../../services/buildingDetailService';
import { salesDocumentService } from '../../services/salesDocumentService';
import { format } from 'date-fns';

const TABS = ['Overview', 'Wings', 'Fire Systems', 'Contacts', 'Documents'];
const FIRE_SYSTEM_TYPES = ['Fire Fighting', 'Fire Alarm', 'Fire Sprinkler', 'Fire Hydrant', 'Fire Extinguisher', 'Emergency Lighting', 'Smoke Detector', 'Others'];
const CONTACT_CATEGORIES = ['society', 'developer', 'architect', 'pmc', 'other'];
const DOC_CATEGORIES = ['Fire NOC', 'Building Plan', 'NOC Certificate', 'AMC Agreement', 'Quotation', 'Invoice', 'Site Photo', 'Other'];

export default function BuildingDetail() {
  const { id } = useParams();
  const navigate = useNavigate();

  if (id === 'new') {
    navigate('/dashboard/buildings', { replace: true });
    return null;
  }

  const { selected: building, fetchItem, loading } = useBuildingStore();
  const [activeTab, setActiveTab] = useState('Overview');
  const [editing, setEditing] = useState(false);
  const [form, setForm] = useState({});
  const [statuses, setStatuses] = useState([]);

  // Sub-entity states
  const [wings, setWings] = useState([]);
  const [fireSystems, setFireSystems] = useState([]);
  const [contacts, setContacts] = useState([]);
  const [documents, setDocuments] = useState([]);
  const [showForm, setShowForm] = useState(null);

  useEffect(() => {
    fetchItem(id);
    loadSubEntities();
    buildingDetailService.getStatuses().then(s => {
      if (Array.isArray(s)) setStatuses(s);
    }).catch(() => { });
  }, [id, fetchItem]);

  useEffect(() => {
    if (building) {
      setForm({
        name: building.name || '',
        society_name: building.society_name || '',
        building_type: building.building_type || '',
        address: building.address || '',
        area: building.area || '',
        city: building.city || '',
        state: building.state || '',
        country: building.country || '',
        pincode: building.pincode || '',
        latitude: building.latitude || '',
        longitude: building.longitude || '',
        no_of_floors: building.no_of_floors || '',
        no_of_wings: building.no_of_wings || '',
        no_of_flats: building.no_of_flats || '',
        commercial_shops_available: building.commercial_shops_available || false,
        no_of_staircase: building.no_of_staircase || '',
        no_of_lifts: building.no_of_lifts || '',
        no_of_exits_entry: building.no_of_exits_entry || '',
        fire_safety_available: building.fire_safety_available || false,
        fire_safety_type: building.fire_safety_type || '',
        under_construction: building.under_construction || false,
        property_owner: building.property_owner || '',
        plot_no: building.plot_no || '',
        developer_name: building.developer_name || '',
        developer_contact: building.developer_contact || '',
        architect_name: building.architect_name || '',
        architect_contact: building.architect_contact || '',
        pmc_consultant_name: building.pmc_consultant_name || '',
        pmc_consultant_contact: building.pmc_consultant_contact || '',
        provisional_noc: building.provisional_noc || false,
        provisional_noc_details: building.provisional_noc_details || '',
        construction_year: building.construction_year || '',
        occupancy_certificate_year: building.occupancy_certificate_year || '',
        fire_noc_status: building.fire_noc_status || 'Unknown',
        basement_levels: building.basement_levels || '',
        terrace_available: building.terrace_available || false,
        refuge_floors: building.refuge_floors || '',
        status: building.status || 'Active',
      });
    }
  }, [building]);

  const loadSubEntities = async () => {
    try {
      const [w, f, c, d] = await Promise.all([
        buildingDetailService.getWings(id),
        buildingDetailService.getFireSystems(id),
        buildingDetailService.getContacts(id),
        salesDocumentService.list({ building_id: id }),
      ]);
      if (Array.isArray(w)) setWings(w);
      if (Array.isArray(f)) setFireSystems(f);
      if (Array.isArray(c)) setContacts(c);
      if (Array.isArray(d)) setDocuments(d);
    } catch (err) {
      console.error(err);
    }
  };

  const handleSave = async () => {
    try {
      await useBuildingStore.getState().updateItem(id, form);
      setEditing(false);
      fetchItem(id);
    } catch (err) {
      console.error(err);
    }
  };

  const handleAssignStatus = async (statusId) => {
    try {
      await buildingDetailService.assignStatus(id, statusId);
      fetchItem(id);
    } catch (err) {
      console.error(err);
    }
  };

  const handleRemoveStatus = async (statusId) => {
    try {
      await buildingDetailService.removeStatus(id, statusId);
      fetchItem(id);
    } catch (err) {
      console.error(err);
    }
  };

  if (loading) {
    return <div className="flex items-center justify-center h-64"><div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600" /></div>;
  }

  if (!building) {
    return <div className="text-center py-12 text-gray-500">Building not found</div>;
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-3">
          <button onClick={() => navigate('/dashboard/buildings')} className="p-2 hover:bg-gray-100 rounded-lg"><ArrowLeft className="w-5 h-5" /></button>
          <div>
            <h1 className="text-2xl font-bold text-gray-900">{building.name}</h1>
            <p className="text-sm text-gray-500 flex items-center gap-1"><MapPin className="w-3 h-3" />{building.address || ''} {building.city || ''}</p>
          </div>
        </div>
        <div className="flex gap-2">
          <button onClick={() => window.open(`/reports/building/${id}?format=pdf`)} className="flex items-center gap-2 px-3 py-2 border rounded-lg text-sm hover:bg-gray-50">
            <Download className="w-4 h-4" /> PDF Report
          </button>
          {editing ? (
            <>
              <button onClick={handleSave} className="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg text-sm"><Save className="w-4 h-4" /> Save</button>
              <button onClick={() => setEditing(false)} className="flex items-center gap-2 px-4 py-2 border rounded-lg text-sm"><X className="w-4 h-4" /> Cancel</button>
            </>
          ) : (
            <button onClick={() => setEditing(true)} className="flex items-center gap-2 px-4 py-2 border rounded-lg text-sm"><Edit2 className="w-4 h-4" /> Edit</button>
          )}
        </div>
      </div>

      {/* Status Badges */}
      <div className="flex items-center gap-2 flex-wrap">
        {(building.statuses || []).map(s => (
          <span key={s.id} className="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium text-white" style={{ backgroundColor: s.color }}>
            {s.name}
            {editing && <button onClick={() => handleRemoveStatus(s.id)} className="ml-1 hover:bg-white/20 rounded-full p-0.5"><X className="w-3 h-3" /></button>}
          </span>
        ))}
        {editing && statuses.filter(s => !(building.statuses || []).find(bs => bs.id === s.id)).length > 0 && (
          <select onChange={e => { if (e.target.value) handleAssignStatus(parseInt(e.target.value)); e.target.value = ''; }} className="px-3 py-1 border rounded-full text-sm bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            <option value="">+ Add Status</option>
            {statuses.filter(s => !(building.statuses || []).find(bs => bs.id === s.id)).map(s => (
              <option key={s.id} value={s.id}>{s.name}</option>
            ))}
          </select>
        )}
      </div>

      {/* Tabs */}
      <div className="flex border-b overflow-x-auto">
        {TABS.map(tab => (
          <button key={tab} onClick={() => setActiveTab(tab)}
            className={`px-4 py-2 text-sm font-medium border-b-2 whitespace-nowrap ${activeTab === tab ? 'border-red-600 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700'}`}>
            {tab}
          </button>
        ))}
      </div>

      {/* Tab Content */}
      {activeTab === 'Overview' && (
        <div className="bg-white rounded-lg border p-6">
          {editing ? (
            <div className="space-y-4">
              <h3 className="text-sm font-semibold text-gray-900">Basic Information</h3>
              <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                <Field label="Name" value={form.name} onChange={v => setForm({ ...form, name: v })} />
                <Field label="Society Name" value={form.society_name} onChange={v => setForm({ ...form, society_name: v })} />
                <Field label="Building Type" value={form.building_type} onChange={v => setForm({ ...form, building_type: v })} />
                <Field label="Address" value={form.address} onChange={v => setForm({ ...form, address: v })} full />
                <Field label="Area" value={form.area} onChange={v => setForm({ ...form, area: v })} />
                <Field label="City" value={form.city} onChange={v => setForm({ ...form, city: v })} />
                <Field label="State" value={form.state} onChange={v => setForm({ ...form, state: v })} />
                <Field label="Pincode" value={form.pincode} onChange={v => setForm({ ...form, pincode: v })} />
              </div>

              <h3 className="text-sm font-semibold text-gray-900 mt-6">Building Details</h3>
              <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                <Field label="Floors" value={form.no_of_floors} onChange={v => setForm({ ...form, no_of_floors: v })} type="number" />
                <Field label="Total Flats" value={form.no_of_flats} onChange={v => setForm({ ...form, no_of_flats: v })} type="number" />
                <Field label="Staircases" value={form.no_of_staircase} onChange={v => setForm({ ...form, no_of_staircase: v })} type="number" />
                <Field label="Lifts" value={form.no_of_lifts} onChange={v => setForm({ ...form, no_of_lifts: v })} type="number" />
                <Field label="Exits" value={form.no_of_exits_entry} onChange={v => setForm({ ...form, no_of_exits_entry: v })} type="number" />
                <Field label="Basement Levels" value={form.basement_levels} onChange={v => setForm({ ...form, basement_levels: v })} type="number" />
                <Field label="Refuge Floors" value={form.refuge_floors} onChange={v => setForm({ ...form, refuge_floors: v })} type="number" />
                <Field label="Construction Year" value={form.construction_year} onChange={v => setForm({ ...form, construction_year: v })} type="number" />
                <Field label="Occupancy Cert. Year" value={form.occupancy_certificate_year} onChange={v => setForm({ ...form, occupancy_certificate_year: v })} type="number" />
                <ToggleField label="Shops Available" checked={form.commercial_shops_available} onChange={v => setForm({ ...form, commercial_shops_available: v })} />
                <ToggleField label="Under Construction" checked={form.under_construction} onChange={v => setForm({ ...form, under_construction: v })} />
                <ToggleField label="Terrace Available" checked={form.terrace_available} onChange={v => setForm({ ...form, terrace_available: v })} />
              </div>

              <h3 className="text-sm font-semibold text-gray-900 mt-6">Fire Safety</h3>
              <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                <ToggleField label="Fire Safety Available" checked={form.fire_safety_available} onChange={v => setForm({ ...form, fire_safety_available: v })} />
                <Field label="Fire Safety Type" value={form.fire_safety_type} onChange={v => setForm({ ...form, fire_safety_type: v })} />
                <div>
                  <label className="block text-sm font-medium text-color-white">Fire NOC Status</label>
                  <select value={form.fire_noc_status} onChange={e => setForm({ ...form, fire_noc_status: e.target.value })} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    {['Unknown', 'Applied', 'Approved', 'Expired', 'Rejected'].map(s => <option key={s} value={s}>{s}</option>)}
                  </select>
                </div>
              </div>

              <h3 className="text-sm font-semibold text-gray-900 mt-6">Stakeholders</h3>
              <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                <Field label="Property Owner" value={form.property_owner} onChange={v => setForm({ ...form, property_owner: v })} />
                <Field label="Developer Name" value={form.developer_name} onChange={v => setForm({ ...form, developer_name: v })} />
                <Field label="Developer Contact" value={form.developer_contact} onChange={v => setForm({ ...form, developer_contact: v })} />
                <Field label="Architect Name" value={form.architect_name} onChange={v => setForm({ ...form, architect_name: v })} />
                <Field label="Architect Contact" value={form.architect_contact} onChange={v => setForm({ ...form, architect_contact: v })} />
                <Field label="PMC Consultant" value={form.pmc_consultant_name} onChange={v => setForm({ ...form, pmc_consultant_name: v })} />
                <Field label="PMC Contact" value={form.pmc_consultant_contact} onChange={v => setForm({ ...form, pmc_consultant_contact: v })} />
              </div>
            </div>
          ) : (
            <div className="space-y-6">
              <Section title="Basic Information">
                <InfoGrid items={[
                  { label: 'Society', value: building.society_name },
                  { label: 'Type', value: building.building_type },
                  { label: 'Area', value: building.area },
                  { label: 'Floors', value: building.no_of_floors },
                  { label: 'Flats', value: building.no_of_flats },
                  { label: 'Construction Year', value: building.construction_year },
                  { label: 'Fire NOC', value: building.fire_noc_status },
                ]} />
              </Section>
              <Section title="Fire Safety">
                <InfoGrid items={[
                  { label: 'Fire Safety', value: building.fire_safety_available ? 'Yes' : 'No' },
                  { label: 'Type', value: building.fire_safety_type },
                  { label: 'NOC Status', value: building.fire_noc_status },
                ]} />
              </Section>
              <Section title="Stakeholders">
                <InfoGrid items={[
                  { label: 'Developer', value: building.developer_name ? `${building.developer_name}${building.developer_contact ? ` (${building.developer_contact})` : ''}` : null },
                  { label: 'Architect', value: building.architect_name ? `${building.architect_name}${building.architect_contact ? ` (${building.architect_contact})` : ''}` : null },
                  { label: 'PMC', value: building.pmc_consultant_name ? `${building.pmc_consultant_name}${building.pmc_consultant_contact ? ` (${building.pmc_consultant_contact})` : ''}` : null },
                  { label: 'Owner', value: building.property_owner },
                ]} />
              </Section>
            </div>
          )}
        </div>
      )}

      {activeTab === 'Wings' && <WingsTab wings={wings} buildingId={id} reload={loadSubEntities} editing={editing} />}
      {activeTab === 'Fire Systems' && <FireSystemsTab systems={fireSystems} buildingId={id} reload={loadSubEntities} editing={editing} />}
      {activeTab === 'Contacts' && <ContactsTab contacts={contacts} buildingId={id} reload={loadSubEntities} editing={editing} />}
      {activeTab === 'Documents' && <DocumentsTab documents={documents} buildingId={id} reload={loadSubEntities} />}
    </div>
  );
}

function WingsTab({ wings, buildingId, reload, editing }) {
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState({ name: '', floors: '', flats_per_floor: '', flat_configuration: '', total_flats: '' });
  const [editingId, setEditingId] = useState(null);

  const handleSave = async () => {
    try {
      if (editingId) {
        await buildingDetailService.updateWing(buildingId, editingId, form);
      } else {
        await buildingDetailService.createWing(buildingId, form);
      }
      setShowForm(false);
      setEditingId(null);
      setForm({ name: '', floors: '', flats_per_floor: '', flat_configuration: '', total_flats: '' });
      reload();
    } catch (err) { console.error(err); }
  };

  const handleEdit = (wing) => {
    setForm({ name: wing.name, floors: wing.floors || '', flats_per_floor: wing.flats_per_floor || '', flat_configuration: wing.flat_configuration || '', total_flats: wing.total_flats || '' });
    setEditingId(wing.id);
    setShowForm(true);
  };

  return (
    <div className="space-y-4">
      <div className="flex justify-between items-center">
        <h2 className="text-lg font-semibold text-gray-900">Wings ({wings.length})</h2>
        {editing && <button onClick={() => { setShowForm(true); setEditingId(null); }} className="flex items-center gap-2 px-3 py-1.5 bg-red-600 text-white rounded-lg text-sm"><Plus className="w-3 h-3" /> Add Wing</button>}
      </div>
      {showForm && (
        <div className="bg-white border rounded-lg p-4 space-y-3">
          <div className="grid grid-cols-5 gap-3">
            <input placeholder="Name" value={form.name} onChange={e => setForm({ ...form, name: e.target.value })} className="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
            <input placeholder="Floors" type="number" value={form.floors} onChange={e => setForm({ ...form, floors: e.target.value })} className="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
            <input placeholder="Flats/Floor" type="number" value={form.flats_per_floor} onChange={e => setForm({ ...form, flats_per_floor: e.target.value })} className="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
            <input placeholder="Config (e.g. 2BHK)" value={form.flat_configuration} onChange={e => setForm({ ...form, flat_configuration: e.target.value })} className="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
            <input placeholder="Total Flats" type="number" value={form.total_flats} onChange={e => setForm({ ...form, total_flats: e.target.value })} className="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
          </div>
          <div className="flex gap-2">
            <button onClick={handleSave} className="px-3 py-1.5 bg-green-600 text-white rounded-lg text-sm">{editingId ? 'Update' : 'Save'}</button>
            <button onClick={() => { setShowForm(false); setEditingId(null); }} className="px-3 py-1.5 border rounded-lg text-sm">Cancel</button>
          </div>
        </div>
      )}
      <div className="bg-white rounded-lg border overflow-hidden">
        <table className="w-full">
          <thead className="bg-gray-50 border-b"><tr>
            <th className="px-4 py-2 text-left text-xs font-medium text-gray-500">Name</th>
            <th className="px-4 py-2 text-left text-xs font-medium text-gray-500">Floors</th>
            <th className="px-4 py-2 text-left text-xs font-medium text-gray-500">Flats/Floor</th>
            <th className="px-4 py-2 text-left text-xs font-medium text-gray-500">Config</th>
            <th className="px-4 py-2 text-left text-xs font-medium text-gray-500">Total</th>
            {editing && <th className="px-4 py-2 text-right text-xs font-medium text-gray-500">Actions</th>}
          </tr></thead>
          <tbody className="divide-y divide-gray-100">
            {wings.map(wing => (
              <tr key={wing.id} className="hover:bg-gray-50">
                <td className="px-4 py-2 text-sm font-medium">{wing.name}</td>
                <td className="px-4 py-2 text-sm">{wing.floors}</td>
                <td className="px-4 py-2 text-sm">{wing.flats_per_floor}</td>
                <td className="px-4 py-2 text-sm">{wing.flat_configuration || '-'}</td>
                <td className="px-4 py-2 text-sm">{wing.total_flats}</td>
                {editing && <td className="px-4 py-2 text-right">
                  <button onClick={() => handleEdit(wing)} className="text-blue-600 hover:text-blue-800 text-xs mr-2">Edit</button>
                  <button onClick={async () => { await buildingDetailService.removeWing(buildingId, wing.id); reload(); }} className="text-red-600 hover:text-red-800 text-xs">Delete</button>
                </td>}
              </tr>
            ))}
          </tbody>
        </table>
        {wings.length === 0 && <p className="text-center text-gray-500 py-8 text-sm">No wings configured</p>}
      </div>
    </div>
  );
}

function FireSystemsTab({ systems, buildingId, reload, editing }) {
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState({ system_type: '', sub_type: '', quantity: '', capacity: '', brand: '', installation_year: '', last_testing_date: '' });
  const [editingId, setEditingId] = useState(null);

  const handleSave = async () => {
    try {
      const payload = { ...form };
      Object.keys(payload).forEach(k => { if (payload[k] === '') delete payload[k]; });
      if (editingId) {
        await buildingDetailService.updateFireSystem(buildingId, editingId, payload);
      } else {
        await buildingDetailService.createFireSystem(buildingId, payload);
      }
      setShowForm(false); setEditingId(null);
      setForm({ system_type: '', sub_type: '', quantity: '', capacity: '', brand: '', installation_year: '', last_testing_date: '' });
      reload();
    } catch (err) { console.error(err); }
  };

  return (
    <div className="space-y-4">
      <div className="flex justify-between items-center">
        <h2 className="text-lg font-semibold text-gray-900">Fire Systems ({systems.length})</h2>
        {editing && <button onClick={() => { setShowForm(true); setEditingId(null); }} className="flex items-center gap-2 px-3 py-1.5 bg-red-600 text-white rounded-lg text-sm"><Plus className="w-3 h-3" /> Add System</button>}
      </div>
      {showForm && (
        <div className="bg-white border rounded-lg p-4 space-y-3">
          <div className="grid grid-cols-4 gap-3">
            <select value={form.system_type} onChange={e => setForm({ ...form, system_type: e.target.value })} className="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
              <option value="">Type</option>
              {FIRE_SYSTEM_TYPES.map(t => <option key={t} value={t}>{t}</option>)}
            </select>
            <input placeholder="Sub Type" value={form.sub_type} onChange={e => setForm({ ...form, sub_type: e.target.value })} className="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
            <input placeholder="Quantity" type="number" value={form.quantity} onChange={e => setForm({ ...form, quantity: e.target.value })} className="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
            <input placeholder="Brand" value={form.brand} onChange={e => setForm({ ...form, brand: e.target.value })} className="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
            <input placeholder="Capacity" value={form.capacity} onChange={e => setForm({ ...form, capacity: e.target.value })} className="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
            <input placeholder="Installation Year" type="number" value={form.installation_year} onChange={e => setForm({ ...form, installation_year: e.target.value })} className="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
            <input placeholder="Last Tested" type="date" value={form.last_testing_date} onChange={e => setForm({ ...form, last_testing_date: e.target.value })} className="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
          </div>
          <div className="flex gap-2">
            <button onClick={handleSave} className="px-3 py-1.5 bg-green-600 text-white rounded-lg text-sm">{editingId ? 'Update' : 'Save'}</button>
            <button onClick={() => { setShowForm(false); setEditingId(null); }} className="px-3 py-1.5 border rounded-lg text-sm">Cancel</button>
          </div>
        </div>
      )}
      <div className="bg-white rounded-lg border overflow-x-auto">
        <table className="w-full">
          <thead className="bg-gray-50 border-b"><tr>
            <th className="px-4 py-2 text-left text-xs font-medium text-gray-500">Type</th>
            <th className="px-4 py-2 text-left text-xs font-medium text-gray-500">Sub Type</th>
            <th className="px-4 py-2 text-left text-xs font-medium text-gray-500">Qty</th>
            <th className="px-4 py-2 text-left text-xs font-medium text-gray-500">Brand</th>
            <th className="px-4 py-2 text-left text-xs font-medium text-gray-500">Capacity</th>
            <th className="px-4 py-2 text-left text-xs font-medium text-gray-500">Installed</th>
            <th className="px-4 py-2 text-left text-xs font-medium text-gray-500">Last Tested</th>
            {editing && <th className="px-4 py-2 text-right text-xs font-medium text-gray-500">Actions</th>}
          </tr></thead>
          <tbody className="divide-y divide-gray-100">
            {systems.map(sys => (
              <tr key={sys.id} className="hover:bg-gray-50">
                <td className="px-4 py-2 text-sm">{sys.system_type}</td>
                <td className="px-4 py-2 text-sm">{sys.sub_type || '-'}</td>
                <td className="px-4 py-2 text-sm">{sys.quantity || '-'}</td>
                <td className="px-4 py-2 text-sm">{sys.brand || '-'}</td>
                <td className="px-4 py-2 text-sm">{sys.capacity || '-'}</td>
                <td className="px-4 py-2 text-sm">{sys.installation_year || '-'}</td>
                <td className="px-4 py-2 text-sm">{sys.last_testing_date ? format(new Date(sys.last_testing_date), 'dd MMM yyyy') : '-'}</td>
                {editing && <td className="px-4 py-2 text-right">
                  <button onClick={() => { setForm({ system_type: sys.system_type, sub_type: sys.sub_type || '', quantity: sys.quantity || '', capacity: sys.capacity || '', brand: sys.brand || '', installation_year: sys.installation_year || '', last_testing_date: sys.last_testing_date || '' }); setEditingId(sys.id); setShowForm(true); }} className="text-blue-600 hover:text-blue-800 text-xs mr-2">Edit</button>
                  <button onClick={async () => { await buildingDetailService.removeFireSystem(buildingId, sys.id); reload(); }} className="text-red-600 hover:text-red-800 text-xs">Delete</button>
                </td>}
              </tr>
            ))}
          </tbody>
        </table>
        {systems.length === 0 && <p className="text-center text-gray-500 py-8 text-sm">No fire systems recorded</p>}
      </div>
    </div>
  );
}

function ContactsTab({ contacts, buildingId, reload, editing }) {
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState({ full_name: '', role: '', role_category: 'society', mobile_number: '', whatsapp_number: '', email: '', notes: '' });
  const [editingId, setEditingId] = useState(null);

  const handleSave = async () => {
    try {
      if (editingId) {
        await buildingDetailService.updateContact(buildingId, editingId, form);
      } else {
        await buildingDetailService.createContact(buildingId, form);
      }
      setShowForm(false); setEditingId(null);
      setForm({ full_name: '', role: '', role_category: 'society', mobile_number: '', whatsapp_number: '', email: '', notes: '' });
      reload();
    } catch (err) { console.error(err); }
  };

  return (
    <div className="space-y-4">
      <div className="flex justify-between items-center">
        <h2 className="text-lg font-semibold text-gray-900">Contacts ({contacts.length})</h2>
        {editing && <button onClick={() => { setShowForm(true); setEditingId(null); }} className="flex items-center gap-2 px-3 py-1.5 bg-red-600 text-white rounded-lg text-sm"><Plus className="w-3 h-3" /> Add Contact</button>}
      </div>
      {showForm && (
        <div className="bg-white border rounded-lg p-4 space-y-3">
          <div className="grid grid-cols-3 gap-3">
            <input placeholder="Full Name" value={form.full_name} onChange={e => setForm({ ...form, full_name: e.target.value })} className="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
            <input placeholder="Role (e.g. Secretary)" value={form.role} onChange={e => setForm({ ...form, role: e.target.value })} className="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
            <select value={form.role_category} onChange={e => setForm({ ...form, role_category: e.target.value })} className="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
              {CONTACT_CATEGORIES.map(c => <option key={c} value={c}>{c.charAt(0).toUpperCase() + c.slice(1)}</option>)}
            </select>
            <input placeholder="Mobile" value={form.mobile_number} onChange={e => setForm({ ...form, mobile_number: e.target.value })} className="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
            <input placeholder="WhatsApp" value={form.whatsapp_number} onChange={e => setForm({ ...form, whatsapp_number: e.target.value })} className="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
            <input placeholder="Email" type="email" value={form.email} onChange={e => setForm({ ...form, email: e.target.value })} className="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
          </div>
          <textarea placeholder="Notes" value={form.notes} onChange={e => setForm({ ...form, notes: e.target.value })} rows={2} className="w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
          <div className="flex gap-2">
            <button onClick={handleSave} className="px-3 py-1.5 bg-green-600 text-white rounded-lg text-sm">{editingId ? 'Update' : 'Save'}</button>
            <button onClick={() => { setShowForm(false); setEditingId(null); }} className="px-3 py-1.5 border rounded-lg text-sm">Cancel</button>
          </div>
        </div>
      )}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {contacts.map(contact => (
          <div key={contact.id} className="bg-white border rounded-lg p-4">
            <div className="flex items-start justify-between">
              <div>
                <p className="font-medium text-gray-900">{contact.full_name}</p>
                <p className="text-sm text-gray-500">{contact.role}</p>
                <span className="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{contact.role_category}</span>
              </div>
              {editing && <div className="flex gap-1">
                <button onClick={() => { setForm({ full_name: contact.full_name, role: contact.role, role_category: contact.role_category, mobile_number: contact.mobile_number || '', whatsapp_number: contact.whatsapp_number || '', email: contact.email || '', notes: contact.notes || '' }); setEditingId(contact.id); setShowForm(true); }} className="text-blue-600 hover:text-blue-800 text-xs">Edit</button>
                <button onClick={async () => { await buildingDetailService.removeContact(buildingId, contact.id); reload(); }} className="text-red-600 hover:text-red-800 text-xs">Del</button>
              </div>}
            </div>
            <div className="mt-3 space-y-1 text-xs text-gray-500">
              {contact.mobile_number && <p>📱 {contact.mobile_number}</p>}
              {contact.whatsapp_number && <p>💬 {contact.whatsapp_number}</p>}
              {contact.email && <p>✉️ {contact.email}</p>}
            </div>
          </div>
        ))}
        {contacts.length === 0 && <p className="text-center text-gray-500 py-8 text-sm col-span-full">No contacts recorded</p>}
      </div>
    </div>
  );
}

function DocumentsTab({ documents, buildingId, reload }) {
  const [uploading, setUploading] = useState(false);

  const handleUpload = async (e) => {
    const file = e.target.files[0];
    if (!file) return;
    setUploading(true);
    const formData = new FormData();
    formData.append('file', file);
    formData.append('building_id', buildingId);
    formData.append('category', 'Other');
    formData.append('name', file.name);
    try {
      await salesDocumentService.upload(formData);
      reload();
    } catch (err) { console.error(err); }
    setUploading(false);
    e.target.value = '';
  };

  return (
    <div className="space-y-4">
      <div className="flex justify-between items-center">
        <h2 className="text-lg font-semibold text-gray-900">Documents ({documents.length})</h2>
        <label className="flex items-center gap-2 px-3 py-1.5 bg-red-600 text-white rounded-lg text-sm cursor-pointer hover:bg-red-700">
          <Plus className="w-3 h-3" /> {uploading ? 'Uploading...' : 'Upload'}
          <input type="file" className="hidden" onChange={handleUpload} />
        </label>
      </div>
      {(documents || []).length === 0 ? (
        <div className="text-center py-12 text-gray-500 bg-white border rounded-lg">
          <FileText className="w-12 h-12 mx-auto text-gray-300 mb-3" />
          <p>No documents uploaded</p>
        </div>
      ) : (
        <div className="bg-white rounded-lg border overflow-hidden">
          <table className="w-full">
            <thead className="bg-gray-50 border-b"><tr>
              <th className="px-4 py-2 text-left text-xs font-medium text-gray-500">Name</th>
              <th className="px-4 py-2 text-left text-xs font-medium text-gray-500">Category</th>
              <th className="px-4 py-2 text-left text-xs font-medium text-gray-500">Size</th>
              <th className="px-4 py-2 text-left text-xs font-medium text-gray-500">Uploaded</th>
              <th className="px-4 py-2 text-right text-xs font-medium text-gray-500">Actions</th>
            </tr></thead>
            <tbody className="divide-y divide-gray-100">
              {(documents || []).map(doc => (
                <tr key={doc.id} className="hover:bg-gray-50">
                  <td className="px-4 py-2 text-sm font-medium">{doc.name}</td>
                  <td className="px-4 py-2 text-sm">{doc.category}</td>
                  <td className="px-4 py-2 text-sm">{doc.file_size ? `${(doc.file_size / 1024).toFixed(0)} KB` : '-'}</td>
                  <td className="px-4 py-2 text-sm text-gray-500">{doc.created_at ? format(new Date(doc.created_at), 'dd MMM yyyy') : '-'}</td>
                  <td className="px-4 py-2 text-right">
                    <a href={salesDocumentService.downloadUrl(doc.id)} className="text-blue-600 hover:text-blue-800 text-xs mr-2"><Download className="w-3 h-3 inline" /> Download</a>
                    <button onClick={async () => { if (confirm('Delete this document?')) { await salesDocumentService.remove(doc.id); reload(); } }} className="text-red-600 hover:text-red-800 text-xs"><Trash2 className="w-3 h-3 inline" /> Delete</button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}

// Shared components
function Field({ label, value, onChange, type = 'text', full }) {
  return (
    <div className={full ? 'col-span-full' : ''}>
      <label className="block text-sm font-medium text-color-white">{label}</label>
      <input type={type} value={value} onChange={e => onChange(e.target.value)} className="w-full px-3 py-2 border rounded-lg text-sm" />
    </div>
  );
}

function ToggleField({ label, checked, onChange }) {
  return (
    <div className="flex items-center gap-3">
      <button type="button" onClick={() => onChange(!checked)} className={`relative w-10 h-5 rounded-full transition-colors ${checked ? 'bg-red-600' : 'bg-gray-300'}`}>
        <span className={`absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full transition-transform ${checked ? 'translate-x-5' : ''}`} />
      </button>
      <span className="text-sm text-gray-700">{label}</span>
    </div>
  );
}

function Section({ title, children }) {
  return <div><h3 className="text-sm font-semibold text-gray-900 mb-3">{title}</h3>{children}</div>;
}

function InfoGrid({ items }) {
  return (
    <div className="grid grid-cols-2 md:grid-cols-3 gap-3">
      {items.filter(i => i.value).map((item, idx) => (
        <div key={idx}><p className="text-xs text-gray-500">{item.label}</p><p className="text-sm text-gray-900 mt-0.5">{item.value}</p></div>
      ))}
    </div>
  );
}
