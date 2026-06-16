import { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { 
  Truck, FileText, Download, Plus, Mail, Phone, MapPin, Search, ChevronRight, CheckCircle, Package
} from 'lucide-react';
import api from '../../services/api';

export default function SupplierDetails() {
  const { id } = useParams();
  const [supplier, setSupplier] = useState(null);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState('General Info');

  useEffect(() => {
    fetchSupplier();
  }, [id]);

  const fetchSupplier = async () => {
    try {
      const res = await api.get(`/suppliers/${id}`);
      setSupplier(res.data);
    } catch (error) {
      console.error('Error fetching supplier details:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return <div className="p-8 text-center text-gray-500">Loading supplier details...</div>;
  }

  if (!supplier) {
    return <div className="p-8 text-center text-red-500">Supplier not found.</div>;
  }

  return (
    <div className="space-y-6">
      {/* Breadcrumb */}
      <div className="text-sm text-gray-500 mb-4 flex items-center">
        <Link to="/dashboard/suppliers" className="hover:text-[#1a56db]">Suppliers</Link>
        <span className="mx-2">›</span>
        <span className="font-semibold text-gray-900">{supplier.name}</span>
      </div>

      {/* Header Profile */}
      <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div className="flex items-center">
          <div className="w-16 h-16 rounded-2xl bg-[#e1effe] flex items-center justify-center mr-5 shadow-sm border border-blue-100">
            <Truck className="w-8 h-8 text-[#1a56db]" />
          </div>
          <div>
            <h1 className="text-3xl font-bold text-gray-900">{supplier.name}</h1>
            <div className="flex items-center mt-2 space-x-4 text-sm font-medium">
              <span className="flex items-center text-gray-600">
                <CheckCircle className="w-4 h-4 text-green-500 mr-1.5" /> Preferred Tier
              </span>
              <span className="flex items-center text-gray-500">
                <MapPin className="w-4 h-4 mr-1.5 text-gray-400" /> {supplier.address ? supplier.address.split(',')[0] : 'No Address'}
              </span>
            </div>
          </div>
        </div>
        
        <div className="flex space-x-3 w-full md:w-auto">
          <button className="flex-1 md:flex-none px-4 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-bold rounded-lg hover:bg-gray-50 shadow-sm transition-colors">
            Export Report
          </button>
          <button className="flex-1 md:flex-none inline-flex justify-center items-center px-4 py-2.5 bg-[#1a56db] hover:bg-[#1e40af] text-white text-sm font-bold rounded-lg shadow-sm transition-colors">
            <Plus className="w-4 h-4 mr-2" /> New Purchase Order
          </button>
        </div>
      </div>

      {/* Stat Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 relative overflow-hidden">
          <div className="absolute top-6 right-6 text-blue-500">
            <WalletIcon />
          </div>
          <p className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Total Lifetime Spend</p>
          <h3 className="text-3xl font-bold text-gray-900 mb-2">
            ₹{parseFloat(supplier.total_lifetime_spend || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}
          </h3>
          <p className="text-sm font-bold text-[#1a56db] flex items-center">
            <TrendingUp className="w-4 h-4 mr-1" /> 12% Increase vs. last year
          </p>
        </div>

        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 relative">
          <div className="flex justify-between items-start mb-4">
            <p className="text-xs font-bold text-gray-500 uppercase tracking-wider">Reliability Score</p>
            <span className="font-bold text-[#1a56db] text-lg">98.4%</span>
          </div>
          <div className="flex items-end justify-between space-x-1 h-12 mb-2">
            {[40, 50, 60, 60, 65, 75, 75].map((h, i) => (
               <div key={i} className="w-full bg-[#e1effe] rounded-sm" style={{ height: `${h}%` }}></div>
            ))}
          </div>
          <p className="text-xs text-gray-500 font-medium">Avg. Lead Time: 4.2 Days (Stable)</p>
        </div>

        <div className="bg-[#1a56db] rounded-xl shadow-sm border border-[#1e40af] p-6 text-white relative overflow-hidden">
          <div className="absolute top-6 right-6 opacity-30">
            <Package className="w-6 h-6" />
          </div>
          <p className="text-xs font-bold text-blue-100 uppercase tracking-wider mb-2">Active Orders</p>
          <h3 className="text-5xl font-bold mb-3">{supplier.active_orders_count || '0'}</h3>
          <p className="text-sm text-blue-100 font-medium">{supplier.active_orders_count > 0 ? `${supplier.active_orders_count} items in transit today` : 'No active orders'}</p>
        </div>
      </div>

      {/* Tabs */}
      <div className="border-b border-gray-200 mt-8 mb-6">
        <div className="flex space-x-8">
          {['General Info', 'Order History', 'Products Supplied', 'Financials'].map(tab => (
            <button
              key={tab}
              onClick={() => setActiveTab(tab)}
              className={`pb-4 text-sm font-bold transition-colors border-b-2 ${
                activeTab === tab ? 'border-[#1a56db] text-[#1a56db]' : 'border-transparent text-gray-500 hover:text-gray-900'
              }`}
            >
              {tab}
            </button>
          ))}
        </div>
      </div>

      {/* Tab Content */}
      {activeTab === 'General Info' && (
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Left Column */}
          <div className="lg:col-span-2 space-y-6">
            <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
              <h3 className="text-base font-bold text-gray-900 mb-6">Supplier Details</h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                  <p className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Legal Name</p>
                  <p className="text-sm font-medium text-gray-900">{supplier.name}</p>
                </div>
                <div>
                  <p className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Tax ID / GST</p>
                  <p className="text-sm font-medium text-gray-900">{supplier.gst_number || 'XX-8899220'}</p>
                </div>
                <div>
                  <p className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Main Office</p>
                  <p className="text-sm font-medium text-gray-900 leading-relaxed">{supplier.address || '450 Enterprise Way, Suite 12, San Francisco, CA'}</p>
                </div>
                <div>
                  <p className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Industry</p>
                  <p className="text-sm font-medium text-gray-900">Logistics & Warehousing</p>
                </div>
              </div>
            </div>

            <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden relative h-64">
              <div className="absolute inset-0 bg-gray-100 flex items-center justify-center">
                 <img src="https://upload.wikimedia.org/wikipedia/commons/8/80/World_map_-_low_resolution.svg" alt="Map" className="w-full h-full object-cover opacity-20 grayscale" />
              </div>
              <div className="absolute bottom-4 left-4 bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200 flex items-center text-sm font-bold text-gray-700">
                <MapPin className="w-4 h-4 text-[#1a56db] mr-2" />
                {supplier.address ? supplier.address.split(',')[0] : 'San Francisco Distribution Hub'}
              </div>
            </div>

            {/* Recent Purchase Orders Table */}
            <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-8">
              <div className="flex justify-between items-center mb-6">
                <h3 className="text-base font-bold text-gray-900">Recent Purchase Orders</h3>
                <Link to="/dashboard/purchases" className="text-sm font-bold text-[#1a56db] hover:underline">View All History</Link>
              </div>
              <div className="overflow-x-auto">
                <table className="w-full text-left border-collapse">
                  <thead>
                    <tr className="border-b border-gray-200">
                      <th className="pb-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Order ID</th>
                      <th className="pb-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                      <th className="pb-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Amount</th>
                      <th className="pb-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                      <th className="pb-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Action</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-100">
                    {supplier.purchase_orders?.length === 0 ? (
                      <tr><td colSpan="5" className="py-4 text-center text-sm text-gray-500">No purchase orders found.</td></tr>
                    ) : (
                      supplier.purchase_orders?.slice(0, 5).map(po => (
                        <tr key={po.id} className="hover:bg-gray-50">
                          <td className="py-4 text-sm font-bold text-[#1a56db]">{po.po_number}</td>
                          <td className="py-4 text-sm text-gray-900 font-medium">
                            {new Date(po.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                          </td>
                          <td className="py-4 text-sm font-bold text-gray-900">
                            ₹{parseFloat(po.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2})}
                          </td>
                          <td className="py-4">
                            <span className="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-50 text-green-700 uppercase tracking-wider border border-green-200">
                              {po.status}
                            </span>
                          </td>
                          <td className="py-4 text-right">
                            <button className="text-gray-400 hover:text-gray-600">
                              <span className="font-bold tracking-widest leading-none">...</span>
                            </button>
                          </td>
                        </tr>
                      ))
                    )}
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          {/* Right Column */}
          <div className="space-y-6">
            <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
              <div className="flex justify-between items-center mb-6">
                <h3 className="text-base font-bold text-gray-900">Primary Contacts</h3>
                <button className="text-[#1a56db]"><EditIcon /></button>
              </div>
              
              <div className="space-y-4">
                <div className="border border-gray-200 rounded-lg p-4 flex items-center justify-between">
                  <div className="flex items-center">
                    <div className="w-10 h-10 rounded-full bg-gray-200 overflow-hidden mr-3">
                      <img src={`https://ui-avatars.com/api/?name=${encodeURIComponent(supplier.name)}&background=random`} alt="Avatar" />
                    </div>
                    <div>
                      <p className="text-sm font-bold text-gray-900">{supplier.name.split(' ')[0] || 'Admin'} Contact</p>
                      <p className="text-xs text-gray-500 font-medium">Account Executive</p>
                    </div>
                  </div>
                  <Mail className="w-4 h-4 text-gray-400" />
                </div>
                
                {supplier.phone && (
                  <div className="border border-gray-200 rounded-lg p-4 flex items-center justify-between">
                    <div className="flex items-center">
                      <div className="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3 text-blue-600 font-bold text-sm">
                        OP
                      </div>
                      <div>
                        <p className="text-sm font-bold text-gray-900">Operations Desk</p>
                        <p className="text-xs text-gray-500 font-medium">{supplier.phone}</p>
                      </div>
                    </div>
                    <Phone className="w-4 h-4 text-gray-400" />
                  </div>
                )}
                
                <button className="w-full py-3 border border-dashed border-gray-300 rounded-lg text-sm font-bold text-gray-500 hover:text-[#1a56db] hover:border-[#1a56db] hover:bg-blue-50 transition-colors flex justify-center items-center">
                  <Plus className="w-4 h-4 mr-2" /> Add Contact
                </button>
              </div>
            </div>

            <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
              <h3 className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Internal Tags</h3>
              <div className="flex flex-wrap gap-2">
                <span className="px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-bold rounded-md">High Reliability</span>
                <span className="px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-bold rounded-md">Fast Logistics</span>
                <span className="px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-bold rounded-md">Q3 Priority</span>
                <span className="px-3 py-1.5 bg-[#e1effe] text-[#1a56db] text-xs font-bold rounded-md">Tech Infrastructure</span>
              </div>
              
              <h3 className="text-xs font-bold text-gray-500 uppercase tracking-wider mt-8 mb-3">Internal Notes</h3>
              <p className="text-sm text-gray-600 italic leading-relaxed">
                "{supplier.name} consistently beats delivery estimates by 0.5 days. Recommend renewing contract early for Q4 volume."
              </p>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

function WalletIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
  );
}

function EditIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
  );
}
