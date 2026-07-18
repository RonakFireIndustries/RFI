import React, { useEffect, useState } from 'react';
import { useBuildingStore } from '../../store/buildingStore';
import { useSiteStore } from '../../store/siteStore';
import {
  Plus, Edit, Trash2, Search, MapPin, Building2, Info, X, Compass,
  Shield, Flame, Hammer, User, Phone, FileText, ChevronDown, ChevronUp
} from 'lucide-react';

const defaultFormData = {
  name: '',
  address: '',
  city: '',
  state: '',
  country: 'India',
  pincode: '',
  latitude: '',
  longitude: '',
  no_of_floors: '',
  no_of_wings: '',
  no_of_flats: '',
  commercial_shops_available: false,
  no_of_staircase: '',
  no_of_lifts: '',
  no_of_exits_entry: '',
  fire_safety_available: false,
  fire_safety_type: '',
  under_construction: false,
  property_owner: '',
  plot_no: '',
  developer_name: '',
  developer_contact: '',
  architect_name: '',
  architect_contact: '',
  pmc_consultant_name: '',
  pmc_consultant_contact: '',
  provisional_noc: false,
  provisional_noc_details: '',
  site_id: '',
  status: 'Active',
};

export default function Buildings() {
  const {
    items: buildings,
    loading,
    fetchItems: fetchBuildings,
    createItem: createBuilding,
    updateItem: updateBuilding,
    deleteItem: deleteBuilding,
  } = useBuildingStore();

  const { items: sites, fetchItems: fetchSites } = useSiteStore();

  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [currentPage, setCurrentPage] = useState(1);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingBuilding, setEditingBuilding] = useState(null);
  const [formData, setFormData] = useState({ ...defaultFormData });
  const [formErrors, setFormErrors] = useState({});
  const [expandedCard, setExpandedCard] = useState(null);

  useEffect(() => {
    fetchBuildings({ search, status: statusFilter, page: currentPage, per_page: 1000 });
    fetchSites({ per_page: 1000 });
  }, [search, statusFilter, currentPage]);

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
    if (formErrors[name]) setFormErrors(prev => ({ ...prev, [name]: null }));
  };

  const openAddModal = () => {
    setEditingBuilding(null);
    setFormData({ ...defaultFormData });
    setFormErrors({});
    setIsModalOpen(true);
  };

  const openEditModal = (b) => {
    setEditingBuilding(b);
    setFormData({
      name: b.name || '',
      address: b.address || '',
      city: b.city || '',
      state: b.state || '',
      country: b.country || 'India',
      pincode: b.pincode || '',
      latitude: b.latitude || '',
      longitude: b.longitude || '',
      no_of_floors: b.no_of_floors ?? '',
      no_of_wings: b.no_of_wings || '',
      no_of_flats: b.no_of_flats ?? '',
      commercial_shops_available: !!b.commercial_shops_available,
      no_of_staircase: b.no_of_staircase ?? '',
      no_of_lifts: b.no_of_lifts ?? '',
      no_of_exits_entry: b.no_of_exits_entry ?? '',
      fire_safety_available: !!b.fire_safety_available,
      fire_safety_type: b.fire_safety_type || '',
      under_construction: !!b.under_construction,
      property_owner: b.property_owner || '',
      plot_no: b.plot_no || '',
      developer_name: b.developer_name || '',
      developer_contact: b.developer_contact || '',
      architect_name: b.architect_name || '',
      architect_contact: b.architect_contact || '',
      pmc_consultant_name: b.pmc_consultant_name || '',
      pmc_consultant_contact: b.pmc_consultant_contact || '',
      provisional_noc: !!b.provisional_noc,
      provisional_noc_details: b.provisional_noc_details || '',
      site_id: b.site_id || '',
      status: b.status || 'Active',
    });
    setFormErrors({});
    setIsModalOpen(true);
  };

  const handleFormSubmit = async (e) => {
    e.preventDefault();
    setFormErrors({});
    if (!formData.name) { setFormErrors({ name: 'Building name is required' }); return; }
    try {
      const payload = {
        ...formData,
        latitude: formData.latitude ? parseFloat(formData.latitude) : null,
        longitude: formData.longitude ? parseFloat(formData.longitude) : null,
        no_of_floors: formData.no_of_floors !== '' ? parseInt(formData.no_of_floors) : null,
        no_of_flats: formData.no_of_flats !== '' ? parseInt(formData.no_of_flats) : null,
        no_of_staircase: formData.no_of_staircase !== '' ? parseInt(formData.no_of_staircase) : null,
        no_of_lifts: formData.no_of_lifts !== '' ? parseInt(formData.no_of_lifts) : null,
        no_of_exits_entry: formData.no_of_exits_entry !== '' ? parseInt(formData.no_of_exits_entry) : null,
        site_id: formData.site_id ? parseInt(formData.site_id) : null,
      };
      if (editingBuilding) {
        await updateBuilding(editingBuilding.id, payload);
      } else {
        await createBuilding(payload);
      }
      setIsModalOpen(false);
      fetchBuildings({ search, status: statusFilter, page: currentPage, per_page: 1000 });
    } catch (err) {
      if (err.response?.data?.errors) setFormErrors(err.response.data.errors);
      else setFormErrors({ general: err.message || 'An error occurred.' });
    }
  };

  const handleDelete = async (id) => {
    if (window.confirm('Are you sure you want to delete this building?')) {
      try {
        await deleteBuilding(id);
        fetchBuildings({ search, status: statusFilter, page: currentPage, per_page: 1000 });
      } catch (err) { alert(err.message || 'Failed to delete building.'); }
    }
  };

  const SectionHeader = ({ icon: Icon, title, color }) => (
    <div className="flex items-center gap-2 mb-3">
      <div className={`w-7 h-7 rounded-lg flex items-center justify-center ${color}`}>
        <Icon className="w-4 h-4" />
      </div>
      <h4 className="text-xs font-bold text-gray-700 uppercase tracking-wider">{title}</h4>
    </div>
  );

  return (
    <div className="w-full">
      <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
          <h1 className="text-3xl font-extrabold text-foreground tracking-tight">Buildings</h1>
          <p className="text-gray-500 mt-1">Manage building details, safety info, and construction data.</p>
        </div>
        <button onClick={openAddModal} className="inline-flex items-center justify-center w-full sm:w-auto px-5 py-3 bg-primary text-white rounded-xl font-semibold hover:bg-primary/90 transition-all shadow-md">
          <Plus className="w-5 h-5 mr-2" />
          Add Building
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center">
          <div className="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center mr-4 text-blue-600">
            <Building2 className="w-6 h-6" />
          </div>
          <div>
            <div className="text-2xl font-bold text-gray-900">{buildings?.length || 0}</div>
            <div className="text-sm text-gray-500 font-medium">Total Buildings</div>
          </div>
        </div>
        <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center">
          <div className="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center mr-4 text-amber-600">
            <Hammer className="w-6 h-6" />
          </div>
          <div>
            <div className="text-2xl font-bold text-gray-900">{buildings?.filter(b => b.under_construction).length || 0}</div>
            <div className="text-sm text-gray-500 font-medium">Under Construction</div>
          </div>
        </div>
        <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center">
          <div className="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center mr-4 text-green-600">
            <Shield className="w-6 h-6" />
          </div>
          <div>
            <div className="text-2xl font-bold text-gray-900">{buildings?.filter(b => b.fire_safety_available).length || 0}</div>
            <div className="text-sm text-gray-500 font-medium">Fire Safety Ready</div>
          </div>
        </div>
      </div>

      <div className="bg-white p-4 rounded-xl border border-gray-100 shadow-sm mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
        <div className="w-full md:w-96 relative">
          <Search className="absolute left-3 top-3 w-5 h-5 text-gray-400" />
          <input type="text" placeholder="Search by name, city, plot..." className="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value={search} onChange={(e) => setSearch(e.target.value)} />
        </div>
        <select className="w-full md:w-auto px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value={statusFilter} onChange={(e) => setStatusFilter(e.target.value)}>
          <option value="">All Statuses</option>
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>
      </div>

      {loading ? (
        <div className="flex justify-center items-center py-20">
          <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
        </div>
      ) : buildings?.length === 0 ? (
        <div className="bg-white rounded-2xl border border-gray-100 p-12 text-center shadow-sm">
          <Info className="w-12 h-12 text-gray-400 mx-auto mb-4" />
          <h3 className="text-lg font-bold text-gray-900">No Buildings Found</h3>
          <p className="text-gray-500 mt-1">Add a new building to get started.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
          {buildings.map(b => (
            <div key={b.id} className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-all flex flex-col justify-between">
              <div className="p-6">
                <div className="flex justify-between items-start mb-4">
                  <div>
                    <h3 className="text-lg font-bold text-gray-900 leading-tight">{b.name}</h3>
                    {b.plot_no && <span className="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md">Plot: {b.plot_no}</span>}
                  </div>
                  <div className="flex flex-col items-end gap-1">
                    <span className={`px-2.5 py-1 rounded-full text-xs font-bold ${b.status === 'Active' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600'}`}>{b.status}</span>
                    {b.under_construction && <span className="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700">Under Construction</span>}
                  </div>
                </div>

                {b.address && (
                  <div className="flex items-start text-sm text-gray-600 mb-3 gap-2">
                    <MapPin className="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                    <div>
                      <p className="font-medium text-gray-800">{b.address}</p>
                      <p className="text-xs text-gray-500">{b.city && `${b.city}, `}{b.state && `${b.state} `}{b.pincode}</p>
                    </div>
                  </div>
                )}

                <div className="border-t border-gray-50 pt-3 mt-3">
                  <div className="grid grid-cols-3 gap-2 text-center">
                    {b.no_of_floors != null && <div className="bg-gray-50 rounded-lg p-2"><div className="text-lg font-bold text-gray-900">{b.no_of_floors}</div><div className="text-[10px] text-gray-500 font-medium">Floors</div></div>}
                    {b.no_of_flats != null && <div className="bg-gray-50 rounded-lg p-2"><div className="text-lg font-bold text-gray-900">{b.no_of_flats}</div><div className="text-[10px] text-gray-500 font-medium">Flats</div></div>}
                    {b.no_of_wings && <div className="bg-gray-50 rounded-lg p-2"><div className="text-lg font-bold text-gray-900">{b.no_of_wings}</div><div className="text-[10px] text-gray-500 font-medium">Wings</div></div>}
                  </div>
                  <div className="grid grid-cols-3 gap-2 text-center mt-2">
                    {b.no_of_lifts != null && <div className="bg-gray-50 rounded-lg p-2"><div className="text-lg font-bold text-gray-900">{b.no_of_lifts}</div><div className="text-[10px] text-gray-500 font-medium">Lifts</div></div>}
                    {b.no_of_staircase != null && <div className="bg-gray-50 rounded-lg p-2"><div className="text-lg font-bold text-gray-900">{b.no_of_staircase}</div><div className="text-[10px] text-gray-500 font-medium">Staircases</div></div>}
                    {b.no_of_exits_entry != null && <div className="bg-gray-50 rounded-lg p-2"><div className="text-lg font-bold text-gray-900">{b.no_of_exits_entry}</div><div className="text-[10px] text-gray-500 font-medium">Exits/Entry</div></div>}
                  </div>
                </div>

                <div className="flex items-center gap-3 mt-3 text-xs">
                  <div className={`flex items-center gap-1 ${b.fire_safety_available ? 'text-green-600 font-medium' : 'text-gray-400'}`}>
                    <Flame className="w-3.5 h-3.5" />
                    {b.fire_safety_available ? (b.fire_safety_type || 'Fire Safety') : 'No Fire Safety'}
                  </div>
                  {b.commercial_shops_available && <span className="text-purple-600 font-medium bg-purple-50 px-2 py-0.5 rounded">Commercial Shops</span>}
                  {b.provisional_noc && <span className="text-blue-600 font-medium bg-blue-50 px-2 py-0.5 rounded">NOC</span>}
                </div>

                <button onClick={() => setExpandedCard(expandedCard === b.id ? null : b.id)} className="mt-3 flex items-center gap-1 text-xs text-gray-500 hover:text-gray-700 font-medium">
                  {expandedCard === b.id ? <ChevronUp className="w-3.5 h-3.5" /> : <ChevronDown className="w-3.5 h-3.5" />}
                  {expandedCard === b.id ? 'Less details' : 'More details'}
                </button>

                {expandedCard === b.id && (
                  <div className="mt-3 space-y-3 border-t border-gray-50 pt-3">
                    {b.property_owner && (
                      <div className="flex items-center gap-2 text-xs text-gray-600"><User className="w-3.5 h-3.5 text-gray-400" /><span>Owner: {b.property_owner}</span></div>
                    )}
                    {b.site && (
                      <div className="flex items-center gap-2 text-xs text-gray-600"><MapPin className="w-3.5 h-3.5 text-gray-400" /><span>Site: {b.site.name}</span></div>
                    )}
                    {b.developer_name && (
                      <div className="flex items-center gap-2 text-xs text-gray-600"><User className="w-3.5 h-3.5 text-gray-400" /><span>Developer: {b.developer_name} {b.developer_contact && `(${b.developer_contact})`}</span></div>
                    )}
                    {b.architect_name && (
                      <div className="flex items-center gap-2 text-xs text-gray-600"><User className="w-3.5 h-3.5 text-gray-400" /><span>Architect: {b.architect_name} {b.architect_contact && `(${b.architect_contact})`}</span></div>
                    )}
                    {b.pmc_consultant_name && (
                      <div className="flex items-center gap-2 text-xs text-gray-600"><User className="w-3.5 h-3.5 text-gray-400" /><span>PMC: {b.pmc_consultant_name} {b.pmc_consultant_contact && `(${b.pmc_consultant_contact})`}</span></div>
                    )}
                    {b.provisional_noc && b.provisional_noc_details && (
                      <div className="flex items-start gap-2 text-xs text-gray-600"><FileText className="w-3.5 h-3.5 text-gray-400 mt-0.5" /><span>NOC Details: {b.provisional_noc_details}</span></div>
                    )}
                    {b.latitude && b.longitude && (
                      <div className="flex items-center gap-2 text-xs text-gray-500"><Compass className="w-3.5 h-3.5 text-gray-400" /><span>GPS: {b.latitude}, {b.longitude}</span></div>
                    )}
                  </div>
                )}
              </div>

              <div className="bg-gray-50/50 px-6 py-3 border-t border-gray-100 flex justify-end gap-2">
                <button onClick={() => openEditModal(b)} className="p-2 text-gray-600 hover:text-blue-600 hover:bg-white rounded-lg transition-colors border border-transparent hover:border-gray-100" title="Edit"><Edit className="w-4 h-4" /></button>
                <button onClick={() => handleDelete(b.id)} className="p-2 text-gray-600 hover:text-red-600 hover:bg-white rounded-lg transition-colors border border-transparent hover:border-gray-100" title="Delete"><Trash2 className="w-4 h-4" /></button>
              </div>
            </div>
          ))}
        </div>
      )}

      {isModalOpen && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50 overflow-y-auto">
          <div className="bg-white rounded-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
            <div className="flex justify-between items-center px-6 py-4 border-b border-gray-150 sticky top-0 bg-white z-10">
              <h2 className="text-xl font-bold text-gray-900">{editingBuilding ? 'Edit Building' : 'Add Building'}</h2>
              <button onClick={() => setIsModalOpen(false)} className="text-gray-400 hover:text-gray-600"><X className="w-5 h-5" /></button>
            </div>

            <form onSubmit={handleFormSubmit} className="p-6 space-y-6">
              {formErrors.general && (
                <div className="bg-red-50 text-red-700 p-3 rounded-lg text-sm">{formErrors.general}</div>
              )}

              <div>
                <h4 className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Basic Info</h4>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Building Name *</label>
                    <input type="text" name="name" value={formData.name} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required />
                    {formErrors.name && <p className="text-red-500 text-xs mt-1">{formErrors.name}</p>}
                  </div>
                  <div>
                    <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Status *</label>
                    <select name="status" value={formData.status} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                      <option value="Active">Active</option>
                      <option value="Inactive">Inactive</option>
                    </select>
                  </div>
                  <div>
                    <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Plot No</label>
                    <input type="text" name="plot_no" value={formData.plot_no} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                  <div>
                    <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Linked Site</label>
                    <select name="site_id" value={formData.site_id} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                      <option value="">None</option>
                      {sites?.map(s => <option key={s.id} value={s.id}>{s.name}</option>)}
                    </select>
                  </div>
                </div>
              </div>

              <div>
                <h4 className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Address & Location</h4>
                <div className="space-y-4">
                  <div>
                    <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Address</label>
                    <textarea name="address" value={formData.address} onChange={handleInputChange} rows="2" className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                  <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                      <label className="block text-xs font-semibold text-gray-600 mb-1">City</label>
                      <input type="text" name="city" value={formData.city} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                      <label className="block text-xs font-semibold text-gray-600 mb-1">State</label>
                      <input type="text" name="state" value={formData.state} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                      <label className="block text-xs font-semibold text-gray-600 mb-1">Country</label>
                      <input type="text" name="country" value={formData.country} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                      <label className="block text-xs font-semibold text-gray-600 mb-1">Pincode</label>
                      <input type="text" name="pincode" value={formData.pincode} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                  </div>
                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Latitude</label>
                      <input type="number" step="any" name="latitude" value={formData.latitude} onChange={handleInputChange} placeholder="e.g. 19.0760" className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                      <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Longitude</label>
                      <input type="number" step="any" name="longitude" value={formData.longitude} onChange={handleInputChange} placeholder="e.g. 72.8777" className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                  </div>
                </div>
              </div>

              <div>
                <h4 className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Building Structure</h4>
                <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                  <div>
                    <label className="block text-xs font-bold text-gray-700 uppercase mb-1">No. of Floors</label>
                    <input type="number" name="no_of_floors" value={formData.no_of_floors} onChange={handleInputChange} min="0" className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                  <div>
                    <label className="block text-xs font-bold text-gray-700 uppercase mb-1">No. of Wings</label>
                    <input type="text" name="no_of_wings" value={formData.no_of_wings} onChange={handleInputChange} placeholder="e.g. A, B, C" className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                  <div>
                    <label className="block text-xs font-bold text-gray-700 uppercase mb-1">No. of Flats</label>
                    <input type="number" name="no_of_flats" value={formData.no_of_flats} onChange={handleInputChange} min="0" className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                  <div>
                    <label className="block text-xs font-bold text-gray-700 uppercase mb-1">No. of Staircases</label>
                    <input type="number" name="no_of_staircase" value={formData.no_of_staircase} onChange={handleInputChange} min="0" className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                  <div>
                    <label className="block text-xs font-bold text-gray-700 uppercase mb-1">No. of Lifts</label>
                    <input type="number" name="no_of_lifts" value={formData.no_of_lifts} onChange={handleInputChange} min="0" className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                  <div>
                    <label className="block text-xs font-bold text-gray-700 uppercase mb-1">No. of Exits/Entry</label>
                    <input type="number" name="no_of_exits_entry" value={formData.no_of_exits_entry} onChange={handleInputChange} min="0" className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                </div>
                <div className="mt-4 flex flex-wrap gap-6">
                  <label className="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="commercial_shops_available" checked={formData.commercial_shops_available} onChange={(e) => setFormData(prev => ({ ...prev, commercial_shops_available: e.target.checked }))} className="w-4 h-4 rounded border-gray-300" />
                    <span className="text-sm font-medium text-gray-700">Commercial Shops Available</span>
                  </label>
                  <label className="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="under_construction" checked={formData.under_construction} onChange={(e) => setFormData(prev => ({ ...prev, under_construction: e.target.checked }))} className="w-4 h-4 rounded border-gray-300" />
                    <span className="text-sm font-medium text-gray-700">Under Construction</span>
                  </label>
                </div>
              </div>

              <div>
                <h4 className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Fire Safety</h4>
                <div className="space-y-4">
                  <label className="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="fire_safety_available" checked={formData.fire_safety_available} onChange={(e) => setFormData(prev => ({ ...prev, fire_safety_available: e.target.checked }))} className="w-4 h-4 rounded border-gray-300" />
                    <span className="text-sm font-medium text-gray-700">Fire Safety Available</span>
                  </label>
                  {formData.fire_safety_available && (
                    <div>
                      <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Fire Safety Type</label>
                      <input type="text" name="fire_safety_type" value={formData.fire_safety_type} onChange={handleInputChange} placeholder="e.g. Sprinkler, Hose Reel, Extinguisher" className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                  )}
                </div>
              </div>

              <div>
                <h4 className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Ownership & Construction Details</h4>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Property Owner</label>
                    <input type="text" name="property_owner" value={formData.property_owner} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                  <div>
                    <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Plot No</label>
                    <input type="text" name="plot_no" value={formData.plot_no} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                </div>
              </div>

              <div>
                <h4 className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Developer & Architect</h4>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-xs font-semibold text-gray-600 mb-1">Developer Name</label>
                    <input type="text" name="developer_name" value={formData.developer_name} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                  <div>
                    <label className="block text-xs font-semibold text-gray-600 mb-1">Developer Contact</label>
                    <input type="text" name="developer_contact" value={formData.developer_contact} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                  <div>
                    <label className="block text-xs font-semibold text-gray-600 mb-1">Architect Name</label>
                    <input type="text" name="architect_name" value={formData.architect_name} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                  <div>
                    <label className="block text-xs font-semibold text-gray-600 mb-1">Architect Contact</label>
                    <input type="text" name="architect_contact" value={formData.architect_contact} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                </div>
              </div>

              <div>
                <h4 className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">PMC Consultant</h4>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-xs font-semibold text-gray-600 mb-1">PMC Name</label>
                    <input type="text" name="pmc_consultant_name" value={formData.pmc_consultant_name} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                  <div>
                    <label className="block text-xs font-semibold text-gray-600 mb-1">PMC Contact</label>
                    <input type="text" name="pmc_consultant_contact" value={formData.pmc_consultant_contact} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                </div>
              </div>

              <div>
                <h4 className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">NOC</h4>
                <div className="space-y-4">
                  <label className="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="provisional_noc" checked={formData.provisional_noc} onChange={(e) => setFormData(prev => ({ ...prev, provisional_noc: e.target.checked }))} className="w-4 h-4 rounded border-gray-300" />
                    <span className="text-sm font-medium text-gray-700">Provisional NOC Available</span>
                  </label>
                  {formData.provisional_noc && (
                    <div>
                      <label className="block text-xs font-bold text-gray-700 uppercase mb-1">NOC Details</label>
                      <textarea name="provisional_noc_details" value={formData.provisional_noc_details} onChange={handleInputChange} rows="2" className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter NOC details..." />
                    </div>
                  )}
                </div>
              </div>

              <div className="flex justify-end gap-3 pt-4 border-t border-gray-150">
                <button type="button" onClick={() => setIsModalOpen(false)} className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition-colors">Cancel</button>
                <button type="submit" className="px-5 py-2 bg-primary text-white rounded-lg text-sm hover:bg-primary/90 transition-colors font-semibold">
                  {editingBuilding ? 'Save Changes' : 'Create Building'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
