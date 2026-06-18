import React, { useEffect, useState } from 'react';
import { useSiteStore } from '../../store/siteStore';
import { useEmployeeStore } from '../../store/employeeStore';
import { 
  Plus, Edit, Trash2, Search, MapPin, Phone, Mail, User, ShieldAlert,
  Building, Compass, CheckCircle2, XCircle, Info, ChevronLeft, ChevronRight, X
} from 'lucide-react';
import LocationPicker from '../../components/Map/LocationPicker';

export default function Sites() {
  const { 
    items: sites, 
    loading, 
    fetchItems: fetchSites, 
    createItem: createSite, 
    updateItem: updateSite, 
    deleteItem: deleteSite 
  } = useSiteStore();

  const { 
    employees, 
    fetchEmployees 
  } = useEmployeeStore();

  // Search & Filter
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [currentPage, setCurrentPage] = useState(1);
  const [perPage] = useState(10);

  // Modals
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingSite, setEditingSite] = useState(null);
  const [formData, setFormData] = useState({
    name: '',
    code: '',
    client_details: '',
    address: '',
    city: '',
    state: '',
    country: 'India',
    pincode: '',
    contact_person: '',
    phone: '',
    email: '',
    latitude: '',
    longitude: '',
    allowed_radius: 100,
    geo_fencing_enabled: false,
    status: 'Active',
    site_manager_id: ''
  });

  const [formErrors, setFormErrors] = useState({});

  useEffect(() => {
    fetchSites({ search, status: statusFilter, page: currentPage, per_page: perPage });
    fetchEmployees({ per_page: 100 });
  }, [search, statusFilter, currentPage]);

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
    if (formErrors[name]) {
      setFormErrors(prev => ({ ...prev, [name]: null }));
    }
  };

  const openAddModal = () => {
    setEditingSite(null);
    setFormData({
      name: '',
      code: '',
      client_details: '',
      address: '',
      city: '',
      state: '',
      country: 'India',
      pincode: '',
      contact_person: '',
      phone: '',
      email: '',
      latitude: '',
      longitude: '',
      allowed_radius: 100,
      geo_fencing_enabled: false,
      status: 'Active',
      site_manager_id: ''
    });
    setFormErrors({});
    setIsModalOpen(true);
  };

  const openEditModal = (site) => {
    setEditingSite(site);
    setFormData({
      name: site.name || '',
      code: site.code || '',
      client_details: site.client_details || '',
      address: site.address || '',
      city: site.city || '',
      state: site.state || '',
      country: site.country || 'India',
      pincode: site.pincode || '',
      contact_person: site.contact_person || '',
      phone: site.phone || '',
      email: site.email || '',
      latitude: site.latitude || '',
      longitude: site.longitude || '',
      allowed_radius: site.allowed_radius || 100,
      geo_fencing_enabled: !!site.geo_fencing_enabled,
      status: site.status || 'Active',
      site_manager_id: site.site_manager_id || ''
    });
    setFormErrors({});
    setIsModalOpen(true);
  };

  const handleFormSubmit = async (e) => {
    e.preventDefault();
    setFormErrors({});

    // Simple client-side validation
    const errors = {};
    if (!formData.name) errors.name = 'Site name is required';
    if (!formData.status) errors.status = 'Status is required';

    if (Object.keys(errors).length > 0) {
      setFormErrors(errors);
      return;
    }

    try {
      const payload = {
        ...formData,
        latitude: formData.latitude ? parseFloat(formData.latitude) : null,
        longitude: formData.longitude ? parseFloat(formData.longitude) : null,
        allowed_radius: formData.allowed_radius ? parseInt(formData.allowed_radius) : 100,
        geo_fencing_enabled: !!formData.geo_fencing_enabled,
        site_manager_id: formData.site_manager_id ? parseInt(formData.site_manager_id) : null
      };

      if (editingSite) {
        await updateSite(editingSite.id, payload);
      } else {
        await createSite(payload);
      }
      setIsModalOpen(false);
      fetchSites({ search, status: statusFilter, page: currentPage, per_page: perPage });
    } catch (err) {
      if (err.response?.data?.errors) {
        setFormErrors(err.response.data.errors);
      } else {
        setFormErrors({ general: err.message || 'An error occurred.' });
      }
    }
  };

  const handleDelete = async (id) => {
    if (window.confirm('Are you sure you want to delete this site?')) {
      try {
        await deleteSite(id);
        fetchSites({ search, status: statusFilter, page: currentPage, per_page: perPage });
      } catch (err) {
        alert(err.message || 'Failed to delete site.');
      }
    }
  };

  return (
    <div className="w-full">
      {/* Header section */}
      <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
          <h1 className="text-3xl font-extrabold text-[#0B1B36] tracking-tight">Construction Sites</h1>
          <p className="text-gray-500 mt-1">Manage project locations, contact persons, geographic coordinates, and managers.</p>
        </div>
        <button 
          onClick={openAddModal}
          className="inline-flex items-center justify-center w-full sm:w-auto px-5 py-3 bg-[#1a56db] text-white rounded-xl font-semibold hover:bg-[#1546b5] transition-all shadow-md shadow-blue-200"
        >
          <Plus className="w-5 h-5 mr-2" />
          Add New Site
        </button>
      </div>

      {/* Stats bar */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center">
          <div className="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center mr-4 text-blue-600">
            <Building className="w-6 h-6" />
          </div>
          <div>
            <div className="text-2xl font-bold text-gray-900">{sites?.length || 0}</div>
            <div className="text-sm text-gray-500 font-medium">Total Managed Sites</div>
          </div>
        </div>
        <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center">
          <div className="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center mr-4 text-green-600">
            <CheckCircle2 className="w-6 h-6" />
          </div>
          <div>
            <div className="text-2xl font-bold text-gray-900">
              {sites?.filter(s => s.status === 'Active').length || 0}
            </div>
            <div className="text-sm text-gray-500 font-medium">Active Sites</div>
          </div>
        </div>
        <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center">
          <div className="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center mr-4 text-red-600">
            <XCircle className="w-6 h-6" />
          </div>
          <div>
            <div className="text-2xl font-bold text-gray-900">
              {sites?.filter(s => s.status === 'Inactive').length || 0}
            </div>
            <div className="text-sm text-gray-500 font-medium">Inactive Sites</div>
          </div>
        </div>
      </div>

      {/* Filters & search */}
      <div className="bg-white p-4 rounded-xl border border-gray-100 shadow-sm mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
        <div className="w-full md:w-96 relative">
          <Search className="absolute left-3 top-3 w-5 h-5 text-gray-400" />
          <input 
            type="text" 
            placeholder="Search by name or code..."
            className="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
          />
        </div>
        <div className="flex gap-3 w-full md:w-auto">
          <select 
            className="w-full md:w-auto px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            value={statusFilter}
            onChange={(e) => setStatusFilter(e.target.value)}
          >
            <option value="">All Statuses</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
          </select>
        </div>
      </div>

      {/* Sites Grid */}
      {loading ? (
        <div className="flex justify-center items-center py-20">
          <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
        </div>
      ) : sites?.length === 0 ? (
        <div className="bg-white rounded-2xl border border-gray-100 p-12 text-center shadow-sm">
          <Info className="w-12 h-12 text-gray-400 mx-auto mb-4" />
          <h3 className="text-lg font-bold text-gray-900">No Sites Found</h3>
          <p className="text-gray-500 mt-1">Try adjusting your filters or search criteria, or add a new site.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
          {sites.map(site => (
            <div key={site.id} className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-all flex flex-col justify-between">
              <div className="p-6">
                {/* Header info */}
                <div className="flex justify-between items-start mb-4">
                  <div>
                    <span className="inline-block text-[10px] font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-md mb-2">
                      {site.code || 'NO-CODE'}
                    </span>
                    <h3 className="text-lg font-bold text-gray-900 leading-tight">{site.name}</h3>
                  </div>
                  <span className={`px-2.5 py-1 rounded-full text-xs font-bold ${
                    site.status === 'Active' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600'
                  }`}>
                    {site.status}
                  </span>
                </div>

                {/* Location */}
                <div className="flex items-start text-sm text-gray-600 mb-3 gap-2">
                  <MapPin className="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                  <div>
                    <p className="font-medium text-gray-800">{site.address || 'No Address'}</p>
                    <p className="text-xs text-gray-500">{site.city && `${site.city}, `}{site.state && `${site.state} `}{site.pincode}</p>
                  </div>
                </div>

                {/* Contact person */}
                <div className="border-t border-gray-50 pt-3 mt-3 space-y-2">
                  <div className="flex items-center text-xs text-gray-600 gap-2">
                    <User className="w-3.5 h-3.5 text-gray-400" />
                    <span className="font-medium text-gray-700">Contact: {site.contact_person || 'N/A'}</span>
                  </div>
                  {site.phone && (
                    <div className="flex items-center text-xs text-gray-500 gap-2">
                      <Phone className="w-3.5 h-3.5 text-gray-400" />
                      <span>{site.phone}</span>
                    </div>
                  )}
                  {site.email && (
                    <div className="flex items-center text-xs text-gray-500 gap-2">
                      <Mail className="w-3.5 h-3.5 text-gray-400" />
                      <span className="truncate">{site.email}</span>
                    </div>
                  )}
                  {site.latitude && site.longitude && (
                    <div className="flex items-center text-xs text-gray-500 gap-2">
                      <Compass className="w-3.5 h-3.5 text-gray-400" />
                      <span>GPS: {site.latitude}, {site.longitude}</span>
                    </div>
                  )}
                  {site.allowed_radius && (
                    <div className="flex items-center text-xs text-gray-500 gap-2">
                      <span className="w-3.5 h-3.5 inline-flex items-center justify-center text-gray-400 font-bold text-[10px]">R</span>
                      <span>Radius: {site.allowed_radius}m</span>
                    </div>
                  )}
                  <div className="flex items-center text-xs gap-2">
                    <ShieldAlert className={`w-3.5 h-3.5 ${site.geo_fencing_enabled ? 'text-green-500' : 'text-gray-400'}`} />
                    <span className={site.geo_fencing_enabled ? 'text-green-600 font-medium' : 'text-gray-500'}>
                      {site.geo_fencing_enabled ? 'Geo-Fencing Active' : 'Geo-Fencing Disabled'}
                    </span>
                  </div>
                </div>
              </div>

              {/* Card Footer with Manager and Actions */}
              <div className="bg-gray-50/50 px-6 py-4 border-t border-gray-100 flex justify-between items-center">
                <div className="flex items-center gap-2">
                  <div className="w-7 h-7 rounded-full bg-blue-100 text-blue-800 flex items-center justify-center text-xs font-bold">
                    {site.site_manager?.name ? site.site_manager.name.substring(0, 2).toUpperCase() : 'M'}
                  </div>
                  <div className="text-xs">
                    <p className="text-gray-400 font-semibold">SITE MANAGER</p>
                    <p className="font-bold text-gray-800 truncate max-w-[120px]">
                      {site.site_manager?.name || 'Unassigned'}
                    </p>
                  </div>
                </div>
                <div className="flex gap-2">
                  <button 
                    onClick={() => openEditModal(site)}
                    className="p-2 text-gray-600 hover:text-blue-600 hover:bg-white rounded-lg transition-colors border border-transparent hover:border-gray-100"
                    title="Edit Site"
                  >
                    <Edit className="w-4 h-4" />
                  </button>
                  <button 
                    onClick={() => handleDelete(site.id)}
                    className="p-2 text-gray-600 hover:text-red-600 hover:bg-white rounded-lg transition-colors border border-transparent hover:border-gray-100"
                    title="Delete Site"
                  >
                    <Trash2 className="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Modal - Add / Edit Site */}
      {isModalOpen && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50 overflow-y-auto">
          <div className="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
            <div className="flex justify-between items-center px-6 py-4 border-b border-gray-150">
              <h2 className="text-xl font-bold text-gray-900">
                {editingSite ? 'Edit Construction Site' : 'Add Construction Site'}
              </h2>
              <button onClick={() => setIsModalOpen(false)} className="text-gray-400 hover:text-gray-600">
                <X className="w-5 h-5" />
              </button>
            </div>
            
            <form onSubmit={handleFormSubmit} className="p-6 space-y-6">
              {formErrors.general && (
                <div className="bg-red-50 text-red-700 p-3 rounded-lg text-sm flex items-center">
                  <ShieldAlert className="w-5 h-5 mr-2" />
                  {formErrors.general}
                </div>
              )}

              {/* Basic Fields */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Site Name *</label>
                  <input 
                    type="text" 
                    name="name"
                    value={formData.name}
                    onChange={handleInputChange}
                    className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                  />
                  {formErrors.name && <p className="text-red-500 text-xs mt-1">{formErrors.name}</p>}
                </div>
                <div>
                  <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Site Code</label>
                  <input 
                    type="text" 
                    name="code"
                    value={formData.code}
                    onChange={handleInputChange}
                    className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                  {formErrors.code && <p className="text-red-500 text-xs mt-1">{formErrors.code}</p>}
                </div>
              </div>

              {/* Client & Status & Manager */}
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div className="md:col-span-1">
                  <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Status *</label>
                  <select
                    name="status"
                    value={formData.status}
                    onChange={handleInputChange}
                    className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                  </select>
                </div>
                <div className="md:col-span-1">
                  <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Site Manager</label>
                  <select
                    name="site_manager_id"
                    value={formData.site_manager_id}
                    onChange={handleInputChange}
                    className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="">Select Manager</option>
                    {employees?.map(emp => (
                      <option key={emp.id} value={emp.id}>{emp.full_name || emp.name}</option>
                    ))}
                  </select>
                </div>
                <div className="md:col-span-1">
                  <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Client Details</label>
                  <input 
                    type="text" 
                    name="client_details"
                    value={formData.client_details}
                    onChange={handleInputChange}
                    className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
              </div>

              {/* Contact Information */}
              <div className="bg-gray-50/50 p-4 rounded-xl border border-gray-100 space-y-4">
                <h3 className="text-sm font-bold text-gray-800">Contact Details</h3>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <label className="block text-xs font-semibold text-gray-600 mb-1">Contact Person</label>
                    <input 
                      type="text" 
                      name="contact_person"
                      value={formData.contact_person}
                      onChange={handleInputChange}
                      className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-semibold text-gray-600 mb-1">Phone Number</label>
                    <input 
                      type="text" 
                      name="phone"
                      value={formData.phone}
                      onChange={handleInputChange}
                      className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-semibold text-gray-600 mb-1">Email</label>
                    <input 
                      type="email" 
                      name="email"
                      value={formData.email}
                      onChange={handleInputChange}
                      className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                    />
                  </div>
                </div>
              </div>

              {/* Geographic Coordinates */}
              <div className="bg-gray-50/50 p-4 rounded-xl border border-gray-100 space-y-4">
                <div className="flex justify-between items-center mb-2">
                  <h3 className="text-sm font-bold text-gray-800">Geographic & Fencing Settings</h3>
                  <label className="flex items-center cursor-pointer">
                    <div className="relative">
                      <input 
                        type="checkbox" 
                        name="geo_fencing_enabled"
                        checked={formData.geo_fencing_enabled}
                        onChange={(e) => setFormData(prev => ({ ...prev, geo_fencing_enabled: e.target.checked }))}
                        className="sr-only" 
                      />
                      <div className={`block w-10 h-6 rounded-full transition-colors ${formData.geo_fencing_enabled ? 'bg-blue-600' : 'bg-gray-300'}`}></div>
                      <div className={`dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform ${formData.geo_fencing_enabled ? 'transform translate-x-4' : ''}`}></div>
                    </div>
                    <div className="ml-3 text-xs font-bold text-gray-700">Enable Geo-Fencing</div>
                  </label>
                </div>
                
                <div className="mb-4">
                  <p className="text-xs text-gray-500 mb-2">Click on the map to set the exact coordinates of the site.</p>
                  <LocationPicker 
                    latitude={formData.latitude} 
                    longitude={formData.longitude} 
                    onLocationChange={(lat, lng) => setFormData(prev => ({ ...prev, latitude: lat, longitude: lng }))}
                  />
                </div>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Latitude</label>
                    <input 
                      type="number" 
                      step="any"
                      name="latitude"
                      value={formData.latitude}
                      onChange={handleInputChange}
                      placeholder="e.g. 19.0760"
                      className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                    />
                    {formErrors.latitude && <p className="text-red-500 text-xs mt-1">{formErrors.latitude}</p>}
                  </div>
                  <div>
                    <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Longitude</label>
                    <input 
                      type="number" 
                      step="any"
                      name="longitude"
                      value={formData.longitude}
                      onChange={handleInputChange}
                      placeholder="e.g. 72.8777"
                      className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                    />
                    {formErrors.longitude && <p className="text-red-500 text-xs mt-1">{formErrors.longitude}</p>}
                  </div>
                  <div>
                    <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Allowed Radius (m)</label>
                    <input 
                      type="number" 
                      name="allowed_radius"
                      value={formData.allowed_radius}
                      onChange={handleInputChange}
                      placeholder="e.g. 100"
                      className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                    />
                    <p className="text-[10px] text-gray-400 mt-1">Default: 100m.</p>
                    {formErrors.allowed_radius && <p className="text-red-500 text-xs mt-1">{formErrors.allowed_radius}</p>}
                  </div>
                </div>
              </div>

              {/* Address Fields */}
              <div className="space-y-4">
                <div>
                  <label className="block text-xs font-bold text-gray-700 uppercase mb-1">Address</label>
                  <textarea 
                    name="address"
                    value={formData.address}
                    onChange={handleInputChange}
                    rows="2"
                    className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                  ></textarea>
                </div>
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                  <div>
                    <label className="block text-xs font-semibold text-gray-600 mb-1">City</label>
                    <input 
                      type="text" 
                      name="city"
                      value={formData.city}
                      onChange={handleInputChange}
                      className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-semibold text-gray-600 mb-1">State</label>
                    <input 
                      type="text" 
                      name="state"
                      value={formData.state}
                      onChange={handleInputChange}
                      className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-semibold text-gray-600 mb-1">Country</label>
                    <input 
                      type="text" 
                      name="country"
                      value={formData.country}
                      onChange={handleInputChange}
                      className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-semibold text-gray-600 mb-1">Pincode</label>
                    <input 
                      type="text" 
                      name="pincode"
                      value={formData.pincode}
                      onChange={handleInputChange}
                      className="w-full px-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                </div>
              </div>

              {/* Actions */}
              <div className="flex justify-end gap-3 pt-4 border-t border-gray-150">
                <button 
                  type="button"
                  onClick={() => setIsModalOpen(false)}
                  className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition-colors"
                >
                  Cancel
                </button>
                <button 
                  type="submit"
                  className="px-5 py-2 bg-[#1a56db] text-white rounded-lg text-sm hover:bg-[#1546b5] transition-colors font-semibold"
                >
                  {editingSite ? 'Save Changes' : 'Create Site'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
