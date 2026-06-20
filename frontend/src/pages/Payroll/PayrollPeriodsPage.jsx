import React, { useEffect, useState } from 'react';
import { usePayrollPeriodStore } from '../../store/payrollPeriodStore';
import { Plus, Edit2, Trash2, X } from 'lucide-react';

const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-GB') : '--';
const toDateInput = (d) => d ? new Date(d).toISOString().split('T')[0] : '';

const PayrollPeriodsPage = () => {
  const { items: periods, loading, error, fetchItems, createItem, updateItem, deleteItem } = usePayrollPeriodStore();
  const [showModal, setShowModal] = useState(false);
  const [editingId, setEditingId] = useState(null);
  const [deleteError, setDeleteError] = useState(null);
  const [formData, setFormData] = useState({
    month: '',
    year: '',
    start_date: '',
    end_date: '',
    status: 'Draft'
  });

  useEffect(() => {
    fetchItems();
  }, [fetchItems]);

  const handleOpenModal = (period = null) => {
    if (period) {
      setEditingId(period.id);
      setFormData({
        month: period.month,
        year: period.year,
        start_date: toDateInput(period.start_date),
        end_date: toDateInput(period.end_date),
        status: period.status || 'Draft'
      });
    } else {
      setEditingId(null);
      const today = new Date();
      setFormData({
        month: today.getMonth() + 1,
        year: today.getFullYear(),
        start_date: '',
        end_date: '',
        status: 'Draft'
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
    if (editingId) {
      await updateItem(editingId, formData);
    } else {
      await createItem(formData);
    }
    fetchItems();
    handleCloseModal();
  };

  if (loading && !showModal) return <div className="p-6">Loading...</div>;
  if (error) return <div className="p-6 text-red-600">Error loading payroll periods.</div>;

  const handleDelete = async (id) => {
    if (!window.confirm('Are you sure?')) return;
    setDeleteError(null);
    try {
      await deleteItem(id);
    } catch (e) {
      setDeleteError(e?.response?.data?.message || e?.message || 'Failed to delete');
    }
  };

  return (
    <div className="p-6 max-w-7xl mx-auto">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Payroll Periods</h1>
        <button onClick={() => handleOpenModal()} className="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg flex items-center shadow-sm transition-colors">
          <Plus className="w-4 h-4 mr-2" /> Add Period
        </button>
      </div>

      <div className="bg-white rounded-lg shadow overflow-hidden">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month/Year</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Date</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {periods.map((p) => (
              <tr key={p.id}>
                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{p.month}/{p.year}</td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{fmtDate(p.start_date)}</td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{fmtDate(p.end_date)}</td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <span className={`px-2 py-1 rounded-full text-xs font-semibold 
                    ${p.status === 'Paid' ? 'bg-green-100 text-green-800' : 
                      p.status === 'Locked' ? 'bg-red-100 text-red-800' : 
                      'bg-yellow-100 text-yellow-800'}`}>
                    {p.status}
                  </span>
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button onClick={() => handleOpenModal(p)} className="text-blue-600 hover:text-blue-900 mr-3"><Edit2 className="w-4 h-4" /></button>
                  <button onClick={() => handleDelete(p.id)} className="text-red-600 hover:text-red-900"><Trash2 className="w-4 h-4" /></button>
                </td>
              </tr>
            ))}
            {periods.length === 0 && (
              <tr>
                <td colSpan="5" className="px-6 py-4 text-center text-sm text-gray-500">No payroll periods found.</td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      {deleteError && (
        <div className="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 flex justify-between items-center">
          <span>{deleteError}</span>
          <button onClick={() => setDeleteError(null)} className="text-red-500 hover:text-red-700 ml-4"><X className="w-4 h-4" /></button>
        </div>
      )}

      {showModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl shadow-lg w-full max-w-xl">
            <div className="flex justify-between items-center p-6 border-b">
              <h2 className="text-xl font-bold text-gray-900">{editingId ? 'Edit Payroll Period' : 'Add Payroll Period'}</h2>
              <button onClick={handleCloseModal} className="text-gray-400 hover:text-gray-600">
                <X className="w-6 h-6" />
              </button>
            </div>
            
            <form onSubmit={handleSubmit} className="p-6">
              <div className="grid grid-cols-2 gap-6">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Month *</label>
                  <input type="number" min="1" max="12" required className="w-full border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500"
                    value={formData.month} onChange={(e) => setFormData({ ...formData, month: e.target.value })} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Year *</label>
                  <input type="number" min="2000" max="2100" required className="w-full border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500"
                    value={formData.year} onChange={(e) => setFormData({ ...formData, year: e.target.value })} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                  <input type="date" required className="w-full border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500"
                    value={formData.start_date} onChange={(e) => setFormData({ ...formData, start_date: e.target.value })} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">End Date *</label>
                  <input type="date" required className="w-full border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500"
                    value={formData.end_date} onChange={(e) => setFormData({ ...formData, end_date: e.target.value })} />
                </div>
                <div className="col-span-2">
                  <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
                  <select className="w-full border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500"
                    value={formData.status} onChange={(e) => setFormData({ ...formData, status: e.target.value })}>
                    <option value="Draft">Draft</option>
                    <option value="Processing">Processing</option>
                    <option value="Approved">Approved</option>
                    <option value="Locked">Locked</option>
                    <option value="Paid">Paid</option>
                  </select>
                </div>
              </div>

              <div className="mt-8 flex justify-end gap-3 pt-6 border-t">
                <button type="button" onClick={handleCloseModal} className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" className="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600">
                  {editingId ? 'Update Period' : 'Save Period'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};

export default PayrollPeriodsPage;
