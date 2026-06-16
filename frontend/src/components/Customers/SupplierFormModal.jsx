import { useState, useEffect } from 'react';
import { X } from 'lucide-react';
import api from '../../services/api';
import { useAuthStore } from '../../store/authStore';
import { useBranchStore } from '../../store/branchStore';

export default function SupplierFormModal({ isOpen, onClose, onSuccess, supplier = null }) {
  const { user, roles } = useAuthStore();
  const { availableBranches } = useBranchStore();
  
  const isAdmin = roles?.includes('Super Admin') || roles?.includes('Admin');
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    gst_number: '',
    address: '',
    branch_id: user?.branch_id || ''
  });
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});

  useEffect(() => {
    if (supplier) {
      setFormData({
        name: supplier.name || '',
        email: supplier.email || '',
        phone: supplier.phone || '',
        gst_number: supplier.gst_number || '',
        address: supplier.address || '',
        branch_id: supplier.branch_id || user?.branch_id
      });
    } else {
      setFormData({
        name: '',
        email: '',
        phone: '',
        gst_number: '',
        address: '',
        branch_id: user?.branch_id || ''
      });
    }
    setErrors({});
  }, [supplier, user, isOpen]);

  if (!isOpen) return null;

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
    if (errors[e.target.name]) {
      setErrors({ ...errors, [e.target.name]: null });
    }
  };

  const validate = () => {
    const newErrors = {};
    if (!formData.name.trim()) newErrors.name = 'Name is required';
    if (!formData.branch_id) newErrors.branch_id = 'Branch is required (User must have a branch assigned)';
    if (formData.email && !/\S+@\S+\.\S+/.test(formData.email)) newErrors.email = 'Invalid email address';
    
    if (formData.gst_number && formData.gst_number.length !== 15) {
      newErrors.gst_number = 'GST Number must be 15 characters';
    }
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!validate()) return;
    
    setLoading(true);
    try {
      if (supplier) {
        await api.put(`/suppliers/${supplier.id}`, formData);
      } else {
        await api.post('/suppliers', formData);
      }
      onSuccess();
      onClose();
    } catch (error) {
      console.error('Error saving supplier:', error);
      if (error.response?.data?.errors) {
        setErrors(error.response.data.errors);
      } else {
        alert(error.response?.data?.message || 'Failed to save supplier');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div className="bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden">
        <div className="flex justify-between items-center p-6 border-b border-gray-100">
          <h2 className="text-xl font-bold text-gray-900">{supplier ? 'Edit Supplier' : 'Add New Supplier'}</h2>
          <button onClick={onClose} className="text-gray-400 hover:text-gray-600 transition-colors">
            <X className="w-5 h-5" />
          </button>
        </div>
        
        <form onSubmit={handleSubmit} className="p-6 space-y-4">
          <div>
            <label className="block text-sm font-bold text-gray-700 mb-1">Supplier Name *</label>
            <input
              type="text"
              name="name"
              value={formData.name}
              onChange={handleChange}
              className={`w-full px-4 py-2 border rounded-lg focus:ring-[#1a56db] focus:border-[#1a56db] ${errors.name ? 'border-red-500' : 'border-gray-300'}`}
              placeholder="e.g. Global Logistics Pro"
            />
            {errors.name && <p className="mt-1 text-xs text-red-500">{errors.name}</p>}
          </div>
          
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-bold text-gray-700 mb-1">Email</label>
              <input
                type="email"
                name="email"
                value={formData.email}
                onChange={handleChange}
                className={`w-full px-4 py-2 border rounded-lg focus:ring-[#1a56db] focus:border-[#1a56db] ${errors.email ? 'border-red-500' : 'border-gray-300'}`}
                placeholder="contact@supplier.com"
              />
              {errors.email && <p className="mt-1 text-xs text-red-500">{errors.email[0] || errors.email}</p>}
            </div>
            <div>
              <label className="block text-sm font-bold text-gray-700 mb-1">Phone</label>
              <input
                type="tel"
                name="phone"
                value={formData.phone}
                onChange={handleChange}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#1a56db] focus:border-[#1a56db]"
                placeholder="+1 555-0129"
              />
            </div>
          </div>
          
          <div>
            <label className="block text-sm font-bold text-gray-700 mb-1">GST Number</label>
            <input
              type="text"
              name="gst_number"
              value={formData.gst_number}
              onChange={handleChange}
              className={`w-full px-4 py-2 border uppercase rounded-lg focus:ring-[#1a56db] focus:border-[#1a56db] ${errors.gst_number ? 'border-red-500' : 'border-gray-300'}`}
              placeholder="e.g. 27ABCDE1234F1Z5"
            />
            {errors.gst_number && <p className="mt-1 text-xs text-red-500">{errors.gst_number}</p>}
          </div>

          <div>
            <label className="block text-sm font-bold text-gray-700 mb-1">Address</label>
            <textarea
              name="address"
              value={formData.address}
              onChange={handleChange}
              rows="3"
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[#1a56db] focus:border-[#1a56db]"
              placeholder="Full office address..."
            ></textarea>
          </div>
          
          {isAdmin && !user?.branch_id && (
            <div>
              <label className="block text-sm font-bold text-gray-700 mb-1">Assign to Branch *</label>
              <select
                name="branch_id"
                value={formData.branch_id}
                onChange={handleChange}
                className={`w-full px-4 py-2 border rounded-lg focus:ring-[#1a56db] focus:border-[#1a56db] ${errors.branch_id ? 'border-red-500' : 'border-gray-300'}`}
              >
                <option value="">Select Branch...</option>
                {availableBranches?.map(branch => (
                  <option key={branch.id} value={branch.id}>{branch.name}</option>
                ))}
              </select>
              {errors.branch_id && <p className="mt-1 text-xs text-red-500">{errors.branch_id}</p>}
            </div>
          )}
          
          {!isAdmin && errors.branch_id && <p className="text-xs text-red-500 font-bold">{errors.branch_id}</p>}
          
          <div className="pt-4 flex justify-end space-x-3 border-t border-gray-100 mt-6">
            <button
              type="button"
              onClick={onClose}
              className="px-5 py-2.5 border border-gray-300 text-gray-700 font-bold rounded-lg hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={loading}
              className="px-5 py-2.5 bg-[#1a56db] text-white font-bold rounded-lg hover:bg-[#1e40af] transition-colors disabled:opacity-50"
            >
              {loading ? 'Saving...' : (supplier ? 'Update Supplier' : 'Add Supplier')}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
