import { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { 
  CheckCircle, Edit, MapPin, Search, Plus, Filter, MoreVertical, 
  MessageSquare, Settings, BarChart2, Mail, Phone, FileText, Download, Trash2, Clock
} from 'lucide-react';
import api from '../../services/api';
import CustomerFormModal from '../../components/Customers/CustomerFormModal';
import DocumentManager from '../../components/Shared/DocumentManager';
import NotesSection from '../../components/Shared/NotesSection';

export default function CustomerProfile() {
  const { id } = useParams();
  const [customer, setCustomer] = useState(null);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState('Overview');
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);

  useEffect(() => {
    fetchCustomer();
  }, [id]);

  const fetchCustomer = async () => {
    try {
      const res = await api.get(`/customers/${id}`);
      setCustomer(res.data);
    } catch (error) {
      console.error('Error fetching customer details:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <div className="p-8 text-center text-gray-500">Loading profile...</div>;
  if (!customer) return <div className="p-8 text-center text-red-500">Customer not found.</div>;

  return (
    <div className="space-y-6">
      {/* Header Profile Section */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div className="flex items-center">
          <div className="w-16 h-16 rounded-xl bg-gray-900 flex items-center justify-center mr-5 shadow-lg">
             <span className="text-2xl font-bold text-white">{customer.name.substring(0, 2).toUpperCase()}</span>
          </div>
          <div>
            <div className="flex items-center space-x-3 mb-1">
              <h1 className="text-2xl font-bold text-gray-900">{customer.name}</h1>
              <span className="px-2.5 py-0.5 rounded text-[10px] font-bold bg-[#1a56db] text-white uppercase tracking-wider">Key Account</span>
            </div>
            <div className="flex items-center space-x-4 text-sm font-medium text-gray-500">
              <span className="flex items-center">
                <MapPin className="w-4 h-4 mr-1.5" /> {customer.address ? customer.address.split(',')[0] : 'San Francisco, CA'}
              </span>
              <span>•</span>
              <span>Enterprise Segment</span>
              <span>•</span>
              <span className="flex items-center text-[#1a56db]">
                <CheckCircle className="w-4 h-4 mr-1.5" /> {customer.status || 'Active Status'}
              </span>
            </div>
          </div>
        </div>
        
        <div className="flex space-x-3 w-full md:w-auto">
          <button 
            onClick={() => setIsEditModalOpen(true)}
            className="flex-1 md:flex-none px-4 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-bold rounded-lg hover:bg-gray-50 shadow-sm transition-colors flex items-center justify-center"
          >
            <Edit className="w-4 h-4 mr-2" /> Edit Profile
          </button>
          <button className="flex-1 md:flex-none inline-flex justify-center items-center px-4 py-2.5 bg-[#1a56db] hover:bg-[#1e40af] text-white text-sm font-bold rounded-lg shadow-sm transition-colors">
            <Plus className="w-4 h-4 mr-2" /> New Sales Order
          </button>
        </div>
      </div>

      {/* Top Stat Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center">
          <div className="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center mr-4">
            <DollarSign className="w-6 h-6 text-[#1a56db]" />
          </div>
          <div>
            <p className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total Lifetime Spend</p>
            <h3 className="text-2xl font-bold text-gray-900">₹{parseFloat(customer.total_revenue || 0).toLocaleString(undefined, {minimumFractionDigits: 2})}</h3>
          </div>
        </div>

        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center justify-between">
          <div className="flex items-center">
             <div className="w-12 h-12 rounded-full bg-orange-50 flex items-center justify-center mr-4">
              <StarIcon />
            </div>
            <div>
              <p className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Reliability Score</p>
              <h3 className="text-2xl font-bold text-gray-900">98/100</h3>
            </div>
          </div>
          <span className="text-xs font-bold text-red-500">-4%</span>
        </div>

        <div className="bg-[#f8f9fb] rounded-xl shadow-sm border border-gray-200 p-6 flex items-center">
          <div className="w-12 h-12 rounded-full bg-white border border-gray-200 shadow-sm flex items-center justify-center mr-4">
            <ShoppingCartIcon />
          </div>
          <div>
            <p className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Active Sales Orders</p>
            <h3 className="text-2xl font-bold text-gray-900">
              {customer.sales_orders?.filter(so => !['Delivered', 'Completed'].includes(so.status)).length || 0} Orders
            </h3>
          </div>
        </div>
      </div>

      {/* Main Content Layout */}
      <div className="flex flex-col lg:flex-row gap-6">
        {/* Main Tab Area */}
        <div className="flex-1 space-y-6">
          <div className="bg-white rounded-xl shadow-sm border border-gray-200">
            <div className="border-b border-gray-200 px-6">
              <div className="flex space-x-8">
                {['Overview', 'Order History', 'Documents', 'Notes'].map(tab => (
                  <button
                    key={tab}
                    onClick={() => setActiveTab(tab)}
                    className={`py-4 text-sm font-bold transition-colors border-b-2 ${
                      activeTab === tab ? 'border-[#1a56db] text-[#1a56db]' : 'border-transparent text-gray-500 hover:text-gray-900'
                    }`}
                  >
                    {tab}
                  </button>
                ))}
              </div>
            </div>
            
            <div className="p-6">
              {activeTab === 'Overview' && (
                <div>
                  <div className="flex justify-between items-center mb-6">
                    <h3 className="text-lg font-bold text-gray-900">Recent Order History</h3>
                    <button className="text-sm font-bold text-[#1a56db] hover:underline">View All</button>
                  </div>
                  <div className="overflow-x-auto">
                    <table className="w-full text-left border-collapse">
                      <thead>
                        <tr className="border-b border-gray-100">
                          <th className="pb-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Order ID</th>
                          <th className="pb-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                          <th className="pb-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Amount</th>
                          <th className="pb-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                          <th className="pb-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Action</th>
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-gray-50">
                        {customer.sales_orders?.length === 0 ? (
                          <tr><td colSpan="5" className="py-4 text-center text-sm text-gray-500">No recent orders.</td></tr>
                        ) : (
                          customer.sales_orders?.slice(0, 4).map(so => (
                            <tr key={so.id} className="hover:bg-gray-50">
                              <td className="py-4 text-sm font-bold text-[#1a56db]">{so.so_number}</td>
                              <td className="py-4 text-sm text-gray-900 font-medium">
                                {new Date(so.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                              </td>
                              <td className="py-4 text-sm font-bold text-gray-900">
                                ₹{parseFloat(so.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2})}
                              </td>
                              <td className="py-4">
                                <span className={`inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border ${
                                  so.status === 'Delivered' ? 'bg-green-50 text-green-700 border-green-200' :
                                  so.status === 'Processing' ? 'bg-blue-50 text-blue-700 border-blue-200' :
                                  so.status === 'Shipped' ? 'bg-purple-50 text-purple-700 border-purple-200' :
                                  'bg-red-50 text-red-700 border-red-200'
                                }`}>
                                  <span className={`w-1.5 h-1.5 rounded-full mr-1.5 ${so.status === 'Delivered' ? 'bg-green-500' : so.status === 'Processing' ? 'bg-blue-500' : so.status === 'Shipped' ? 'bg-purple-500' : 'bg-red-500'}`}></span>
                                  {so.status}
                                </span>
                              </td>
                              <td className="py-4 text-right">
                                <button className="text-gray-400 hover:text-gray-600"><MoreVertical className="w-4 h-4" /></button>
                              </td>
                            </tr>
                          ))
                        )}
                      </tbody>
                    </table>
                  </div>

                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                    <div className="border border-gray-200 rounded-xl p-6">
                       <h3 className="text-base font-bold text-gray-900 mb-4">Latest Communication</h3>
                       <div className="space-y-4">
                         <div className="flex items-start">
                           <div className="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center mr-3 mt-1 shrink-0">
                             <Mail className="w-4 h-4 text-[#1a56db]" />
                           </div>
                           <div>
                             <p className="text-sm font-bold text-gray-900">Quote sent for Q4 server expansion</p>
                             <p className="text-xs text-gray-500 mt-1">Yesterday, 4:30 PM • By Sarah</p>
                           </div>
                         </div>
                         <div className="flex items-start">
                           <div className="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center mr-3 mt-1 shrink-0">
                             <Phone className="w-4 h-4 text-green-600" />
                           </div>
                           <div>
                             <p className="text-sm font-bold text-gray-900">Contract renewal negotiation</p>
                             <p className="text-xs text-gray-500 mt-1">Oct 21, 2023 • By David Chen</p>
                           </div>
                         </div>
                       </div>
                    </div>
                    <div className="border border-gray-200 rounded-xl p-6 flex flex-col justify-between">
                       <h3 className="text-base font-bold text-gray-900 mb-4">Engagement Activity</h3>
                       <div className="flex items-end justify-between h-24 space-x-2">
                         {/* Dummy chart bars */}
                         <div className="w-full bg-[#e1effe] rounded-t-sm h-[30%]"></div>
                         <div className="w-full bg-[#1a56db] rounded-t-sm h-[80%]"></div>
                         <div className="w-full bg-[#e1effe] rounded-t-sm h-[40%]"></div>
                         <div className="w-full bg-[#e1effe] rounded-t-sm h-[60%]"></div>
                         <div className="w-full bg-[#e1effe] rounded-t-sm h-[100%] opacity-20"></div>
                       </div>
                    </div>
                  </div>
                </div>
              )}

              {activeTab === 'Order History' && (
                <div className="text-center py-8 text-gray-500 font-medium">Full Order History view (coming soon)</div>
              )}
              
              {activeTab === 'Documents' && (
                <DocumentManager documentableType="App\Models\Customer" documentableId={customer.id} initialDocuments={customer.documents} />
              )}

              {activeTab === 'Notes' && (
                <NotesSection notableType="App\Models\Customer" notableId={customer.id} initialNotes={customer.notes} />
              )}
            </div>
          </div>
        </div>

        {/* Right Sidebar Area */}
        <div className="w-full lg:w-80 space-y-6">
          <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Primary Contacts</h3>
            <div className="space-y-4">
              <div className="flex items-center justify-between">
                <div className="flex items-center">
                  <div className="w-10 h-10 rounded-full overflow-hidden mr-3">
                    <img src={`https://ui-avatars.com/api/?name=${encodeURIComponent(customer.name)}&background=random`} alt="Avatar" />
                  </div>
                  <div>
                    <p className="text-sm font-bold text-gray-900">Sarah Jenkins</p>
                    <p className="text-xs text-gray-500 font-medium">Account Executive</p>
                  </div>
                </div>
                <button className="text-[#1a56db] hover:bg-blue-50 p-1.5 rounded"><MessageSquare className="w-4 h-4" /></button>
              </div>
              <div className="flex items-center justify-between">
                <div className="flex items-center">
                  <div className="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-3 text-gray-600 font-bold text-sm">
                    DC
                  </div>
                  <div>
                    <p className="text-sm font-bold text-gray-900">David Chen</p>
                    <p className="text-xs text-gray-500 font-medium">Operations Lead</p>
                  </div>
                </div>
                <button className="text-[#1a56db] hover:bg-blue-50 p-1.5 rounded"><MessageSquare className="w-4 h-4" /></button>
              </div>
            </div>
          </div>

          <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
             <h3 className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Tags</h3>
             <div className="flex flex-wrap gap-2">
                <span className="px-2.5 py-1 bg-[#e1effe] text-[#1a56db] text-xs font-bold rounded">Tier 1 Vendor</span>
                <span className="px-2.5 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded">SaaS Integration</span>
                <span className="px-2.5 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded">Auto-Pay</span>
                <span className="px-2.5 py-1 bg-gray-50 border border-gray-200 text-gray-500 text-xs font-bold rounded">+ Add</span>
             </div>
          </div>
          
          <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 overflow-hidden relative h-40">
             <div className="absolute inset-0 bg-gray-200">
               <img src="https://upload.wikimedia.org/wikipedia/commons/8/80/World_map_-_low_resolution.svg" className="w-full h-full object-cover opacity-30 grayscale" alt="Map" />
             </div>
             <div className="absolute bottom-4 left-4">
               <p className="text-xs font-bold text-white shadow-sm">HQ Location</p>
               <p className="text-xs text-white shadow-sm">300 California St, SF</p>
             </div>
             <button className="absolute bottom-4 right-4 w-8 h-8 bg-[#1a56db] rounded-full flex items-center justify-center text-white shadow-lg hover:bg-[#1e40af]">
               <MessageSquare className="w-4 h-4" />
             </button>
          </div>
        </div>
      </div>

      {isEditModalOpen && (
        <CustomerFormModal 
          isOpen={isEditModalOpen} 
          onClose={() => setIsEditModalOpen(false)} 
          onSuccess={fetchCustomer}
          customer={customer}
        />
      )}
    </div>
  );
}

function StarIcon() {
  return <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-orange-500"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>;
}

function ShoppingCartIcon() {
  return <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-[#1a56db]"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>;
}
