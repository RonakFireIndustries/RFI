import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { 
  Search, Plus, Filter, MoreVertical, Users, DollarSign, Activity, Wallet, Download, CheckCircle, Clock
} from 'lucide-react';
import api from '../../services/api';
import CustomerFormModal from '../../components/Customers/CustomerFormModal';

export default function CustomerDirectory() {
  const [customers, setCustomers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchQuery, setSearchQuery] = useState('');
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingCustomer, setEditingCustomer] = useState(null);

  useEffect(() => {
    fetchCustomers();
  }, []);

  const fetchCustomers = async () => {
    try {
      const res = await api.get('/customers');
      setCustomers(res.data.data || res.data); // Handle pagination structure or flat array
    } catch (error) {
      console.error('Error fetching customers:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleEdit = (customer) => {
    setEditingCustomer(customer);
    setIsModalOpen(true);
  };

  const handleDelete = async (id) => {
    if (window.confirm('Are you sure you want to permanently delete this customer?')) {
      try {
        await api.delete(`/customers/${id}`);
        fetchCustomers();
      } catch (error) {
        alert('Failed to delete customer');
      }
    }
  };

  const totalCustomers = customers.length;
  const activeOrders = customers.reduce((sum, c) => sum + (c.orders_count || 0), 0);
  const totalRevenue = customers.reduce((sum, c) => sum + (parseFloat(c.total_revenue) || 0), 0);
  const avgLifetimeValue = totalCustomers ? totalRevenue / totalCustomers : 0;

  const filteredCustomers = customers.filter(c => 
    c.name?.toLowerCase().includes(searchQuery.toLowerCase()) ||
    c.email?.toLowerCase().includes(searchQuery.toLowerCase())
  );

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-start">
        <div>
          <h1 className="text-3xl font-bold text-gray-900 mb-2">Customer Directory</h1>
          <p className="text-gray-500">Manage your enterprise relationships and track customer health.</p>
        </div>
        <div className="flex space-x-3">
          <button className="flex items-center px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-bold rounded-lg hover:bg-gray-50 shadow-sm transition-colors">
            <Download className="w-4 h-4 mr-2" /> Export
          </button>
          <button 
            onClick={() => { setEditingCustomer(null); setIsModalOpen(true); }}
            className="flex items-center px-4 py-2 bg-[#1a56db] text-white text-sm font-bold rounded-lg hover:bg-[#1e40af] shadow-sm transition-colors"
          >
            <Plus className="w-4 h-4 mr-2" /> Add Customer
          </button>
        </div>
      </div>

      {/* Stat Cards */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div className="flex justify-between items-start mb-4">
            <div className="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center">
              <Users className="w-5 h-5 text-[#1a56db]" />
            </div>
            <span className="text-xs font-bold text-green-500 bg-green-50 px-2 py-1 rounded-full">+12%</span>
          </div>
          <p className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total Customers</p>
          <h3 className="text-2xl font-bold text-gray-900">{totalCustomers.toLocaleString()}</h3>
        </div>

        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div className="flex justify-between items-start mb-4">
            <div className="w-10 h-10 rounded-full bg-orange-50 flex items-center justify-center">
              <Activity className="w-5 h-5 text-orange-500" />
            </div>
            <span className="text-xs font-bold text-green-500 bg-green-50 px-2 py-1 rounded-full">+5%</span>
          </div>
          <p className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Active Orders</p>
          <h3 className="text-2xl font-bold text-gray-900">{activeOrders}</h3>
        </div>

        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div className="flex justify-between items-start mb-4">
            <div className="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center">
              <DollarSign className="w-5 h-5 text-green-500" />
            </div>
            <span className="text-xs font-bold text-gray-500 bg-gray-100 px-2 py-1 rounded-full">Stable</span>
          </div>
          <p className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total Revenue</p>
          <h3 className="text-2xl font-bold text-gray-900">₹{(totalRevenue / 1000000).toFixed(1)}M</h3>
        </div>

        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div className="flex justify-between items-start mb-4">
            <div className="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center">
              <Wallet className="w-5 h-5 text-purple-500" />
            </div>
            <span className="text-xs font-bold text-red-500 bg-red-50 px-2 py-1 rounded-full">-2%</span>
          </div>
          <p className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Avg. Lifetime Value</p>
          <h3 className="text-2xl font-bold text-gray-900">₹{avgLifetimeValue.toLocaleString(undefined, {maximumFractionDigits: 0})}</h3>
        </div>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-200">
        <div className="p-4 border-b border-gray-200 flex flex-col md:flex-row justify-between items-center gap-4">
          <div className="flex items-center w-full md:w-auto">
            <div className="relative w-full md:w-80">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <Search className="h-4 w-4 text-gray-400" />
              </div>
              <input
                type="text"
                placeholder="Search customer, email or ID..."
                className="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 focus:bg-white focus:ring-[#1a56db] focus:border-[#1a56db] transition-colors"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
              />
            </div>
          </div>
          <div className="flex items-center space-x-3 w-full md:w-auto overflow-x-auto">
            <button className="flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 whitespace-nowrap">
              Segment: All <Filter className="w-4 h-4 ml-2 text-gray-400" />
            </button>
            <button className="flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 whitespace-nowrap">
              Status: All <Filter className="w-4 h-4 ml-2 text-gray-400" />
            </button>
            <button className="p-2 text-gray-400 hover:text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
              <MoreVertical className="w-5 h-5" />
            </button>
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="border-b border-gray-200">
                <th className="px-6 py-4 w-12">
                  <input type="checkbox" className="rounded border-gray-300 text-[#1a56db] focus:ring-[#1a56db]" />
                </th>
                <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Customer Name</th>
                <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Segment</th>
                <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Orders</th>
                <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Last Order</th>
                <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Balance</th>
                <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {loading ? (
                <tr>
                  <td colSpan="8" className="px-6 py-8 text-center text-gray-500">Loading customers...</td>
                </tr>
              ) : filteredCustomers.length === 0 ? (
                <tr>
                  <td colSpan="8" className="px-6 py-8 text-center text-gray-500">No customers found.</td>
                </tr>
              ) : (
                filteredCustomers.map((customer) => (
                  <tr key={customer.id} className="hover:bg-gray-50 transition-colors">
                    <td className="px-6 py-4">
                      <input type="checkbox" className="rounded border-gray-300 text-[#1a56db] focus:ring-[#1a56db]" />
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex items-center">
                        <div className="h-10 w-10 rounded-full bg-gradient-to-br from-[#1a56db] to-blue-400 flex items-center justify-center text-white font-bold text-sm mr-4 shadow-sm">
                          {customer.name.substring(0, 2).toUpperCase()}
                        </div>
                        <div>
                          <Link to={`/dashboard/customers/${customer.id}`} className="text-sm font-bold text-gray-900 hover:text-[#1a56db]">
                            {customer.name}
                          </Link>
                          <div className="text-xs text-gray-500 mt-0.5">{customer.email || customer.phone || 'No contact info'}</div>
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-600 uppercase tracking-wider">
                        {customer.segment || 'Enterprise'}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                      {customer.orders_count || 0}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {customer.last_order_date ? new Date(customer.last_order_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '-'}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className={`text-sm font-bold ${parseFloat(customer.balance) > 0 ? 'text-red-500' : 'text-gray-900'}`}>
                        ₹{parseFloat(customer.balance || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex items-center text-sm font-medium">
                        <span className="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                        <span className="text-gray-700">Active</span>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm">
                      <button onClick={() => handleEdit(customer)} className="text-gray-400 hover:text-[#1a56db] mx-1 transition-colors">
                        Edit
                      </button>
                      <button onClick={() => handleDelete(customer.id)} className="text-gray-400 hover:text-red-600 mx-1 transition-colors">
                        Del
                      </button>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
        <div className="p-4 border-t border-gray-200 flex items-center justify-between">
          <div className="text-sm text-gray-500 font-medium">Showing 1 to {filteredCustomers.length} of {totalCustomers} customers</div>
          <div className="flex space-x-1">
            <button className="px-3 py-1 border border-gray-200 rounded-md text-sm text-gray-500 bg-white hover:bg-gray-50">‹</button>
            <button className="px-3 py-1 border border-transparent rounded-md text-sm text-white bg-[#1a56db]">1</button>
            <button className="px-3 py-1 border border-gray-200 rounded-md text-sm text-gray-700 bg-white hover:bg-gray-50">2</button>
            <button className="px-3 py-1 border border-gray-200 rounded-md text-sm text-gray-700 bg-white hover:bg-gray-50">3</button>
            <button className="px-3 py-1 border border-gray-200 rounded-md text-sm text-gray-500 bg-white hover:bg-gray-50">›</button>
          </div>
        </div>
      </div>

      <div className="fixed bottom-6 left-6 z-50">
        <button className="px-4 py-3 bg-[#1a56db] text-white font-bold rounded-lg shadow-lg hover:bg-[#1e40af] transition-colors flex items-center">
          <Plus className="w-5 h-5 mr-2" /> Quick Action
        </button>
      </div>

      {isModalOpen && (
        <CustomerFormModal 
          isOpen={isModalOpen} 
          onClose={() => setIsModalOpen(false)} 
          onSuccess={fetchCustomers}
          customer={editingCustomer}
        />
      )}
    </div>
  );
}
