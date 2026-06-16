import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { 
  Search, Plus, Filter, MoreVertical, DollarSign, Download, Building2, Star
} from 'lucide-react';
import api from '../../services/api';
import SupplierFormModal from '../../components/Customers/SupplierFormModal';

export default function SupplierDirectory() {
  const [suppliers, setSuppliers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchQuery, setSearchQuery] = useState('');
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingSupplier, setEditingSupplier] = useState(null);

  useEffect(() => {
    fetchSuppliers();
  }, []);

  const fetchSuppliers = async () => {
    try {
      const res = await api.get('/suppliers');
      setSuppliers(res.data.data || res.data); 
    } catch (error) {
      console.error('Error fetching suppliers:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleEdit = (supplier) => {
    setEditingSupplier(supplier);
    setIsModalOpen(true);
  };

  const handleDelete = async (id) => {
    if (window.confirm('Are you sure you want to permanently delete this supplier?')) {
      try {
        await api.delete(`/suppliers/${id}`);
        fetchSuppliers();
      } catch (error) {
        alert('Failed to delete supplier');
      }
    }
  };

  const totalSuppliers = suppliers.length;
  const totalPayable = suppliers.reduce((sum, s) => sum + (parseFloat(s.balance) || 0), 0);
  const avgRating = 4.8; // Stub based on UI screenshot

  const filteredSuppliers = suppliers.filter(s => 
    s.name?.toLowerCase().includes(searchQuery.toLowerCase()) ||
    s.email?.toLowerCase().includes(searchQuery.toLowerCase())
  );

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-start">
        <div>
          <h1 className="text-3xl font-bold text-gray-900 mb-2">Supplier Directory</h1>
          <p className="text-gray-500">Manage global vendor relations, performance metrics, and outstanding accounts.</p>
        </div>
        <div className="flex space-x-3">
          <button className="flex items-center px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-bold rounded-lg hover:bg-gray-50 shadow-sm transition-colors">
            <Download className="w-4 h-4 mr-2" /> Export
          </button>
          <button 
            onClick={() => { setEditingSupplier(null); setIsModalOpen(true); }}
            className="flex items-center px-4 py-2 bg-[#1a56db] text-white text-sm font-bold rounded-lg hover:bg-[#1e40af] shadow-sm transition-colors"
          >
            <Plus className="w-4 h-4 mr-2" /> Add Supplier
          </button>
        </div>
      </div>

      {/* Stat Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center">
           <div className="w-12 h-12 rounded bg-blue-50 flex items-center justify-center mr-4">
            <Building2 className="w-6 h-6 text-[#1a56db]" />
          </div>
          <div>
            <p className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total Suppliers</p>
            <h3 className="text-2xl font-bold text-gray-900">{totalSuppliers}</h3>
          </div>
        </div>

        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center">
           <div className="w-12 h-12 rounded bg-blue-50 flex items-center justify-center mr-4">
            <DollarSign className="w-6 h-6 text-[#1a56db]" />
          </div>
          <div>
            <p className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total Payable</p>
            <h3 className="text-2xl font-bold text-gray-900">₹{totalPayable.toLocaleString(undefined, {minimumFractionDigits: 2})}</h3>
          </div>
        </div>

        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center">
           <div className="w-12 h-12 rounded bg-orange-50 flex items-center justify-center mr-4">
            <Star className="w-6 h-6 text-orange-500" />
          </div>
          <div>
            <p className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Avg Rating</p>
            <h3 className="text-2xl font-bold text-gray-900">{avgRating} / 5.0</h3>
          </div>
        </div>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-200">
        <div className="p-4 border-b border-gray-200 flex flex-col md:flex-row justify-between items-center gap-4">
          <div className="flex items-center space-x-3 w-full md:w-auto">
            <button className="flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 whitespace-nowrap">
              Category: All Categories
            </button>
            <button className="flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 whitespace-nowrap">
              Status: Active Only
            </button>
            <button className="text-[#1a56db] text-sm font-bold hover:underline">Clear Filters</button>
          </div>
          <div className="flex items-center w-full md:w-auto">
            <div className="relative w-full md:w-64">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <Search className="h-4 w-4 text-gray-400" />
              </div>
              <input
                type="text"
                placeholder="Search suppliers..."
                className="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 focus:bg-white focus:ring-[#1a56db]"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
              />
            </div>
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="border-b border-gray-200">
                <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Supplier & Contact</th>
                <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Category</th>
                <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Balance</th>
                <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Performance</th>
                <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {loading ? (
                <tr><td colSpan="6" className="px-6 py-8 text-center text-gray-500">Loading...</td></tr>
              ) : filteredSuppliers.length === 0 ? (
                <tr><td colSpan="6" className="px-6 py-8 text-center text-gray-500">No suppliers found.</td></tr>
              ) : (
                filteredSuppliers.map((supplier) => (
                  <tr key={supplier.id} className="hover:bg-gray-50 transition-colors">
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex items-center">
                        <div className="h-10 w-10 rounded border border-gray-200 bg-white flex items-center justify-center text-[#1a56db] font-bold text-sm mr-4 shadow-sm">
                          {supplier.name.substring(0, 2).toUpperCase()}
                        </div>
                        <div>
                          <Link to={`/dashboard/suppliers/${supplier.id}`} className="text-sm font-bold text-gray-900 hover:text-[#1a56db]">
                            {supplier.name}
                          </Link>
                          <div className="text-xs text-gray-500 mt-0.5">{supplier.email || supplier.phone}</div>
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className="inline-flex items-center px-2.5 py-1 rounded bg-blue-50 text-blue-600 text-[10px] font-bold uppercase tracking-wider">
                        {supplier.category || 'General'}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex items-center text-sm font-medium">
                        <span className="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                        <span className="text-gray-700">Active</span>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className={`text-sm font-bold ${parseFloat(supplier.balance) > 0 ? 'text-red-500' : 'text-gray-900'}`}>
                        ₹{parseFloat(supplier.balance || 0).toLocaleString(undefined, {minimumFractionDigits: 2})}
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex space-x-0.5 text-orange-500">
                        <Star className="w-4 h-4 fill-current" />
                        <Star className="w-4 h-4 fill-current" />
                        <Star className="w-4 h-4 fill-current" />
                        <Star className="w-4 h-4 fill-current" />
                        <Star className="w-4 h-4" />
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm">
                      <button onClick={() => handleEdit(supplier)} className="text-gray-400 hover:text-[#1a56db] mx-1">Edit</button>
                      <button onClick={() => handleDelete(supplier.id)} className="text-gray-400 hover:text-red-600 mx-1">Del</button>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>

      {isModalOpen && (
        <SupplierFormModal 
          isOpen={isModalOpen} 
          onClose={() => setIsModalOpen(false)} 
          onSuccess={fetchSuppliers}
          supplier={editingSupplier}
        />
      )}
    </div>
  );
}
