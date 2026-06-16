import { useState, useEffect } from 'react';
import { 
  Plus, Search, Download, TrendingUp, Clock, AlertCircle, Filter, ChevronDown, Trash2, FileText, CheckCircle, Truck, Package, List
} from 'lucide-react';
import api from '../../services/api';

export default function SalesOrdersPage() {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchQuery, setSearchQuery] = useState('');
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [customers, setCustomers] = useState([]);
  const [products, setProducts] = useState([]);
  const [branches, setBranches] = useState([]);
  const [newOrder, setNewOrder] = useState({ customer_id: '', branch_id: '', items: [{ product_id: '', quantity: 1, unit_price: 0 }] });
  const [activeTab, setActiveTab] = useState('All Orders');

  useEffect(() => {
    fetchOrders();
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      const [custRes, prodRes, branchRes] = await Promise.all([
        api.get('/customers'),
        api.get('/products'),
        api.get('/branches')
      ]);
      setCustomers(custRes.data.data || custRes.data);
      setProducts(prodRes.data);
      setBranches(branchRes.data);
    } catch (error) {
      console.error("Error fetching data:", error);
    }
  };

  const fetchOrders = async () => {
    setLoading(true);
    try {
      const res = await api.get('/sales/orders');
      setOrders(res.data);
    } catch (error) {
      console.error("Error fetching sales orders:", error);
    } finally {
      setLoading(false);
    }
  };

  const handleCreateSO = async (e, status = 'Pending Approval') => {
    if (e) e.preventDefault();

    // Frontend Validation
    if (!newOrder.customer_id) return alert("Please select a Customer.");
    if (!newOrder.branch_id) return alert("Please select a Warehouse Destination.");
    if (newOrder.items.length === 0) return alert("Please add at least one line item.");
    for (const item of newOrder.items) {
      if (!item.product_id) return alert("Please select a product for all line items.");
      if (item.quantity <= 0) return alert("Quantity must be at least 1.");
      if (item.unit_price < 0) return alert("Unit price cannot be negative.");
    }

    try {
      await api.post('/sales/orders', { ...newOrder, status });
      setIsModalOpen(false);
      fetchOrders();
      setNewOrder({ customer_id: '', branch_id: '', items: [{ product_id: '', quantity: 1, unit_price: 0 }] });
    } catch (error) {
      console.error("Error creating SO:", error);
      const msg = error.response?.data?.message || "Failed to create Sales Order.";
      alert(msg);
    }
  };

  const approveOrder = async (id) => {
    if (!window.confirm("Are you sure you want to approve this Sales Order?")) return;
    try {
      await api.post(`/sales/orders/${id}/approve`);
      fetchOrders();
    } catch (error) {
      console.error("Error approving order:", error);
      alert("Failed to approve order.");
    }
  };

  const deleteOrder = async (id) => {
    if (!window.confirm("Are you sure you want to delete this Sales Order? This cannot be undone.")) return;
    try {
      await api.delete(`/sales/orders/${id}`);
      fetchOrders();
    } catch (error) {
      console.error("Error deleting order:", error);
      alert("Failed to delete Sales Order.");
    }
  };

  // Helper functions for mock badges
  const getPaymentBadge = (status) => {
    // Mock logic based on status since payment isn't fully implemented in backend yet
    if (status === 'Approved' || status === 'Fully Delivered') {
      return (
        <span className="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
          <span className="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span>
          Paid
        </span>
      );
    }
    if (status === 'Partially Delivered') {
      return (
        <span className="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-50 text-yellow-700 border border-yellow-200">
          <span className="w-1.5 h-1.5 bg-yellow-500 rounded-full mr-1.5"></span>
          Partial
        </span>
      );
    }
    return (
      <span className="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">
        <span className="w-1.5 h-1.5 bg-red-500 rounded-full mr-1.5"></span>
        Unpaid
      </span>
    );
  };

  const getFulfillmentBadge = (status) => {
    if (status === 'Fully Delivered') {
      return (
        <span className="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
          <span className="w-1.5 h-1.5 bg-gray-500 rounded-full mr-1.5"></span>
          Delivered
        </span>
      );
    }
    if (status === 'Approved' || status === 'Partially Delivered') {
      return (
        <span className="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
          <span className="w-1.5 h-1.5 bg-blue-500 rounded-full mr-1.5"></span>
          Processing
        </span>
      );
    }
    return (
      <span className="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-50 text-gray-600 border border-gray-200">
        <span className="w-1.5 h-1.5 bg-gray-400 rounded-full mr-1.5"></span>
        Draft
      </span>
    );
  };

  const filteredOrders = orders.filter(o => o.so_number?.toLowerCase().includes(searchQuery.toLowerCase()));

  // Calculate stats
  const totalAmount = orders.reduce((sum, order) => sum + parseFloat(order.total_amount || 0), 0);
  const pendingOrders = orders.filter(o => o.status !== 'Fully Delivered').length;

  return (
    <div className="space-y-6 max-w-7xl mx-auto pb-10">
      {/* Header */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Sales Orders</h1>
          <p className="text-sm text-gray-500 mt-1">Manage and track your global enterprise sales pipeline.</p>
        </div>
        <div className="flex space-x-3">
          <button className="inline-flex items-center px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors shadow-sm">
            <Download className="w-4 h-4 mr-2" />
            Export
          </button>
          <button 
            onClick={() => setIsModalOpen(true)}
            className="inline-flex items-center px-4 py-2 bg-[#1a56db] hover:bg-[#1e40af] text-white text-sm font-medium rounded-lg transition-colors shadow-sm"
          >
            <Plus className="w-4 h-4 mr-2" />
            New Sales Order
          </button>
        </div>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-start">
          <div className="p-3 bg-blue-50 rounded-full text-blue-600 mr-4">
            <FileText className="w-6 h-6" />
          </div>
          <div>
            <p className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Revenue</p>
            <h3 className="text-2xl font-bold text-gray-900">₹{totalAmount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</h3>
          </div>
        </div>
        
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-start">
          <div className="p-3 bg-gray-50 rounded-full text-gray-500 mr-4 border border-gray-100">
            <Clock className="w-6 h-6" />
          </div>
          <div>
            <p className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Pending Fulfillment</p>
            <h3 className="text-2xl font-bold text-gray-900">{pendingOrders}</h3>
          </div>
        </div>

        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-start">
          <div className="p-3 bg-red-50 rounded-full text-red-500 mr-4">
            <AlertCircle className="w-6 h-6" />
          </div>
          <div>
            <p className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">High Priority Orders</p>
            <h3 className="text-2xl font-bold text-gray-900">{orders.filter(o => o.status === 'Pending Approval').length}</h3>
          </div>
        </div>
      </div>

      {/* Main Table Area */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        {/* Tabs & Filters */}
        <div className="border-b border-gray-200 px-2 flex flex-col sm:flex-row justify-between items-center bg-gray-50/50">
          <div className="flex overflow-x-auto w-full sm:w-auto">
            {['All Orders', 'Drafts', 'In Fulfillment', 'Shipped', 'Cancelled'].map((tab) => (
              <button
                key={tab}
                onClick={() => setActiveTab(tab)}
                className={`whitespace-nowrap px-6 py-4 text-sm font-medium border-b-2 transition-colors ${
                  activeTab === tab
                    ? 'border-[#1a56db] text-[#1a56db]'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                }`}
              >
                {tab}
              </button>
            ))}
          </div>
          <div className="flex items-center space-x-3 p-3 sm:p-0 pr-4 w-full sm:w-auto justify-end">
            <button className="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-md transition-colors">
              <Filter className="w-4 h-4 mr-2" />
              Advanced Filters
            </button>
            <div className="h-6 border-l border-gray-300 mx-2"></div>
            <button className="inline-flex items-center text-sm font-medium text-gray-700 hover:text-gray-900">
              <span className="text-gray-400 mr-2 font-normal">SORT BY:</span> Latest First
              <ChevronDown className="w-4 h-4 ml-1" />
            </button>
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-white">
              <tr>
                <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-40">Order #</th>
                <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Customer</th>
                <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Amount</th>
                <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Payment</th>
                <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fulfillment</th>
                <th className="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-100">
              {loading ? (
                <tr>
                  <td colSpan="7" className="px-6 py-12 text-center">
                    <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-[#1a56db]"></div>
                    <p className="mt-2 text-sm text-gray-500">Loading sales orders...</p>
                  </td>
                </tr>
              ) : filteredOrders.length === 0 ? (
                <tr>
                  <td colSpan="7" className="px-6 py-12 text-center text-gray-500">
                    No sales orders found matching your criteria.
                  </td>
                </tr>
              ) : (
                filteredOrders.map((order) => {
                  const customer = customers.find(c => c.id === order.customer_id);
                  const customerName = customer ? customer.name : `Customer #${order.customer_id}`;
                  const customerInitials = customerName.substring(0, 2).toUpperCase();
                  
                  return (
                  <tr key={order.id} className="hover:bg-gray-50 transition-colors">
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="font-bold text-gray-900">{order.so_number}</div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex items-center">
                        <div className="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xs">
                          {customerInitials}
                        </div>
                        <div className="ml-3">
                          <div className="text-sm font-semibold text-gray-900">{customerName}</div>
                          <div className="text-xs text-gray-500">{customer?.email || 'contact@customer.com'}</div>
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="text-sm font-medium text-gray-900">
                        {new Date(order.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                      ${parseFloat(order.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      {getPaymentBadge(order.status)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      {getFulfillmentBadge(order.status)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      {order.status === 'Pending Approval' && (
                        <button 
                          onClick={() => approveOrder(order.id)}
                          className="text-[#1a56db] hover:text-[#1e40af] mr-4 font-semibold"
                        >
                          Approve
                        </button>
                      )}
                      <button 
                        onClick={() => deleteOrder(order.id)}
                        className="text-red-400 hover:text-red-600"
                        title="Delete Order"
                      >
                        <Trash2 className="w-5 h-5" />
                      </button>
                    </td>
                  </tr>
                )})
              )}
            </tbody>
          </table>
        </div>
        
        {/* Pagination placeholder */}
        <div className="px-6 py-4 border-t border-gray-200 flex items-center justify-between bg-gray-50">
          <span className="text-sm text-gray-500">Showing 1 to {filteredOrders.length} of {filteredOrders.length} orders</span>
          <div className="flex space-x-1">
            <button className="px-3 py-1 rounded bg-white border border-gray-300 text-gray-500 hover:bg-gray-50">«</button>
            <button className="px-3 py-1 rounded bg-[#1a56db] text-white">1</button>
            <button className="px-3 py-1 rounded bg-white border border-gray-300 text-gray-700 hover:bg-gray-50">2</button>
            <button className="px-3 py-1 rounded bg-white border border-gray-300 text-gray-700 hover:bg-gray-50">»</button>
          </div>
        </div>
      </div>

      {/* Main Table Area Ends */}

      {/* Massive Modal for Create SO */}
      {isModalOpen && (
        <div className="fixed inset-0 bg-gray-900/40 backdrop-blur-sm flex items-center justify-center z-50 p-4 sm:p-6">
          <div className="bg-gray-50 rounded-xl shadow-2xl w-full max-w-5xl h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            {/* Modal Header */}
            <div className="bg-white px-6 py-4 border-b border-gray-200 flex justify-between items-center">
              <div className="flex items-center text-sm font-semibold text-gray-500 uppercase tracking-wider">
                <button onClick={() => setIsModalOpen(false)} className="text-[#1a56db] hover:underline mr-2">&larr; SALES ORDERS</button>
                / NEW
              </div>
              <div className="flex items-center space-x-3">
                <span className="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-bold tracking-widest border border-gray-200">
                  <span className="w-1.5 h-1.5 bg-gray-400 rounded-full inline-block mr-1.5"></span>
                  DRAFT
                </span>
                <button 
                  onClick={handleCreateSO}
                  className="inline-flex items-center px-6 py-2 bg-[#1a56db] hover:bg-[#1e40af] text-white text-sm font-bold rounded-lg shadow-sm transition-colors"
                >
                  Submit for Approval
                </button>
              </div>
            </div>

            {/* Modal Content */}
            <div className="flex-1 overflow-y-auto p-6">
              <div className="flex flex-col lg:flex-row gap-6">
                
                {/* Left Column */}
                <div className="flex-1 space-y-6">
                  <h1 className="text-3xl font-bold text-gray-900">Create Sales Order</h1>
                  
                  {/* Customer Details Card */}
                  <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 className="flex items-center text-lg font-bold text-gray-900 mb-6">
                      <FileText className="w-5 h-5 text-[#1a56db] mr-2" />
                      Customer Details
                    </h2>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div>
                        <label className="block text-sm font-medium text-gray-600 mb-1">Select Customer</label>
                        <select 
                          className="w-full border border-gray-200 rounded-lg p-3 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#1a56db] focus:border-transparent transition-all"
                          value={newOrder.customer_id}
                          onChange={(e) => setNewOrder({...newOrder, customer_id: e.target.value})}
                          required
                        >
                          <option value="">Select Customer...</option>
                          {customers.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                        </select>
                      </div>
                      <div>
                        <label className="block text-sm font-medium text-gray-600 mb-1">Warehouse Origin</label>
                        <select 
                          className="w-full border border-gray-200 rounded-lg p-3 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#1a56db] focus:border-transparent transition-all"
                          value={newOrder.branch_id}
                          onChange={(e) => setNewOrder({...newOrder, branch_id: e.target.value})}
                          required
                        >
                          <option value="">Select Warehouse...</option>
                          {branches.map(b => <option key={b.id} value={b.id}>{b.name}</option>)}
                        </select>
                      </div>
                      <div>
                        <label className="block text-sm font-medium text-gray-600 mb-1">Order Date</label>
                        <input 
                          type="date"
                          className="w-full border border-gray-200 rounded-lg p-3 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#1a56db] focus:border-transparent transition-all"
                          defaultValue={new Date().toISOString().split('T')[0]}
                        />
                      </div>
                      <div>
                        <label className="block text-sm font-medium text-gray-600 mb-1">Expected Delivery</label>
                        <input 
                          type="date"
                          className="w-full border border-gray-200 rounded-lg p-3 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#1a56db] focus:border-transparent transition-all"
                        />
                      </div>
                    </div>
                  </div>

                  {/* Line Items Card */}
                  <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div className="p-6 border-b border-gray-100 flex justify-between items-center">
                      <h2 className="flex items-center text-lg font-bold text-gray-900">
                        <List className="w-5 h-5 text-[#1a56db] mr-2" />
                        Line Items
                      </h2>
                      <button 
                        type="button"
                        onClick={() => setNewOrder({...newOrder, items: [...newOrder.items, { product_id: '', quantity: 1, unit_price: 0 }]})}
                        className="flex items-center text-[#1a56db] font-semibold text-sm hover:text-[#1e40af]"
                      >
                        <Plus className="w-4 h-4 mr-1" /> Add New Product
                      </button>
                    </div>
                    
                    <div className="overflow-x-auto">
                      <table className="w-full text-left border-collapse">
                        <thead>
                          <tr className="bg-gray-50 border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500 font-bold">
                            <th className="p-4">Product Code / Name</th>
                            <th className="p-4 w-24 text-center">Quantity</th>
                            <th className="p-4 w-32 text-right">Unit Price</th>
                            <th className="p-4 w-24 text-center">Tax (GST)</th>
                            <th className="p-4 w-32 text-right">Total</th>
                            <th className="p-4 w-12 text-center"></th>
                          </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                          {newOrder.items.map((item, index) => (
                            <tr key={index} className="hover:bg-gray-50/50">
                              <td className="p-4">
                                <select
                                  className="w-full bg-transparent border-0 border-b border-gray-200 focus:ring-0 focus:border-[#1a56db] font-semibold text-gray-900 p-0 pb-1"
                                  value={item.product_id}
                                  onChange={(e) => {
                                    const items = [...newOrder.items];
                                    const prod = products.find(p => p.id == e.target.value);
                                    items[index].product_id = e.target.value;
                                    if(prod) items[index].unit_price = parseFloat(prod.selling_price || 0);
                                    setNewOrder({...newOrder, items});
                                  }}
                                  required
                                >
                                  <option value="">Select Product</option>
                                  {products.map(p => <option key={p.id} value={p.id}>{p.sku} - {p.name}</option>)}
                                </select>
                              </td>
                              <td className="p-4">
                                <input
                                  type="number"
                                  min="1"
                                  className="w-full text-center bg-transparent border-0 border-b border-gray-200 focus:ring-0 focus:border-[#1a56db] p-0 pb-1"
                                  value={item.quantity}
                                  onChange={(e) => {
                                    const items = [...newOrder.items];
                                    items[index].quantity = parseInt(e.target.value) || 0;
                                    setNewOrder({...newOrder, items});
                                  }}
                                  required
                                />
                              </td>
                              <td className="p-4">
                                <input
                                  type="number"
                                  step="0.01"
                                  min="0"
                                  className="w-full text-right bg-transparent border-0 border-b border-gray-200 focus:ring-0 focus:border-[#1a56db] p-0 pb-1"
                                  value={item.unit_price}
                                  onChange={(e) => {
                                    const items = [...newOrder.items];
                                    items[index].unit_price = parseFloat(e.target.value) || 0;
                                    setNewOrder({...newOrder, items});
                                  }}
                                  required
                                />
                              </td>
                              <td className="p-4 text-center text-gray-500 text-sm">
                                18%
                              </td>
                              <td className="p-4 text-right font-bold text-gray-900">
                                ${(item.quantity * item.unit_price).toLocaleString(undefined, {minimumFractionDigits: 2})}
                              </td>
                              <td className="p-4 text-center">
                                <button
                                  type="button"
                                  className="text-red-400 hover:text-red-600 transition-colors"
                                  onClick={() => {
                                    const items = newOrder.items.filter((_, i) => i !== index);
                                    setNewOrder({...newOrder, items});
                                  }}
                                >
                                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M3 6h18"></path><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path></svg>
                                </button>
                              </td>
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>

                {/* Right Column */}
                <div className="w-full lg:w-80 xl:w-96 space-y-6">
                  {/* Blue Summary Card */}
                  <div className="bg-[#1a56db] rounded-xl shadow-md p-6 text-white relative overflow-hidden">
                    <div className="absolute right-0 top-0 opacity-10 p-4">
                      <FileText className="w-24 h-24" />
                    </div>
                    <h3 className="font-bold text-lg mb-6 relative z-10">Order Summary</h3>
                    
                    <div className="space-y-4 relative z-10 text-blue-100 text-sm">
                      <div className="flex justify-between">
                        <span>Subtotal</span>
                        <span className="font-medium text-white">
                          ${newOrder.items.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0).toLocaleString(undefined, {minimumFractionDigits: 2})}
                        </span>
                      </div>
                      <div className="flex justify-between">
                        <span>Total GST (Tax)</span>
                        <span className="font-medium text-white">
                          ${(newOrder.items.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0) * 0.18).toLocaleString(undefined, {minimumFractionDigits: 2})}
                        </span>
                      </div>
                      <div className="flex justify-between border-b border-blue-500/50 pb-4">
                        <span>Shipping Estimate</span>
                        <span className="font-medium text-white">₹250.00</span>
                      </div>
                      <div className="flex justify-between items-center pt-2">
                        <span className="text-base text-white">Total Amount</span>
                        <span className="text-2xl font-bold text-white">
                          ₹{((newOrder.items.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0) * 1.18) + 250).toLocaleString(undefined, {minimumFractionDigits: 2})}
                        </span>
                      </div>
                      <p className="text-[10px] uppercase tracking-wider opacity-60 text-right mt-1">Currency: INR (Indian Rupee)</p>
                    </div>
                  </div>

                  {/* Additional Details */}
                  <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
                    <div>
                      <h3 className="flex items-center text-sm font-bold text-gray-800 mb-3">
                        <FileText className="w-4 h-4 text-gray-400 mr-2" /> Internal Remarks
                      </h3>
                      <textarea 
                        className="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm focus:ring-[#1a56db] focus:border-[#1a56db]"
                        rows="3"
                        placeholder="Add any internal processing notes..."
                      ></textarea>
                    </div>
                    
                    <div>
                      <h3 className="flex items-center text-sm font-bold text-gray-800 mb-3">
                        <FileText className="w-4 h-4 text-gray-400 mr-2" /> Terms & Conditions
                      </h3>
                      <select className="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm focus:ring-[#1a56db] focus:border-[#1a56db]">
                        <option>Standard Net 30</option>
                        <option>Net 60</option>
                        <option>Due on Receipt</option>
                      </select>
                    </div>

                    <div className="flex items-start p-4 bg-red-50 text-red-800 rounded-lg text-sm border border-red-100">
                      <AlertCircle className="w-5 h-5 text-red-500 mr-2 flex-shrink-0 mt-0.5" />
                      <p>Orders over ₹10,000 require Department Head approval via workflow.</p>
                    </div>
                    
                    <button 
                      type="button"
                      onClick={(e) => handleCreateSO(e, 'Draft')}
                      className="w-full px-4 py-3 bg-white border-2 border-gray-200 text-[#1a56db] font-bold rounded-lg hover:bg-gray-50 transition-colors"
                    >
                      Save as Draft
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
