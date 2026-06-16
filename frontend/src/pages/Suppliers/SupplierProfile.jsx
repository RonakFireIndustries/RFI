import { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { 
  Building2, MapPin, Edit, Plus, FileText, MessageSquare, Star, 
  ShoppingCart, PackageCheck, Mail, Phone, MoreVertical 
} from 'lucide-react';
import api from '../../services/api';
import SupplierFormModal from '../../components/Customers/SupplierFormModal';
import DocumentManager from '../../components/Shared/DocumentManager';
import NotesSection from '../../components/Shared/NotesSection';

export default function SupplierProfile() {
  const { id } = useParams();
  const [supplier, setSupplier] = useState(null);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState('General Info');
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);

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

  if (loading) return <div className="p-8 text-center text-gray-500">Loading profile...</div>;
  if (!supplier) return <div className="p-8 text-center text-red-500">Supplier not found.</div>;

  return (
    <div className="space-y-6">
      {/* Header Profile Section */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div className="flex items-center">
          <div className="w-16 h-16 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center mr-5">
             <Building2 className="w-8 h-8 text-[#1a56db]" />
          </div>
          <div>
            <div className="flex items-center space-x-3 mb-1">
              <h1 className="text-2xl font-bold text-gray-900">{supplier.name}</h1>
            </div>
            <div className="flex items-center space-x-4 text-sm font-medium text-gray-500">
              <span className="flex items-center">
                 <span className="w-2 h-2 rounded-full bg-[#1a56db] mr-2"></span>
                 Preferred Tier
              </span>
              <span>•</span>
              <span className="flex items-center">
                <MapPin className="w-4 h-4 mr-1.5" /> {supplier.address ? supplier.address.split(',')[0] : 'San Francisco, CA'}
              </span>
            </div>
          </div>
        </div>
        
        <div className="flex space-x-3 w-full md:w-auto">
          <button className="flex-1 md:flex-none px-4 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-bold rounded-lg hover:bg-gray-50 shadow-sm transition-colors flex items-center justify-center">
             Export Report
          </button>
          <button className="flex-1 md:flex-none inline-flex justify-center items-center px-4 py-2.5 bg-[#1a56db] hover:bg-[#1e40af] text-white text-sm font-bold rounded-lg shadow-sm transition-colors">
            <Plus className="w-4 h-4 mr-2" /> New Purchase Order
          </button>
        </div>
      </div>

      {/* Top Stat Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-[#f8f9fb] rounded-xl shadow-sm border border-gray-200 p-6 flex items-center">
          <div>
            <div className="flex items-center mb-1">
              <p className="text-xs font-bold text-gray-500 uppercase tracking-wider mr-2">Total Lifetime Spend</p>
              <DollarSign className="w-4 h-4 text-[#1a56db]" />
            </div>
            <h3 className="text-3xl font-bold text-gray-900 mb-2">₹{parseFloat(supplier.total_lifetime_spend || 0).toLocaleString(undefined, {minimumFractionDigits: 2})}</h3>
            <span className="text-xs font-bold text-[#1a56db] flex items-center">
              <span className="mr-1">↗</span> 12% increase vs. last year
            </span>
          </div>
        </div>

        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between">
          <div className="flex items-center justify-between mb-4">
            <p className="text-xs font-bold text-gray-500 uppercase tracking-wider">Reliability Score</p>
            <span className="text-sm font-bold text-[#1a56db]">98.4%</span>
          </div>
          <div className="flex items-end justify-between h-12 space-x-1">
             <div className="w-full bg-[#e1effe] rounded-t-sm h-[40%]"></div>
             <div className="w-full bg-[#e1effe] rounded-t-sm h-[50%]"></div>
             <div className="w-full bg-[#e1effe] rounded-t-sm h-[60%]"></div>
             <div className="w-full bg-[#e1effe] rounded-t-sm h-[65%]"></div>
             <div className="w-full bg-[#e1effe] rounded-t-sm h-[80%]"></div>
             <div className="w-full bg-[#e1effe] rounded-t-sm h-[90%]"></div>
             <div className="w-full bg-[#1a56db] rounded-t-sm h-[98%]"></div>
          </div>
          <p className="text-[10px] text-gray-400 mt-2 font-medium">Avg lead time: 4 days (top 5%)</p>
        </div>

        <div className="bg-[#1a56db] rounded-xl shadow-sm border border-[#1a56db] p-6 text-white flex flex-col justify-between relative overflow-hidden">
          <div className="absolute top-0 right-0 p-4 opacity-20">
             <ShoppingCart className="w-24 h-24" />
          </div>
          <div className="relative z-10">
            <div className="flex items-center mb-1">
              <p className="text-xs font-bold text-blue-100 uppercase tracking-wider mr-2">Active Orders</p>
              <PackageCheck className="w-4 h-4 text-white" />
            </div>
            <h3 className="text-4xl font-bold text-white mb-2">
              {supplier.active_orders_count || 0}
            </h3>
            <span className="text-xs font-medium text-blue-100">
              2 items in transit today
            </span>
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
                {['General Info', 'Purchase Orders', 'GRNs', 'Documents', 'Notes'].map(tab => (
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
              {activeTab === 'General Info' && (
                <div>
                  <div className="bg-gray-50 rounded-xl border border-gray-200 p-6 mb-6">
                    <div className="flex justify-between items-center mb-4">
                      <h3 className="text-sm font-bold text-gray-900">Supplier Details</h3>
                      <button onClick={() => setIsEditModalOpen(true)} className="text-[#1a56db] hover:bg-blue-50 p-1.5 rounded"><Edit className="w-4 h-4" /></button>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-12">
                       <div>
                         <p className="text-[10px] font-bold text-gray-500 uppercase mb-1">Legal Name</p>
                         <p className="text-sm text-gray-900 font-medium">{supplier.name} LLC</p>
                       </div>
                       <div>
                         <p className="text-[10px] font-bold text-gray-500 uppercase mb-1">Tax ID / GST</p>
                         <p className="text-sm text-gray-900 font-medium">{supplier.gst_number || 'XX-XXXXXX'}</p>
                       </div>
                       <div>
                         <p className="text-[10px] font-bold text-gray-500 uppercase mb-1">Main Office</p>
                         <p className="text-sm text-gray-900 font-medium">{supplier.address || 'Address not provided'}</p>
                       </div>
                       <div>
                         <p className="text-[10px] font-bold text-gray-500 uppercase mb-1">Industry</p>
                         <p className="text-sm text-gray-900 font-medium">{supplier.category || 'Logistics & Warehousing'}</p>
                       </div>
                    </div>
                  </div>

                  <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 overflow-hidden relative h-48 mb-8">
                     <div className="absolute inset-0 bg-gray-200">
                       <img src="https://upload.wikimedia.org/wikipedia/commons/8/80/World_map_-_low_resolution.svg" className="w-full h-full object-cover opacity-30 grayscale" alt="Map" />
                     </div>
                     <div className="absolute bottom-4 left-4 bg-white px-3 py-1.5 rounded shadow-sm flex items-center">
                       <MapPin className="w-3 h-3 text-[#1a56db] mr-2" />
                       <span className="text-xs font-bold text-gray-900">San Francisco Distribution Hub</span>
                     </div>
                  </div>

                  <div className="flex justify-between items-center mb-4">
                    <h3 className="text-lg font-bold text-gray-900">Recent Purchase Orders</h3>
                    <button className="text-sm font-bold text-[#1a56db] hover:underline">View All History</button>
                  </div>
                  <div className="overflow-x-auto">
                    <table className="w-full text-left border-collapse">
                      <thead>
                        <tr className="border-b border-gray-100 bg-gray-50">
                          <th className="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Order ID</th>
                          <th className="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                          <th className="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Amount</th>
                          <th className="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                          <th className="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Action</th>
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-gray-50">
                        {supplier.purchase_orders?.length === 0 ? (
                          <tr><td colSpan="5" className="py-4 text-center text-sm text-gray-500">No purchase orders found.</td></tr>
                        ) : (
                          supplier.purchase_orders?.slice(0, 4).map(po => (
                            <tr key={po.id} className="hover:bg-gray-50">
                              <td className="px-4 py-4 text-sm font-bold text-[#1a56db]">{po.po_number}</td>
                              <td className="px-4 py-4 text-sm text-gray-900 font-medium">
                                {new Date(po.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                              </td>
                              <td className="px-4 py-4 text-sm font-bold text-gray-900">
                                ₹{parseFloat(po.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2})}
                              </td>
                              <td className="px-4 py-4">
                                <span className={`inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border ${
                                  po.status === 'Approved' ? 'bg-green-50 text-green-700 border-green-200' :
                                  po.status === 'Draft' ? 'bg-gray-50 text-gray-700 border-gray-200' :
                                  'bg-blue-50 text-blue-700 border-blue-200'
                                }`}>
                                  {po.status}
                                </span>
                              </td>
                              <td className="px-4 py-4 text-right">
                                <button className="text-gray-400 hover:text-gray-600"><MoreVertical className="w-4 h-4" /></button>
                              </td>
                            </tr>
                          ))
                        )}
                      </tbody>
                    </table>
                  </div>
                </div>
              )}

              {activeTab === 'Purchase Orders' && (
                <div className="text-center py-8 text-gray-500 font-medium">Full Purchase Orders list</div>
              )}

              {activeTab === 'GRNs' && (
                <div className="text-center py-8 text-gray-500 font-medium">Goods Receipt Notes history</div>
              )}
              
              {activeTab === 'Documents' && (
                <DocumentManager documentableType="App\Models\Supplier" documentableId={supplier.id} initialDocuments={supplier.documents} />
              )}

              {activeTab === 'Notes' && (
                <NotesSection notableType="App\Models\Supplier" notableId={supplier.id} initialNotes={supplier.notes} />
              )}
            </div>
          </div>
        </div>

        {/* Right Sidebar Area */}
        <div className="w-full lg:w-80 space-y-6">
          <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div className="flex justify-between items-center mb-4">
              <h3 className="text-xs font-bold text-gray-500 uppercase tracking-wider">Primary Contacts</h3>
              <button className="text-[#1a56db] hover:bg-blue-50 p-1.5 rounded"><Edit className="w-3 h-3" /></button>
            </div>
            <div className="space-y-4">
              <div className="flex items-center justify-between border-b border-gray-100 pb-4">
                <div className="flex items-center">
                  <div className="w-10 h-10 rounded-full overflow-hidden mr-3">
                    <img src="https://ui-avatars.com/api/?name=Sarah+Chen&background=random" alt="Avatar" />
                  </div>
                  <div>
                    <p className="text-sm font-bold text-gray-900">Sarah Chen</p>
                    <p className="text-xs text-gray-500 font-medium">Account Executive</p>
                  </div>
                </div>
                <button className="border border-gray-200 text-gray-500 hover:bg-gray-50 p-1.5 rounded shadow-sm"><Mail className="w-4 h-4" /></button>
              </div>
              <div className="flex items-center justify-between pb-2">
                <div className="flex items-center">
                  <div className="w-10 h-10 rounded-full overflow-hidden mr-3">
                    <img src="https://ui-avatars.com/api/?name=Mark+Thompson&background=random" alt="Avatar" />
                  </div>
                  <div>
                    <p className="text-sm font-bold text-gray-900">Mark Thompson</p>
                    <p className="text-xs text-gray-500 font-medium">Operations Lead</p>
                  </div>
                </div>
                <button className="border border-gray-200 text-gray-500 hover:bg-gray-50 p-1.5 rounded shadow-sm"><Phone className="w-4 h-4" /></button>
              </div>
              <button className="w-full py-2 border border-dashed border-gray-300 rounded text-sm font-bold text-gray-500 hover:bg-gray-50 transition-colors">
                + Add Contact
              </button>
            </div>
          </div>

          <div className="bg-gray-50 rounded-xl border border-gray-200 p-6">
             <h3 className="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-3">Internal Tags</h3>
             <div className="flex flex-wrap gap-2 mb-6">
                <span className="px-2 py-1 bg-white border border-gray-200 text-gray-700 text-xs font-bold rounded">High Reliability</span>
                <span className="px-2 py-1 bg-[#e1effe] text-[#1a56db] border border-blue-200 text-xs font-bold rounded">FAST LOGISTICS</span>
                <span className="px-2 py-1 bg-white border border-gray-200 text-gray-700 text-xs font-bold rounded">G20 PRIORITE</span>
                <span className="px-2 py-1 bg-blue-50 border border-blue-200 text-blue-700 text-xs font-bold rounded">Tech Infrastructure</span>
             </div>

             <h3 className="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Internal Notes</h3>
             <p className="text-xs text-gray-600 italic">"Global Logistics consistently beats delivery estimates by 2-3 days. Recommended to use for contract supply for Q4 scaling."</p>
          </div>
        </div>
      </div>

      {isEditModalOpen && (
        <SupplierFormModal 
          isOpen={isEditModalOpen} 
          onClose={() => setIsEditModalOpen(false)} 
          onSuccess={fetchSupplier}
          supplier={supplier}
        />
      )}
    </div>
  );
}
