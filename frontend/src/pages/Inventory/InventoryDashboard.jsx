import { useState, useEffect } from 'react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Legend } from 'recharts';
import { 
  Banknote, AlertTriangle, ArrowDownLeft, ArrowUpRight, 
  Package, Truck, Sliders, Bell, Download, Image as ImageIcon, Plus, X, Search
} from 'lucide-react';
import api from '../../services/api';

export default function InventoryDashboard() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [searchQuery, setSearchQuery] = useState('');

  // For "New Adjustment"
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [products, setProducts] = useState([]);
  const [branches, setBranches] = useState([]);
  const [formData, setFormData] = useState({
    product_id: '',
    branch_id: '',
    type: 'purchase',
    quantity: '',
    notes: ''
  });

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    setLoading(true);
    try {
      const [invRes, transRes, prodRes, branchRes] = await Promise.all([
        api.get('/inventory'),
        api.get('/inventory/transactions'),
        api.get('/products'),
        api.get('/branches')
      ]);

      const inventories = invRes.data;
      const transactions = transRes.data;
      setProducts(prodRes.data);
      setBranches(branchRes.data);

      // Process inventory data for summary
      let totalValue = 0;
      let lowStockCount = 0;
      const lowStockItems = [];

      inventories.forEach(inv => {
        const itemVal = (inv.product?.selling_price || 0) * inv.quantity;
        totalValue += itemVal;
        
        if (inv.quantity < 10) { // Assuming 10 is low stock threshold
          lowStockCount++;
          lowStockItems.push({
            id: inv.id,
            name: inv.product?.name || 'Unknown',
            sku: inv.product?.sku || 'N/A',
            stock: inv.quantity,
            reorder: 50 // mockup reorder point
          });
        }
      });

      // Process transactions for today's incoming/outgoing
      let incomingUnits = 0;
      let outgoingUnits = 0;
      
      const today = new Date().toISOString().split('T')[0];
      
      transactions.forEach(t => {
        const tDate = new Date(t.created_at).toISOString().split('T')[0];
        if (tDate === today) {
           if (['purchase', 'return', 'transfer_in'].includes(t.type)) {
             incomingUnits += t.quantity;
           } else if (['sale', 'damage', 'transfer_out'].includes(t.type)) {
             outgoingUnits += Math.abs(t.quantity);
           }
        }
      });

      // Warehouse operations (recent transactions)
      const warehouseOps = transactions.slice(0, 5).map(t => ({
        id: t.id,
        type: ['sale', 'damage', 'transfer_out'].includes(t.type) ? 'dispatch' : 
              ['adjustment'].includes(t.type) ? 'adjustment' : 'inbound',
        title: `${t.type.toUpperCase()}: ${t.inventory?.product?.name || 'Item'}`,
        desc: `${t.quantity} units • ${t.inventory?.branch?.name || 'Unknown Branch'}`,
        icon: ['sale', 'damage', 'transfer_out'].includes(t.type) ? Truck : 
              ['adjustment'].includes(t.type) ? Sliders : Package
      }));

      // Stock Movement timeline (mocking Mon-Sun based on recent txns for simplicity)
      const stockMovement = [
        { name: 'Mon', incoming: 400, outgoing: 240 },
        { name: 'Tue', incoming: 500, outgoing: 398 },
        { name: 'Wed', incoming: 300, outgoing: 450 },
        { name: 'Thu', incoming: 600, outgoing: 480 },
        { name: 'Fri', incoming: 200, outgoing: 380 },
        { name: 'Sat', incoming: 100, outgoing: 150 },
        { name: 'Sun', incoming: Math.max(incomingUnits, 50),  outgoing: Math.max(outgoingUnits, 100) },
      ];

      setData({
        summary: {
          totalValue: totalValue || 0,
          valueGrowth: 4.2, // mock
          lowStockItems: lowStockCount,
          incomingUnits,
          incomingPos: transactions.filter(t => ['purchase', 'return', 'transfer_in'].includes(t.type)).length,
          outgoingUnits,
          outgoingOrders: transactions.filter(t => ['sale', 'damage', 'transfer_out'].includes(t.type)).length
        },
        stockMovement,
        warehouseOps,
        lowStock: lowStockItems
      });

    } catch (error) {
      console.error("Error fetching inventory data:", error);
    } finally {
      setLoading(false);
    }
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const openAdjustmentModal = () => {
    setFormData({ product_id: products[0]?.id || '', branch_id: branches[0]?.id || '', type: 'purchase', quantity: '', notes: '' });
    setIsModalOpen(true);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      await api.post('/inventory/transaction', formData);
      setIsModalOpen(false);
      fetchData();
    } catch (error) {
      console.error("Error recording transaction:", error);
      alert(error.response?.data?.message || "Failed to record transaction.");
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center h-full">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-[#1a56db]"></div>
      </div>
    );
  }

  if (!data) return null;

  return (
    <div className="space-y-6 pb-12">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Inventory Dashboard</h1>
          <p className="text-sm text-gray-500">Real-time stock analytics and replenishment tracking.</p>
        </div>
        <div className="flex space-x-3">
          <button className="flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
            <Download className="w-4 h-4 mr-2" />
            Export Report
          </button>
          <button onClick={openAdjustmentModal} className="flex items-center px-4 py-2 bg-[#1a56db] text-white rounded-md text-sm font-medium hover:bg-blue-700">
            <Plus className="w-4 h-4 mr-2" />
            New Adjustment
          </button>
        </div>
      </div>

      {/* KPI Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <div className="flex justify-between items-start mb-2">
            <p className="text-sm font-semibold text-gray-500">Total Stock Value</p>
            <Banknote className="w-5 h-5 text-[#1a56db]" />
          </div>
          <h3 className="text-2xl font-bold text-gray-900 mb-2">₹{numberWithCommas(data.summary.totalValue.toFixed(2))}</h3>
          <div className="flex items-center text-xs">
            <span className="text-green-600 font-medium flex items-center mr-1">
               <ArrowUpRight className="w-3 h-3 mr-0.5" /> +{data.summary.valueGrowth}%
            </span>
            <span className="text-gray-500">vs last month</span>
          </div>
        </div>

        <div className="bg-red-50 rounded-xl shadow-sm border border-red-200 p-6 relative overflow-hidden">
          <div className="absolute right-[-10px] bottom-[-20px] opacity-10 text-red-500">
            <AlertTriangle className="w-24 h-24" />
          </div>
          <div className="flex justify-between items-start mb-2 relative z-10">
            <p className="text-sm font-bold text-red-800">Low Stock Items</p>
            <AlertTriangle className="w-5 h-5 text-red-600" />
          </div>
          <h3 className="text-2xl font-bold text-red-700 mb-2">{data.summary.lowStockItems} Items</h3>
          {data.summary.lowStockItems > 0 && (
            <div className="inline-block px-2 py-1 bg-red-600 text-white text-[10px] font-bold uppercase tracking-wider rounded">
              Action Required
            </div>
          )}
        </div>

        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <div className="flex justify-between items-start mb-2">
            <p className="text-sm font-semibold text-gray-500">Incoming Today</p>
            <ArrowDownLeft className="w-5 h-5 text-gray-400" />
          </div>
          <h3 className="text-2xl font-bold text-gray-900 mb-2">{data.summary.incomingUnits} Units</h3>
          <p className="text-xs text-gray-500">across {data.summary.incomingPos} Operations</p>
        </div>

        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <div className="flex justify-between items-start mb-2">
            <p className="text-sm font-semibold text-gray-500">Outgoing Today</p>
            <ArrowUpRight className="w-5 h-5 text-gray-400" />
          </div>
          <h3 className="text-2xl font-bold text-gray-900 mb-2">{data.summary.outgoingUnits} Units</h3>
          <p className="text-xs text-gray-500">across {data.summary.outgoingOrders} Operations</p>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Stock Movement Timeline */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 lg:col-span-2 flex flex-col">
          <div className="flex justify-between items-center mb-6">
            <h2 className="text-lg font-bold text-gray-900">Stock Movement Timeline</h2>
          </div>
          <div className="flex-1 h-72">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={data.stockMovement} margin={{ top: 10, right: 10, left: -20, bottom: 0 }} barGap={2} barSize={16}>
                <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#f3f4f6" />
                <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fontSize: 11, fill: '#6b7280' }} dy={10} />
                <YAxis hide />
                <Tooltip cursor={{fill: '#f9fafb'}} contentStyle={{ borderRadius: '8px', border: 'none', boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)' }} />
                <Legend iconType="circle" iconSize={8} wrapperStyle={{ fontSize: '12px', color: '#6b7280', paddingTop: '20px' }} />
                <Bar dataKey="incoming" name="Incoming Stock" fill="#1a56db" radius={[4, 4, 0, 0]} />
                <Bar dataKey="outgoing" name="Outgoing Stock" fill="#4b5563" radius={[4, 4, 0, 0]} />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>

        {/* Warehouse Operations */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col">
          <h2 className="text-lg font-bold text-gray-900 mb-6">Recent Transactions</h2>
          <div className="flex-1 overflow-y-auto pr-2">
            <ul className="space-y-6">
              {data.warehouseOps.map((op, idx) => {
                const Icon = op.icon;
                const isAlert = op.type === 'alert';
                const isAdjustment = op.type === 'adjustment';
                
                return (
                  <li key={idx} className="flex relative">
                    {idx !== data.warehouseOps.length - 1 && (
                      <div className="absolute left-4 top-8 bottom-[-24px] w-px bg-gray-200"></div>
                    )}
                    <div className={`relative z-10 w-8 h-8 rounded-full flex items-center justify-center shrink-0 mr-4 ${
                      isAlert ? 'bg-red-50 text-red-500 border border-red-100' :
                      isAdjustment ? 'bg-orange-50 text-orange-500 border border-orange-100' :
                      'bg-blue-50 text-[#1a56db] border border-blue-100'
                    }`}>
                      <Icon className="w-4 h-4" />
                    </div>
                    <div>
                      <p className="text-sm font-bold text-gray-900">{op.title}</p>
                      <p className="text-xs text-gray-500 mt-1">{op.desc}</p>
                    </div>
                  </li>
                );
              })}
              {data.warehouseOps.length === 0 && (
                <li className="text-sm text-gray-500">No recent transactions.</li>
              )}
            </ul>
          </div>
        </div>
      </div>

      {/* Low Stock Alerts Table */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div className="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-4 sm:space-y-0">
          <h2 className="text-lg font-bold text-gray-900">Low Stock Alerts</h2>
          <div className="flex space-x-3 items-center">
            <div className="relative">
              <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <Search className="h-4 w-4 text-gray-400" />
              </div>
              <input
                type="text"
                placeholder="Search items or SKUs..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#1a56db] focus:border-[#1a56db] sm:text-sm"
              />
            </div>
            {data.lowStock.length > 0 && (
              <button className="px-4 py-2 bg-[#1a56db] text-white text-sm font-medium rounded-md hover:bg-blue-700 whitespace-nowrap">
                Restock Selected
              </button>
            )}
          </div>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full text-left text-sm text-gray-600">
            <thead className="text-xs text-gray-500 uppercase bg-gray-50 font-semibold tracking-wider">
              <tr>
                <th className="px-6 py-4">Item Name</th>
                <th className="px-6 py-4">SKU</th>
                <th className="px-6 py-4">Current Stock</th>
                <th className="px-6 py-4">Reorder Point</th>
                <th className="px-6 py-4 text-right">Action</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {data.lowStock
                .filter(item => 
                  item.name.toLowerCase().includes(searchQuery.toLowerCase()) || 
                  item.sku.toLowerCase().includes(searchQuery.toLowerCase())
                )
                .map((item) => (
                <tr key={item.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 flex items-center">
                    <div className="w-10 h-10 rounded-md bg-gray-100 border border-gray-200 flex items-center justify-center mr-3 text-gray-400">
                      <ImageIcon className="w-5 h-5" />
                    </div>
                    <span className="font-bold text-gray-900">{item.name}</span>
                  </td>
                  <td className="px-6 py-4">{item.sku}</td>
                  <td className="px-6 py-4 font-bold text-red-600">{item.stock} units</td>
                  <td className="px-6 py-4">{item.reorder} units</td>
                  <td className="px-6 py-4 text-right">
                    <button className="text-[#1a56db] font-bold hover:underline">Order Now</button>
                  </td>
                </tr>
              ))}
              {data.lowStock.length === 0 && (
                <tr>
                  <td colSpan="5" className="px-6 py-8 text-center text-gray-500">
                    No low stock items. All good!
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Modal for New Transaction/Adjustment */}
      {isModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
          <div className="bg-white rounded-xl shadow-lg w-full max-w-md overflow-hidden">
            <div className="flex justify-between items-center px-6 py-4 border-b border-gray-100">
              <h2 className="text-xl font-bold text-gray-900">New Inventory Adjustment</h2>
              <button onClick={() => setIsModalOpen(false)} className="text-gray-400 hover:text-gray-600">
                <X className="w-6 h-6" />
              </button>
            </div>
            <form onSubmit={handleSubmit} className="p-6 space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Product</label>
                <select required name="product_id" value={formData.product_id} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1a56db] focus:border-[#1a56db]">
                  <option value="" disabled>Select Product</option>
                  {products.map(p => (
                    <option key={p.id} value={p.id}>{p.name}</option>
                  ))}
                </select>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Warehouse/Branch</label>
                <select required name="branch_id" value={formData.branch_id} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1a56db] focus:border-[#1a56db]">
                  <option value="" disabled>Select Branch</option>
                  {branches.map(b => (
                    <option key={b.id} value={b.id}>{b.name}</option>
                  ))}
                </select>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Transaction Type</label>
                <select required name="type" value={formData.type} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1a56db] focus:border-[#1a56db]">
                  <option value="purchase">Purchase (Add Stock)</option>
                  <option value="sale">Sale (Remove Stock)</option>
                  <option value="transfer_in">Transfer In</option>
                  <option value="transfer_out">Transfer Out</option>
                  <option value="damage">Damage/Loss</option>
                  <option value="adjustment">Manual Adjustment</option>
                </select>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                <input required type="number" name="quantity" min="1" value={formData.quantity} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1a56db] focus:border-[#1a56db]" />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" value={formData.notes} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1a56db] focus:border-[#1a56db]" rows="2"></textarea>
              </div>
              
              <div className="flex justify-end space-x-3 pt-4 border-t border-gray-100 mt-6">
                <button type="button" onClick={() => setIsModalOpen(false)} className="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                  Cancel
                </button>
                <button type="submit" className="px-4 py-2 bg-[#1a56db] text-white rounded-md text-sm font-medium hover:bg-blue-700">
                  Record Transaction
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

function numberWithCommas(x) {
  return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
