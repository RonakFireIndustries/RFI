import React, { useEffect, useMemo, useState } from 'react';
import { useSalaryStructureStore } from '../../store/salaryStructureStore';
import { useEmployeesStore } from '../../store/employeesStore';
import { Plus, Edit2, Trash2, X } from 'lucide-react';

const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-GB') : '--';
const toDateInput = (d) => d ? new Date(d).toISOString().split('T')[0] : '';

// Statutory rule, mirrored from App\Support\StatutoryDeductions on the backend.
// PF  = 12% of Basic, capped at the EPF wage ceiling of ₹15,000 (max ₹1,800).
// ESIC = 0.75% of monthly gross, only while gross stays at or below ₹21,000,
//        rounded up to the next rupee.
const PF_RATE = 0.12;
const PF_WAGE_CEILING = 15000;
const ESIC_RATE = 0.0075;
const ESIC_WAGE_CEILING = 21000;

const num = (value) => {
  const parsed = parseFloat(value);
  return Number.isFinite(parsed) ? parsed : 0;
};

const EMPTY_FORM = {
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
  auto_statutory: true,
  pf_deduction: '',
  esic_deduction: '',
  professional_tax: '',
  tds: '',
  other_deductions: '',
  effective_from: '',
  status: 'active',
};

const monthlyGross = (form) => num(form.basic_salary) + num(form.hra) + num(form.conveyance)
  + num(form.medical_allowance) + num(form.special_allowance) + num(form.site_allowance)
  + num(form.travel_allowance) + num(form.food_allowance) + num(form.other_allowance);

const autoPf = (form) => Math.round(Math.min(num(form.basic_salary), PF_WAGE_CEILING) * PF_RATE * 100) / 100;

const autoEsic = (form) => {
  const gross = monthlyGross(form);
  if (gross <= 0 || gross > ESIC_WAGE_CEILING) return 0;
  return Math.ceil(gross * ESIC_RATE);
};

