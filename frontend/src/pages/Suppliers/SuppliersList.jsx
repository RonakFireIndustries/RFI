import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { 
  Search, Plus, Filter, MoreVertical, Wallet, Star, Shield, TrendingUp, ShieldCheck
} from 'lucide-react';
import api from '../../services/api';

export default function SuppliersList() {
  const [suppliers, setSuppliers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchQuery, setSearchQuery] = useState('');

  useEffect(() => {
    fetchSuppliers();
  }, []);

  const fetchSuppliers = async () => {
    try {
      const res = await api.get('/suppliers');
      setSuppliers(res.data);
    } catch (error) {
      console.error('Error fetching suppliers:', error);
    } finally {
      setLoading(false);
    }
  };

  const totalPayable = suppliers.reduce((sum, s) => sum + (parseFloat(s.balance) || 0), 0);
  const avgRating = "4.8"; // Default placeholder if no rating system

  const filteredSuppliers = suppliers.filter(s => 
    s.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
    s.email?.toLowerCase().includes(searchQuery.toLowerCase())
  );

  return (
    <div className="space-y-6">
      <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <div className="text-sm text-gray-500 mb-1 flex items-center">
            <span>Procurement</span>
            <span className="mx-2">›</span>
            <span className="font-semibold text-gray-900">Supplier Directory</span>
          </div>
          <h1 className="text-3xl font-bold text-gray-900">Supplier Directory</h1>
          <p className="text-gray-500 mt-1">Manage global vendor relations, performance metrics, and outstanding accounts.</p>
        </div>

        <div className="flex items-center space-x-4">
          <div className="bg-[#f8f9fb] border border-gray-200 rounded-xl p-4 min-w-[200px] flex items-center">
            <div className="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-4 flex-shrink-0">
              <Wallet className="w-5 h-5 text-blue-600" />
            </div>
            <div>
              <p className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total Payable</p>
              <p className="text-xl font-bold text-gray-900">₹{totalPayable.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
            </div>
          </div>
          
          <div className="bg-[#fff1f2] border border-red-100 rounded-xl p-4 min-w-[160px] flex items-center">
            <div className="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center mr-4 flex-shrink-0">
              <Star className="w-5 h-5 text-orange-500" />
            </div>
            <div>
              <p className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Avg. Rating</p>
              <p className="text-xl font-bold text-gray-900">{avgRating} <span className="text-sm text-gray-500 font-normal">/ 5.0</span></p>
            </div>
          </div>
        </div>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-200">
        <div className="p-4 border-b border-gray-200 flex flex-col md:flex-row justify-between items-center gap-4">
          <div className="flex items-center space-x-4">
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <Search className="h-4 w-4 text-gray-400" />
              </div>
              <input
                type="text"
                placeholder="Search suppliers..."
                className="pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm w-64 focus:ring-[#1a56db] focus:border-[#1a56db]"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
              />
            </div>
            <button className="flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
              <span className="text-gray-500 mr-2">Category:</span>
              <span className="font-bold text-[#1a56db]">All Categories</span>
              <Filter className="w-4 h-4 ml-2 text-gray-400" />
            </button>
            <button className="flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
              <span className="text-gray-500 mr-2">Status:</span>
              <span className="font-bold text-[#1a56db]">Active Only</span>
              <Filter className="w-4 h-4 ml-2 text-gray-400" />
            </button>
            <button className="text-sm font-bold text-[#1a56db] hover:text-[#1e40af]">Clear all filters</button>
          </div>
          <button className="inline-flex items-center px-4 py-2 bg-[#1a56db] hover:bg-[#1e40af] text-white text-sm font-bold rounded-lg transition-colors shadow-sm">
            <Plus className="w-4 h-4 mr-2" /> Add Supplier
          </button>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="bg-gray-50 border-b border-gray-200">
                <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Supplier & Contact</th>
                <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Category</th>
                <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Balance</th>
                <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Performance</th>
                <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200">
              {loading ? (
                <tr>
                  <td colSpan="6" className="px-6 py-8 text-center text-gray-500">Loading suppliers...</td>
                </tr>
              ) : filteredSuppliers.length === 0 ? (
                <tr>
                  <td colSpan="6" className="px-6 py-8 text-center text-gray-500">No suppliers found.</td>
                </tr>
              ) : (
                filteredSuppliers.map((supplier) => (
                  <tr key={supplier.id} className="hover:bg-gray-50 transition-colors group">
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex items-center">
                        <div className="h-10 w-10 rounded-xl bg-blue-100 flex items-center justify-center text-[#1a56db] font-bold text-sm mr-4">
                          {supplier.name.substring(0, 2).toUpperCase()}
                        </div>
                        <div>
                          <Link to={`/dashboard/suppliers/${supplier.id}`} className="text-sm font-bold text-gray-900 hover:text-[#1a56db] transition-colors">
                            {supplier.name}
                          </Link>
                          <div className="text-xs text-gray-500 mt-0.5">
                            {supplier.email || 'No email provided'} • {supplier.phone || 'No phone'}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600 uppercase tracking-wider">
                        {supplier.category || 'General'}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex items-center text-sm">
                        <span className="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                        <span className="font-medium text-gray-700">Active</span>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="text-sm font-bold text-gray-900">
                        ₹{parseFloat(supplier.balance || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                      </div>
                      {parseFloat(supplier.balance) > 0 && (
                        <div className="text-[10px] font-bold text-red-500 uppercase tracking-wider mt-0.5">
                          PAYMENT DUE
                        </div>
                      )}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex items-center text-orange-400">
                        {[1,2,3,4,5].map(i => (
                          <Star key={i} className={`w-4 h-4 ${i <= 4 ? 'fill-current' : 'text-gray-300'}`} />
                        ))}
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <button className="text-gray-400 hover:text-gray-600">
                        <MoreVertical className="w-5 h-5" />
                      </button>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
        <div className="p-4 border-t border-gray-200 flex items-center justify-between text-sm text-gray-500 font-medium">
          <div>Showing {filteredSuppliers.length} entries</div>
        </div>
      </div>

      {/* Bottom Widgets */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="bg-[#1a56db] rounded-xl shadow-sm p-6 text-white relative overflow-hidden">
          <div className="absolute -right-4 -bottom-4 opacity-10">
            <Shield className="w-48 h-48" />
          </div>
          <div className="relative z-10">
            <h3 className="text-lg font-bold mb-2">Audit Logs</h3>
            <p className="text-blue-100 text-sm mb-6 max-w-[200px] leading-relaxed">
              View detailed change history and procurement compliance logs for all suppliers.
            </p>
            <button className="flex items-center text-white font-bold text-sm hover:underline">
              Open Audit Vault <span className="ml-2">→</span>
            </button>
          </div>
        </div>

        <div className="bg-[#f8f9fb] border border-gray-200 rounded-xl shadow-sm p-6">
          <h3 className="text-lg font-bold text-gray-900 mb-6">Compliance Summary</h3>
          <div className="space-y-4">
            <div>
              <div className="flex justify-between text-sm mb-1">
                <span className="font-medium text-gray-600">ISO 9001 Verified</span>
                <span className="font-bold text-[#1a56db]">92%</span>
              </div>
              <div className="w-full bg-gray-200 rounded-full h-1.5">
                <div className="bg-[#1a56db] h-1.5 rounded-full" style={{ width: '92%' }}></div>
              </div>
            </div>
            <div>
              <div className="flex justify-between text-sm mb-1 mt-6">
                <span className="font-medium text-gray-600">Environmental Certs</span>
                <span className="font-bold text-[#1a56db]">78%</span>
              </div>
              <div className="w-full bg-gray-200 rounded-full h-1.5">
                <div className="bg-[#1a56db] h-1.5 rounded-full" style={{ width: '78%' }}></div>
              </div>
            </div>
          </div>
        </div>

        <div className="bg-white border border-gray-200 rounded-xl shadow-sm p-6 flex flex-col items-center justify-center text-center">
          <div className="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center mb-4">
            <TrendingUp className="w-6 h-6 text-[#1a56db]" />
          </div>
          <h3 className="text-lg font-bold text-gray-900 mb-2">Smart Insights</h3>
          <p className="text-sm text-gray-500 mb-6">Switch to "Analytics View" to see market trend predictions and vendor risk scoring.</p>
          <button className="px-6 py-2 bg-white border-2 border-gray-200 text-[#1a56db] font-bold rounded-lg text-sm hover:bg-gray-50 transition-colors w-full">
            Analyze Trends
          </button>
        </div>
      </div>
    </div>
  );
}
