import React, { useEffect, useState } from 'react';
import { useSalaryStructureStore } from '../../store/salaryStructureStore';
import { useEmployeesStore } from '../../store/employeesStore';
import { Plus, Edit2, Trash2, X } from 'lucide-react';

const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-GB') : '--';
const toDateInput = (d) => d ? new Date(d).toISOString().split('T')[0] : '';

const SalaryStructuresPage = () => {
  const { items: structures, loading, error, fetchItems, createItem, updateItem, deleteItem } = useSalaryStructureStore();
  const { items: employees, fetchItems: fetchEmployees } = useEmployeesStore();
  const [showModal, setShowModal] = useState(false);
  const [editingId, setEditingId] = useState(null);
  const [formData, setFormData] = useState({
    employee_id: '',
    basic_salary: '',
    hra: '',
    conveyance: '',
    medical_allowance: '',
    special_allowance: '',
    site_allowance: '',
    travel_allowance: '',
    food_allowance: '',
    other_allowance: '',
    effective_from: '',
    status: 'active'
  });

  useEffect(() => {
    fetchItems();
    fetchEmployees({ per_page: 1000 });
  }, [fetchItems, fetchEmployees]);

  const handleOpenModal = (structure = null) => {
    if (structure) {
      setEditingId(structure.id);
      setFormData({
        employee_id: structure.employee_id || '',
        basic_salary: structure.basic_salary || '',
        hra: structure.hra || '',
        conveyance: structure.conveyance || '',
        medical_allowance: structure.medical_allowance || '',
        special_allowance: structure.special_allowance || '',
        site_allowance: structure.site_allowance || '',
        travel_allowance: structure.travel_allowance || '',
        food_allowance: structure.food_allowance || '',
        other_allowance: structure.other_allowance || '',
        effective_from: toDateInput(structure.effective_from),
        status: structure.status || 'active'
      });
    } else {
      setEditingId(null);
      setFormData({
        employee_id: '', basic_salary: '', hra: '', conveyance: '', medical_allowance: '',
        special_allowance: '', site_allowance: '', travel_allowance: '', food_allowance: '',
        other_allowance: '', effective_from: '', status: 'active'
      });
    }
    setShowModal(true);
  };

  const handleCloseModal = () => {
    setShowModal(false);
    setEditingId(null);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    // Map empty strings to 0 and rename other_allowance to other_earnings
    const payload = {
      ...formData,
      basic_salary: formData.basic_salary || 0,
      hra: formData.hra || 0,
      conveyance: formData.conveyance || 0,
      medical_allowance: formData.medical_allowance || 0,
      special_allowance: formData.special_allowance || 0,
      site_allowance: formData.site_allowance || 0,
      travel_allowance: formData.travel_allowance || 0,
      food_allowance: formData.food_allowance || 0,
      other_earnings: formData.other_allowance || 0,
    };
    
    // Remove the old other_allowance key so it doesn't get sent unnecessarily
    delete payload.other_allowance;

    if (editingId) {
      await updateItem(editingId, payload);
    } else {
      await createItem(payload);
    }
    fetchItems();
    handleCloseModal();
  };

  if (loading && !showModal) return <div className="p-6">Loading...</div>;
  if (error) return <div className="p-6 text-red-600">Error loading structures.</div>;

  return (
    <div className="p-6 max-w-7xl mx-auto">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Salary Structures</h1>
        <button onClick={() => handleOpenModal()} className="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg flex items-center shadow-sm transition-colors">
          <Plus className="w-4 h-4 mr-2" /> Add Structure
        </button>
      </div>

      <div className="bg-white rounded-lg shadow overflow-hidden">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Basic Salary</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Effective From</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {structures.map((s) => (
              <tr key={s.id}>
                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {s.employee ? `${s.employee.full_name || s.employee.first_name}` : `EMP-${s.employee_id}`}
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₹{s.basic_salary}</td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{fmtDate(s.effective_from)}</td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <span className={`px-2 py-1 rounded-full text-xs font-semibold ${s.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}`}>
                    {s.status}
                  </span>
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button onClick={() => handleOpenModal(s)} className="text-blue-600 hover:text-blue-900 mr-3"><Edit2 className="w-4 h-4" /></button>
                  <button onClick={() => { if (window.confirm('Are you sure?')) deleteItem(s.id); }} className="text-red-600 hover:text-red-900"><Trash2 className="w-4 h-4" /></button>
                </td>
              </tr>
            ))}
            {structures.length === 0 && (
              <tr>
                <td colSpan="5" className="px-6 py-4 text-center text-sm text-gray-500">No salary structures found.</td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      {showModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl shadow-lg w-full max-w-3xl max-h-[90vh] overflow-y-auto">
            <div className="flex justify-between items-center p-6 border-b sticky top-0 bg-white">
              <h2 className="text-xl font-bold text-gray-900">{editingId ? 'Edit Salary Structure' : 'Add Salary Structure'}</h2>
              <button onClick={handleCloseModal} className="text-gray-400 hover:text-gray-600">
                <X className="w-6 h-6" />
              </button>
            </div>

            <form onSubmit={handleSubmit} className="p-6">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="md:col-span-2">
                  <label className="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                  <select
                    required
                    className="w-full border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500"
                    value={formData.employee_id}
                    onChange={(e) => setFormData({ ...formData, employee_id: e.target.value })}
                  >
                    <option value="">Select an Employee</option>
                    {employees?.map(emp => (
                      <option key={emp.id} value={emp.id}>{emp.full_name || emp.first_name} (EMP-{emp.id})</option>
                    ))}
                  </select>
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Basic Salary *</label>
                  <input type="number" step="0.01" required className="w-full border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500"
                    value={formData.basic_salary} onChange={(e) => setFormData({ ...formData, basic_salary: e.target.value })} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">HRA</label>
                  <input type="number" step="0.01" className="w-full border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500"
                    value={formData.hra} onChange={(e) => setFormData({ ...formData, hra: e.target.value })} />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Conveyance Allowance</label>
                  <input type="number" step="0.01" className="w-full border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500"
                    value={formData.conveyance} onChange={(e) => setFormData({ ...formData, conveyance: e.target.value })} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Medical Allowance</label>
                  <input type="number" step="0.01" className="w-full border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500"
                    value={formData.medical_allowance} onChange={(e) => setFormData({ ...formData, medical_allowance: e.target.value })} />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Site Allowance</label>
                  <input type="number" step="0.01" className="w-full border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500"
                    value={formData.site_allowance} onChange={(e) => setFormData({ ...formData, site_allowance: e.target.value })} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Travel Allowance</label>
                  <input type="number" step="0.01" className="w-full border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500"
                    value={formData.travel_allowance} onChange={(e) => setFormData({ ...formData, travel_allowance: e.target.value })} />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Effective From *</label>
                  <input type="date" required className="w-full border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500"
                    value={formData.effective_from} onChange={(e) => setFormData({ ...formData, effective_from: e.target.value })} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
                  <select className="w-full border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500"
                    value={formData.status} onChange={(e) => setFormData({ ...formData, status: e.target.value })}>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </div>
              </div>

              <div className="mt-8 flex justify-end gap-3 pt-6 border-t">
                <button type="button" onClick={handleCloseModal} className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" className="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600">
                  {editingId ? 'Update Structure' : 'Save Structure'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};

export default SalaryStructuresPage;
