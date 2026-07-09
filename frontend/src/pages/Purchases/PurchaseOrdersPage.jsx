import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Plus, Search, Download, TrendingUp, Clock, AlertCircle, Filter, ChevronDown, Trash2, FileText, CheckCircle, ShoppingBag, Package, List, Eye
} from 'lucide-react';
import api from '../../services/api';
import ProductSelect from '../../components/Shared/ProductSelect';

export default function PurchaseOrdersPage() {
  const navigate = useNavigate();
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchQuery, setSearchQuery] = useState('');
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [suppliers, setSuppliers] = useState([]);
  const [products, setProducts] = useState([]);
  const [newOrder, setNewOrder] = useState({ supplier_id: '', gst_type: 'cgst', shipping_cost: 0, items: [{ product_id: '', custom_product_name: '', quantity: 1, unit_cost: 0, gst_rate: 18, hsn_code: '' }] });
  const [activeTab, setActiveTab] = useState('All Orders');
  const [currentPage, setCurrentPage] = useState(1);
  const pageSize = 25;

  useEffect(() => {
    fetchOrders();
    fetchData();
  }, []);

  useEffect(() => {
    setCurrentPage(1);
  }, [searchQuery, activeTab]);

  const fetchData = async () => {
    try {
      const [suppRes, prodRes] = await Promise.all([
        api.get('/suppliers'),
        api.get('/products')
      ]);
      setSuppliers(suppRes.data.data || suppRes.data);
      setProducts(prodRes.data.data || prodRes.data);
    } catch (error) {
      console.error("Error fetching data:", error);
    }
  };

  const fetchOrders = async () => {
    setLoading(true);
    try {
      const res = await api.get('/purchases/orders');
      setOrders(res.data);
    } catch (error) {
      console.error("Error fetching purchase orders:", error);
    } finally {
      setLoading(false);
    }
  };

  const handleCreatePO = async (e, status = 'Pending Approval') => {
    if (e) e.preventDefault();

    // Frontend Validation
    if (!newOrder.supplier_id) return alert("Please select a Supplier.");
    if (newOrder.items.length === 0) return alert("Please add at least one line item.");
    for (const item of newOrder.items) {
      if (!item.product_id && !item.custom_product_name) return alert("Please select a product for all line items.");
      if (item.quantity <= 0) return alert("Quantity must be at least 1.");
      if (item.unit_cost < 0) return alert("Unit cost cannot be negative.");
    }

    try {
      await api.post('/purchases/orders', { ...newOrder, status });
      setIsModalOpen(false);
      fetchOrders();
      setNewOrder({ supplier_id: '', gst_type: 'cgst', shipping_cost: 0, items: [{ product_id: '', custom_product_name: '', quantity: 1, unit_cost: 0, gst_rate: 18, hsn_code: '' }] });
    } catch (error) {
      console.error("Error creating PO:", error);
      const msg = error.response?.data?.message || "Failed to create Purchase Order.";
      alert(msg);
    }
  };

  const approveOrder = async (id) => {
    if (!window.confirm("Are you sure you want to approve this Purchase Order?")) return;
    try {
      await api.post(`/purchases/orders/${id}/approve`);
      fetchOrders();
    } catch (error) {
      console.error("Error approving order:", error);
      alert("Failed to approve order.");
    }
  };

  const rejectOrder = async (id) => {
    if (!window.confirm("Are you sure you want to reject this Purchase Order?")) return;
    try {
      await api.post(`/purchases/orders/${id}/reject`);
      fetchOrders();
    } catch (error) {
      console.error("Error rejecting order:", error);
      alert("Failed to reject order.");
    }
  };

  const deleteOrder = async (id) => {
    if (!window.confirm("Are you sure you want to delete this Purchase Order? This cannot be undone.")) return;
    try {
      await api.delete(`/purchases/orders/${id}`);
      fetchOrders();
    } catch (error) {
      console.error("Error deleting order:", error);
      alert("Failed to delete Purchase Order.");
    }
  };

  const getStatusBadge = (status) => {
    if (status === 'Approved') {
      return (
        <span className="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
          <span className="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span>
          Approved
        </span>
      );
    }
    if (status === 'Partially Received') {
      return (
        <span className="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-50 text-yellow-700 border border-yellow-200">
          <span className="w-1.5 h-1.5 bg-yellow-500 rounded-full mr-1.5"></span>
          Partial
        </span>
      );
    }
    if (status === 'Received' || status === 'Fully Received') {
      return (
        <span className="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
          <span className="w-1.5 h-1.5 bg-blue-500 rounded-full mr-1.5"></span>
          Received
        </span>
      );
    }
    if (status === 'Rejected' || status === 'Cancelled') {
      return (
        <span className="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">
          <span className="w-1.5 h-1.5 bg-red-500 rounded-full mr-1.5"></span>
          {status}
        </span>
      );
    }
    if (status === 'Pending Approval') {
      return (
        <span className="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-orange-50 text-orange-700 border border-orange-200">
          <span className="w-1.5 h-1.5 bg-orange-500 rounded-full mr-1.5"></span>
          Pending Approval
        </span>
      );
    }
    return (
      <span className="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-50 text-gray-700 border border-gray-200">
        <span className="w-1.5 h-1.5 bg-gray-500 rounded-full mr-1.5"></span>
        Pending
      </span>
    );
  };

  const statusFilterMap = {
    'All Orders': '',
    'Drafts': 'Draft',
    'Awaiting Receipt': 'Approved,Partially Received',
    'Received': 'Fully Received',
    'Cancelled': 'Cancelled',
  };

  const filteredOrders = orders.filter(o => {
    const matchesSearch = o.po_number?.toLowerCase().includes(searchQuery.toLowerCase());
    const statusFilter = statusFilterMap[activeTab];
    const matchesTab = !statusFilter || statusFilter.split(',').includes(o.status);
    return matchesSearch && matchesTab;
  });

  const totalPages = Math.ceil(filteredOrders.length / pageSize);
  const paginatedOrders = filteredOrders.slice((currentPage - 1) * pageSize, currentPage * pageSize);

  const handleExport = () => {
    const headers = ['PO #', 'Supplier', 'Date', 'Total Amount', 'Tax Amount', 'Shipping Cost', 'Status'];
    const rows = filteredOrders.map(o => {
      const supplier = suppliers.find(s => s.id === o.supplier_id);
      return [
        o.po_number,
        supplier?.name || `Supplier #${o.supplier_id}`,
        new Date(o.created_at).toLocaleDateString(),
        o.total_amount,
        o.tax_amount,
        o.shipping_cost || 0,
        o.status,
      ];
    });
    const csv = [headers, ...rows].map(r => r.map(v => `"${v ?? ''}"`).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `purchase-orders-${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
    URL.revokeObjectURL(url);
  };

  // Calculate stats
  const totalAmount = orders.reduce((sum, order) => sum + parseFloat(order.total_amount || 0), 0);
  const pendingOrders = orders.filter(o => o.status === 'Pending Approval').length;

  return (
    <div className="space-y-6 max-w-7xl mx-auto pb-10">
      {/* Header */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Purchase Orders</h1>
          <p className="text-sm text-gray-500 mt-1">Manage and track your global enterprise procurement pipeline.</p>
        </div>
        <div className="flex flex-wrap gap-2">
          <button onClick={handleExport} className="inline-flex items-center px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors shadow-sm">
            <Download className="w-4 h-4 mr-2" />
            Export
          </button>
          <button
            onClick={() => setIsModalOpen(true)}
            className="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-medium rounded-lg transition-colors shadow-sm"
          >
            <Plus className="w-4 h-4 mr-2" />
            New Purchase Order
          </button>
        </div>
      </div>

      {/* Search */}
      <div className="relative max-w-md">
        <Search className="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
        <input
          value={searchQuery}
          onChange={(e) => setSearchQuery(e.target.value)}
          placeholder="Search by PO number..."
          className="w-full rounded-md border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm focus:border-ring focus:outline-none focus:ring-1 focus:ring-ring"
        />
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-start">
          <div className="p-3 bg-blue-50 rounded-full text-blue-600 mr-4">
            <FileText className="w-6 h-6" />
          </div>
          <div>
            <p className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Procurement</p>
            <h3 className="text-2xl font-bold text-gray-900">₹{totalAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</h3>
          </div>
        </div>

        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-start">
          <div className="p-3 bg-gray-50 rounded-full text-gray-500 mr-4 border border-gray-100">
            <Clock className="w-6 h-6" />
          </div>
          <div>
            <p className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Pending Approval</p>
            <h3 className="text-2xl font-bold text-gray-900">{pendingOrders}</h3>
          </div>
        </div>

        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-start">
          <div className="p-3 bg-red-50 rounded-full text-red-500 mr-4">
            <AlertCircle className="w-6 h-6" />
          </div>
          <div>
            <p className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">High Priority</p>
            <h3 className="text-2xl font-bold text-gray-900">{orders.filter(o => o.status === 'Pending Approval').length}</h3>
          </div>
        </div>
      </div>

      {/* Main Table Area */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        {/* Tabs & Filters */}
        <div className="border-b border-gray-200 px-2 flex flex-col sm:flex-row justify-between items-center bg-gray-50/50">
          <div className="flex overflow-x-auto w-full sm:w-auto">
            {['All Orders', 'Drafts', 'Awaiting Receipt', 'Received', 'Cancelled'].map((tab) => (
              <button
                key={tab}
                onClick={() => setActiveTab(tab)}
                className={`whitespace-nowrap px-6 py-4 text-sm font-medium border-b-2 transition-colors ${activeTab === tab
                  ? 'border-primary/30 text-primary'
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
                <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier</th>
                <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Amount</th>
                <th className="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th className="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-100">
              {loading ? (
                <tr>
                  <td colSpan="6" className="px-6 py-12 text-center">
                    <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary/30"></div>
                    <p className="mt-2 text-sm text-gray-500">Loading purchase orders...</p>
                  </td>
                </tr>
              ) : filteredOrders.length === 0 ? (
                <tr>
                  <td colSpan="6" className="px-6 py-12 text-center text-gray-500">
                    No purchase orders found matching your criteria.
                  </td>
                </tr>
              ) : (
                paginatedOrders.map((order) => {
                  const supplier = suppliers.find(s => s.id === order.supplier_id);
                  const supplierName = supplier ? supplier.name : `Supplier #${order.supplier_id}`;
                  const supplierInitials = supplierName.substring(0, 2).toUpperCase();

                  return (
                    <tr key={order.id} className="hover:bg-gray-50 transition-colors">
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="font-bold text-gray-900">{order.po_number}</div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="flex items-center">
                          <div className="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs">
                            {supplierInitials}
                          </div>
                          <div className="ml-3">
                            <div className="text-sm font-semibold text-gray-900">{supplierName}</div>
                            <div className="text-xs text-gray-500">{supplier?.email || 'contact@supplier.com'}</div>
                          </div>
                        </div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="text-sm font-medium text-gray-900">
                          {new Date(order.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                        </div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                        ₹{parseFloat(order.total_amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        {getStatusBadge(order.status)}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button
                          onClick={() => navigate(`/dashboard/purchases/${order.id}`)}
                          className="text-gray-400 hover:text-primary mr-3"
                          title="Preview Order"
                        >
                          <Eye className="w-5 h-5" />
                        </button>
                        {order.status === 'Pending Approval' && (
                          <>
                            <button
                              onClick={() => approveOrder(order.id)}
                              className="text-green-600 hover:text-green-800 mr-3 font-semibold"
                            >
                              Approve
                            </button>
                            <button
                              onClick={() => rejectOrder(order.id)}
                              className="text-red-600 hover:text-red-800 mr-3 font-semibold"
                            >
                              Reject
                            </button>
                          </>
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
                  )
                })
              )}
            </tbody>
          </table>
        </div>

        {filteredOrders.length > 0 && (
          <div className="px-6 py-4 border-t border-gray-200 flex items-center justify-between bg-gray-50">
            <span className="text-sm text-gray-500">
              Showing {Math.min((currentPage - 1) * pageSize + 1, filteredOrders.length)} to {Math.min(currentPage * pageSize, filteredOrders.length)} of {filteredOrders.length} orders
            </span>
            <div className="flex items-center gap-1">
              <button
                onClick={() => setCurrentPage(p => Math.max(1, p - 1))}
                disabled={currentPage === 1}
                className="px-3 py-1 rounded bg-white border border-gray-300 text-gray-500 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
              >
                ‹
              </button>
              {Array.from({ length: totalPages }, (_, i) => i + 1).map(page => (
                <button
                  key={page}
                  onClick={() => setCurrentPage(page)}
                  className={`px-3 py-1 rounded text-sm font-medium transition-colors ${
                    currentPage === page
                      ? 'bg-primary text-white'
                      : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'
                  }`}
                >
                  {page}
                </button>
              ))}
              <button
                onClick={() => setCurrentPage(p => Math.min(totalPages, p + 1))}
                disabled={currentPage === totalPages}
                className="px-3 py-1 rounded bg-white border border-gray-300 text-gray-500 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
              >
                ›
              </button>
            </div>
          </div>
        )}
      </div>

      {/* Massive Modal for Create PO (Matches Reference Image 1) */}
      {isModalOpen && (
        <div className="fixed inset-0 bg-gray-900/40 backdrop-blur-sm flex items-center justify-center z-50 p-4 sm:p-6">
          <div className="bg-gray-50 rounded-xl shadow-2xl w-full max-w-7xl h-[95vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            {/* Modal Header */}
            <div className="bg-white px-6 py-4 border-b border-gray-200 flex justify-between items-center">
              <div className="flex items-center text-sm font-semibold text-gray-500 uppercase tracking-wider">
                <button onClick={() => setIsModalOpen(false)} className="text-primary hover:underline mr-2">&larr; PURCHASE ORDERS</button>
                / NEW
              </div>
              <div className="flex items-center space-x-3">
                <span className="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-bold tracking-widest border border-gray-200">
                  <span className="w-1.5 h-1.5 bg-gray-400 rounded-full inline-block mr-1.5"></span>
                  DRAFT
                </span>
                <button
                  onClick={handleCreatePO}
                  className="inline-flex items-center px-6 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-bold rounded-lg shadow-sm transition-colors"
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
                  <h1 className="text-3xl font-bold text-gray-900">Create Purchase Order</h1>

                  {/* Supplier Details Card */}
                  <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 className="flex items-center text-lg font-bold text-gray-900 mb-6">
                      <FileText className="w-5 h-5 text-primary mr-2" />
                      Supplier Details
                    </h2>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div>
                        <label className="block text-sm font-medium text-gray-600 mb-1">Select Supplier</label>
                        <select
                          className="w-full border border-gray-200 rounded-lg p-3 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-ring focus:border-transparent transition-all"
                          value={newOrder.supplier_id}
                          onChange={(e) => setNewOrder({ ...newOrder, supplier_id: e.target.value })}
                          required
                        >
                          <option value="" hidden>Select Supplier</option>
                          {suppliers.map(s => <option key={s.id} value={s.id}>{s.name}</option>)}
                        </select>
                      </div>
                      <div>
                        <label className="block text-sm font-medium text-gray-600 mb-1">Order Date</label>
                        <input
                          type="date"
                          className="w-full border border-gray-200 rounded-lg p-3 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-ring focus:border-transparent transition-all"
                          defaultValue={new Date().toISOString().split('T')[0]}
                        />
                      </div>
                      <div>
                        <label className="block text-sm font-medium text-gray-600 mb-1">Expected Delivery</label>
                        <input
                          type="date"
                          className="w-full border border-gray-200 rounded-lg p-3 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-ring focus:border-transparent transition-all"
                        />
                      </div>
                      <div>
                        <label className="block text-sm font-medium text-gray-600 mb-1">GST Type</label>
                        <select
                          className="w-full border border-gray-200 rounded-lg p-3 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-ring focus:border-transparent transition-all"
                          value={newOrder.gst_type}
                          onChange={(e) => setNewOrder({ ...newOrder, gst_type: e.target.value })}
                        >
                          <option value="cgst">CGST + SGST (Intra-State)</option>
                          <option value="igst">IGST (Inter-State)</option>
                        </select>
                      </div>
                      <div>
                        <label className="block text-sm font-medium text-gray-600 mb-1">Shipping Cost (₹)</label>
                        <input
                          type="number"
                          min="0"
                          step="0.01"
                          className="w-full border border-gray-200 rounded-lg p-3 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-ring focus:border-transparent transition-all"
                          value={newOrder.shipping_cost}
                          onChange={(e) => setNewOrder({ ...newOrder, shipping_cost: parseFloat(e.target.value) || 0 })}
                        />
                      </div>
                    </div>
                  </div>

                  {/* Line Items Card */}
                  <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div className="p-6 border-b border-gray-100 flex justify-between items-center">
                      <h2 className="flex items-center text-lg font-bold text-gray-900">
                        <List className="w-5 h-5 text-primary mr-2" />
                        Line Items
                      </h2>
                      <button
                        type="button"
                        onClick={() => setNewOrder({ ...newOrder, items: [...newOrder.items, { product_id: '', custom_product_name: '', quantity: 1, unit_cost: 0, gst_rate: 18, hsn_code: '' }] })}
                        className="flex items-center text-primary font-semibold text-sm hover:text-primary"
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
                            <th className="p-4 w-24">HSN Code</th>
                            <th className="p-4 w-20 text-center">GST %</th>
                            <th className="p-4 w-32 text-right">Total</th>
                            <th className="p-4 w-12 text-center"></th>
                          </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                          {newOrder.items.map((item, index) => (
                            <tr key={index} className="hover:bg-gray-50/50">
                              <td className="p-4">
                                <ProductSelect
                                  products={products}
                                  value={item.product_id || item.custom_product_name}
                                  onChange={(val) => {
                                    const items = [...newOrder.items];
                                    items[index].product_id = val.product_id || '';
                                    items[index].custom_product_name = val.custom_product_name || '';
                                    if (val.product_id) {
                                      const prod = products.find(p => p.id == val.product_id);
                                      if (prod) {
                                        items[index].unit_cost = parseFloat(prod.purchase_price || 0);
                                        if (prod.hsn_code) items[index].hsn_code = prod.hsn_code;
                                      }
                                      items[index].custom_product_name = '';
                                    }
                                    setNewOrder({ ...newOrder, items });
                                  }}
                                  placeholder="Search product..."
                                />
                              </td>
                              <td className="p-4">
                                <input
                                  type="number"
                                  min="1"
                                  className="w-full text-center bg-transparent border-0 border-b border-gray-200 focus:ring-0 focus:border-ring p-0 pb-1"
                                  value={item.quantity}
                                  onChange={(e) => {
                                    const items = [...newOrder.items];
                                    items[index].quantity = parseInt(e.target.value) || 0;
                                    setNewOrder({ ...newOrder, items });
                                  }}
                                  required
                                />
                              </td>
                              <td className="p-4">
                                <input
                                  type="number"
                                  step="0.01"
                                  min="0"
                                  className="w-full text-right bg-transparent border-0 border-b border-gray-200 focus:ring-0 focus:border-ring p-0 pb-1"
                                  value={item.unit_cost}
                                  onChange={(e) => {
                                    const items = [...newOrder.items];
                                    items[index].unit_cost = parseFloat(e.target.value) || 0;
                                    setNewOrder({ ...newOrder, items });
                                  }}
                                  required
                                />
                              </td>
                              <td className="p-4">
                                <input
                                  type="text"
                                  className="w-full bg-transparent border-0 border-b border-gray-200 focus:ring-0 focus:border-ring text-sm p-0 pb-1"
                                  value={item.hsn_code}
                                  onChange={(e) => {
                                    const items = [...newOrder.items];
                                    items[index].hsn_code = e.target.value;
                                    setNewOrder({ ...newOrder, items });
                                  }}
                                  placeholder="HSN"
                                />
                              </td>
                              <td className="p-4 text-center">
                                <select
                                  className="bg-transparent border-0 border-b border-gray-200 focus:ring-0 focus:border-ring text-sm p-0 pb-1"
                                  value={item.gst_rate}
                                  onChange={(e) => {
                                    const items = [...newOrder.items];
                                    items[index].gst_rate = parseInt(e.target.value) || 0;
                                    setNewOrder({ ...newOrder, items });
                                  }}
                                >
                                  <option value="0">0%</option>
                                  <option value="5">5%</option>
                                  <option value="12">12%</option>
                                  <option value="18">18%</option>
                                  <option value="28">28%</option>
                                </select>
                              </td>
                              <td className="p-4 text-right font-bold text-gray-900">
                                ₹{(item.quantity * item.unit_cost).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                              </td>
                              <td className="p-4 text-center">
                                <button
                                  type="button"
                                  className="text-red-400 hover:text-red-600 transition-colors"
                                  onClick={() => {
                                    const items = newOrder.items.filter((_, i) => i !== index);
                                    setNewOrder({ ...newOrder, items });
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
                  <div className="bg-primary rounded-xl shadow-md p-6 text-white relative overflow-hidden">
                    <div className="absolute right-0 top-0 opacity-10 p-4">
                      <FileText className="w-24 h-24" />
                    </div>
                    <h3 className="font-bold text-lg mb-6 relative z-10">Order Summary</h3>

                    <div className="space-y-4 relative z-10 text-blue-100 text-sm">
                      <div className="flex justify-between">
                        <span>Subtotal</span>
                        <span className="font-medium text-white">
                          ₹{newOrder.items.reduce((sum, item) => sum + (item.quantity * item.unit_cost), 0).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                        </span>
                      </div>
                      <div className="flex justify-between">
                        <span>GST ({newOrder.gst_type === 'igst' ? 'IGST' : 'CGST+SGST'})</span>
                        <span className="font-medium text-white">
                          ₹{newOrder.items.reduce((sum, item) => sum + (item.quantity * item.unit_cost * (item.gst_rate || 0) / 100), 0).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                        </span>
                      </div>
                      <div className="flex justify-between border-b border-blue-500/50 pb-4">
                        <span>Shipping Cost</span>
                        <span className="font-medium text-white">₹{(newOrder.shipping_cost || 0).toLocaleString(undefined, { minimumFractionDigits: 2 })}</span>
                      </div>
                      <div className="flex justify-between items-center pt-2">
                        <span className="text-base text-white">Total Amount</span>
                        <span className="text-2xl font-bold text-white">
                          ₹{(newOrder.items.reduce((sum, item) => sum + (item.quantity * item.unit_cost * (1 + (item.gst_rate || 0) / 100)), 0) + (newOrder.shipping_cost || 0)).toLocaleString(undefined, { minimumFractionDigits: 2 })}
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
                        className="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm focus:ring-ring focus:border-ring"
                        rows="3"
                        placeholder="Add any internal procurement notes..."
                      ></textarea>
                    </div>

                    <div>
                      <h3 className="flex items-center text-sm font-bold text-gray-800 mb-3">
                        <FileText className="w-4 h-4 text-gray-400 mr-2" /> Terms & Conditions
                      </h3>
                      <select className="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm focus:ring-ring focus:border-ring">
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
                      onClick={(e) => handleCreatePO(e, 'Draft')}
                      className="w-full px-4 py-3 bg-white border-2 border-gray-200 text-primary font-bold rounded-lg hover:bg-gray-50 transition-colors"
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