const SalaryStructuresPage = () => {
  const { items: structures, loading, saving, error, fetchItems, createItem, updateItem, deleteItem } = useSalaryStructureStore();
  const { items: employees, fetchItems: fetchEmployees } = useEmployeesStore();
  const [showModal, setShowModal] = useState(false);
  const [editingId, setEditingId] = useState(null);
  const [formData, setFormData] = useState({ ...EMPTY_FORM });

  useEffect(() => {
    fetchItems();
    fetchEmployees({ per_page: 1000 });
  }, [fetchItems, fetchEmployees]);

  const computedPf = useMemo(() => autoPf(formData), [formData]);
  const computedEsic = useMemo(() => autoEsic(formData), [formData]);
  const gross = useMemo(() => monthlyGross(formData), [formData]);

  const handleOpenModal = (structure = null) => {
    if (structure) {
      setEditingId(structure.id);
      setFormData({
        employee_id: structure.employee_id || '',
        basic_salary: structure.basic_salary ?? '',
        hra: structure.hra ?? '',
        conveyance: structure.conveyance ?? '',
        medical_allowance: structure.medical_allowance ?? '',
        special_allowance: structure.special_allowance ?? '',
        site_allowance: structure.site_allowance ?? '',
        travel_allowance: structure.travel_allowance ?? '',
        food_allowance: structure.food_allowance ?? '',
        other_allowance: structure.other_earnings ?? '',
        // Old rows predate the column; treat a missing flag as "auto".
        auto_statutory: structure.auto_statutory === undefined || structure.auto_statutory === null
          ? true
          : !!structure.auto_statutory,
        pf_deduction: structure.pf_deduction ?? '',
        esic_deduction: structure.esic_deduction ?? '',
        professional_tax: structure.professional_tax ?? '',
        tds: structure.tds ?? '',
        other_deductions: structure.other_deductions ?? '',
        effective_from: toDateInput(structure.effective_from),
        status: structure.status || 'active',
      });
    } else {
      setEditingId(null);
      setFormData({ ...EMPTY_FORM });
    }
    setShowModal(true);
  };

  const handleCloseModal = () => {
    setShowModal(false);
    setEditingId(null);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (saving) return;

    const auto = !!formData.auto_statutory;

    // Map empty strings to 0, rename other_allowance to other_earnings, and let
    // the backend own the statutory numbers when auto-calc is on.
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
      auto_statutory: auto,
      pf_deduction: auto ? computedPf : (formData.pf_deduction || 0),
      esic_deduction: auto ? computedEsic : (formData.esic_deduction || 0),
      professional_tax: formData.professional_tax || 0,
      tds: formData.tds || 0,
      other_deductions: formData.other_deductions || 0,
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

  const inputClass = 'w-full border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500';

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
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PF</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ESIC</th>
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
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₹{s.pf_deduction ?? 0}</td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₹{s.esic_deduction ?? 0}</td>
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
                <td colSpan="7" className="px-6 py-4 text-center text-sm text-gray-500">No salary structures found.</td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      {showModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl shadow-lg w-full max-w-3xl max-h-[90vh] overflow-y-auto">
            <div className="flex justify-between items-center p-6 border-b sticky top-0 bg-white z-10">
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
                    className={inputClass}
                    value={formData.employee_id}
                    onChange={(e) => setFormData({ ...formData, employee_id: e.target.value })}
                  >
                    <option value="">Select an Employee</option>
                    {employees?.map(emp => (
                      <option key={emp.id} value={emp.id}>{emp.full_name || emp.first_name} (EMP-{emp.id})</option>
                    ))}
                  </select>
                </div>
              </div>

              {/* Earnings */}
              <h3 className="mt-8 mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Earnings (monthly)</h3>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Basic Salary *</label>
                  <input type="number" step="0.01" required className={inputClass}
                    value={formData.basic_salary} onChange={(e) => setFormData({ ...formData, basic_salary: e.target.value })} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">HRA</label>
                  <input type="number" step="0.01" className={inputClass}
                    value={formData.hra} onChange={(e) => setFormData({ ...formData, hra: e.target.value })} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Conveyance Allowance</label>
                  <input type="number" step="0.01" className={inputClass}
                    value={formData.conveyance} onChange={(e) => setFormData({ ...formData, conveyance: e.target.value })} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Medical Allowance</label>
                  <input type="number" step="0.01" className={inputClass}
                    value={formData.medical_allowance} onChange={(e) => setFormData({ ...formData, medical_allowance: e.target.value })} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Special Allowance</label>
                  <input type="number" step="0.01" className={inputClass}
                    value={formData.special_allowance} onChange={(e) => setFormData({ ...formData, special_allowance: e.target.value })} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Site Allowance</label>
                  <input type="number" step="0.01" className={inputClass}
                    value={formData.site_allowance} onChange={(e) => setFormData({ ...formData, site_allowance: e.target.value })} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Travel Allowance</label>
                  <input type="number" step="0.01" className={inputClass}
                    value={formData.travel_allowance} onChange={(e) => setFormData({ ...formData, travel_allowance: e.target.value })} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Food Allowance</label>
                  <input type="number" step="0.01" className={inputClass}
                    value={formData.food_allowance} onChange={(e) => setFormData({ ...formData, food_allowance: e.target.value })} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Other Earnings</label>
                  <input type="number" step="0.01" className={inputClass}
                    value={formData.other_allowance} onChange={(e) => setFormData({ ...formData, other_allowance: e.target.value })} />
                </div>
              </div>

              <div className="mt-4 text-sm text-gray-500">
                Monthly gross: <span className="font-semibold text-gray-700">₹{gross.toLocaleString('en-IN')}</span>
              </div>

              {/* Deductions */}
              <h3 className="mt-8 mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Deductions (monthly)</h3>

              <label className="flex items-start gap-2 mb-4 text-sm text-gray-700">
                <input
                  type="checkbox"
                  className="mt-0.5 rounded border-gray-300 text-orange-500 focus:ring-orange-500"
                  checked={!!formData.auto_statutory}
                  onChange={(e) => setFormData({ ...formData, auto_statutory: e.target.checked })}
                />
                <span>
                  Calculate PF &amp; ESIC automatically
                  <span className="block text-xs text-gray-500">
                    PF = 12% of Basic (max ₹1,800 at the ₹15,000 ceiling). ESIC = 0.75% of gross while gross ≤ ₹21,000.
                    Untick to enter your own amounts.
                  </span>
                </span>
              </label>

              <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Provident Fund (PF)</label>
                  <input
                    type="number"
                    step="0.01"
                    readOnly={!!formData.auto_statutory}
                    className={`${inputClass} ${formData.auto_statutory ? 'bg-gray-100 text-gray-500' : ''}`}
                    value={formData.auto_statutory ? computedPf : formData.pf_deduction}
                    onChange={(e) => setFormData({ ...formData, pf_deduction: e.target.value })}
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">ESIC</label>
                  <input
                    type="number"
                    step="0.01"
                    readOnly={!!formData.auto_statutory}
                    className={`${inputClass} ${formData.auto_statutory ? 'bg-gray-100 text-gray-500' : ''}`}
                    value={formData.auto_statutory ? computedEsic : formData.esic_deduction}
                    onChange={(e) => setFormData({ ...formData, esic_deduction: e.target.value })}
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Professional Tax</label>
                  <input type="number" step="0.01" className={inputClass}
                    value={formData.professional_tax} onChange={(e) => setFormData({ ...formData, professional_tax: e.target.value })} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">TDS</label>
                  <input type="number" step="0.01" className={inputClass}
                    value={formData.tds} onChange={(e) => setFormData({ ...formData, tds: e.target.value })} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Other Deductions</label>
                  <input type="number" step="0.01" className={inputClass}
                    value={formData.other_deductions} onChange={(e) => setFormData({ ...formData, other_deductions: e.target.value })} />
                </div>
              </div>

              <h3 className="mt-8 mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Validity</h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Effective From *</label>
                  <input type="date" required className={inputClass}
                    value={formData.effective_from} onChange={(e) => setFormData({ ...formData, effective_from: e.target.value })} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
                  <select className={inputClass}
                    value={formData.status} onChange={(e) => setFormData({ ...formData, status: e.target.value })}>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </div>
              </div>

              <div className="mt-8 flex justify-end gap-3 pt-6 border-t">
                <button type="button" onClick={handleCloseModal} disabled={saving} className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 disabled:opacity-60 disabled:cursor-not-allowed">Cancel</button>
                <button type="submit" disabled={saving} className="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 disabled:bg-gray-300 disabled:cursor-not-allowed">
                  {saving ? 'Saving...' : (editingId ? 'Update Structure' : 'Save Structure')}
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
