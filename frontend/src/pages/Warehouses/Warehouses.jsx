import { useState, useEffect } from 'react';
import { 
  Plus, Grid, List, Map as MapIcon, Globe, MapPin, Search, TrendingUp, X, Edit2, Trash2
} from 'lucide-react';
import api from '../../services/api';

export default function Warehouses() {
  const [data, setData] = useState([]);
  const [inventoryData, setInventoryData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [viewMode, setViewMode] = useState('grid');
  const [searchQuery, setSearchQuery] = useState('');
  
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [currentBranch, setCurrentBranch] = useState(null);
  const [formData, setFormData] = useState({ name: '', location: '' });

  // Dashboard specific stats
  const [summaryData, setSummaryData] = useState({
    totalValue: 0,
    valueGrowth: 0,
    utilization: { dry: 0, cold: 0 }
  });

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    setLoading(true);
    try {
      const [branchRes, invRes] = await Promise.all([
        api.get('/branches'),
        api.get('/inventory')
      ]);
      setData(branchRes.data);
      setInventoryData(invRes.data);

      // Aggregate total value
      let totalValue = 0;
      invRes.data.forEach(inv => {
        totalValue += (inv.product?.selling_price || 0) * inv.quantity;
      });

      setSummaryData({
        totalValue: totalValue,
        valueGrowth: 4.2, // Still mocked as we don't have historical data
        utilization: { dry: 0, cold: 0 } // Capacity is not in the schema
      });
    } catch (error) {
      console.error("Error fetching branches:", error);
    } finally {
      setLoading(false);
    }
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const openAddModal = () => {
    setCurrentBranch(null);
    setFormData({ name: '', location: '' });
    setIsModalOpen(true);
  };

  const openEditModal = (branch) => {
    setCurrentBranch(branch);
    setFormData({
      name: branch.name || '',
      location: branch.location || ''
    });
    setIsModalOpen(true);
  };

  const closeModal = () => setIsModalOpen(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      if (currentBranch) {
        await api.put(`/branches/${currentBranch.id}`, formData);
      } else {
        await api.post('/branches', formData);
      }
      closeModal();
      fetchData();
    } catch (error) {
      console.error("Error saving branch:", error);
      alert("Failed to save warehouse.");
    }
  };

  const handleDelete = async (id) => {
    if (window.confirm("Are you sure you want to delete this warehouse?")) {
      try {
        await api.delete(`/branches/${id}`);
        fetchData();
      } catch (error) {
        console.error("Error deleting branch:", error);
      }
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center h-full">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-[#1a56db]"></div>
      </div>
    );
  }

  return (
    <div className="space-y-6 pb-12">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-4 sm:space-y-0">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Warehouses</h1>
          <p className="text-sm text-gray-500">Managing {data.length} regional distribution centers and hubs.</p>
        </div>
        <div className="flex space-x-3 items-center">
          <div className="relative">
            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <Search className="h-4 w-4 text-gray-400" />
            </div>
            <input
              type="text"
              placeholder="Search warehouses..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#1a56db] focus:border-[#1a56db] sm:text-sm"
            />
          </div>
          <div className="hidden md:flex bg-gray-100 rounded-lg p-1 border border-gray-200">
            <button 
              onClick={() => setViewMode('grid')}
              className={`px-3 py-1.5 flex items-center text-sm font-medium rounded-md ${viewMode === 'grid' ? 'bg-white shadow-sm text-[#1a56db]' : 'text-gray-500 hover:text-gray-900'}`}
            >
              <Grid className="w-4 h-4 mr-1.5" /> Grid
            </button>
            <button 
              onClick={() => setViewMode('list')}
              className={`px-3 py-1.5 flex items-center text-sm font-medium rounded-md ${viewMode === 'list' ? 'bg-white shadow-sm text-[#1a56db]' : 'text-gray-500 hover:text-gray-900'}`}
            >
              <List className="w-4 h-4 mr-1.5" /> List
            </button>
            <button 
              onClick={() => setViewMode('map')}
              className={`px-3 py-1.5 flex items-center text-sm font-medium rounded-md ${viewMode === 'map' ? 'bg-white shadow-sm text-[#1a56db]' : 'text-gray-500 hover:text-gray-900'}`}
            >
              <MapIcon className="w-4 h-4 mr-1.5" /> Map
            </button>
          </div>
          <button onClick={openAddModal} className="flex items-center px-4 py-2 bg-[#1a56db] text-white rounded-md text-sm font-medium hover:bg-blue-700 h-[36px]">
            <Plus className="w-4 h-4 mr-2" />
            Add Warehouse
          </button>
        </div>
      </div>

      {/* Top Section */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Map Visualization Placeholder */}
        <div className="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden relative min-h-[300px] flex items-center justify-center bg-gray-50">
          <div className="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
          
          <div className="absolute top-4 left-4 bg-white/90 backdrop-blur-sm p-3 rounded-lg shadow-sm border border-gray-200 z-10">
            <h3 className="text-sm font-bold text-gray-900 flex items-center">
              <Globe className="w-4 h-4 mr-2 text-[#1a56db]" /> Geographic Distribution
            </h3>
            <p className="text-xs text-gray-500 mt-1">Real-time status across all nodes</p>
          </div>
          
          <div className="absolute bottom-4 right-4 bg-white/90 backdrop-blur-sm p-3 rounded-lg shadow-sm border border-gray-200 z-10 flex flex-col space-y-2">
            <div className="flex items-center text-xs font-medium text-gray-700">
              <span className="w-3 h-3 rounded-full bg-[#1a56db] mr-2"></span> Operational
            </div>
          </div>
          
          <div className="absolute top-[30%] left-[40%] w-4 h-4 rounded-full bg-[#1a56db] border-2 border-white shadow-md animate-pulse"></div>
          <div className="absolute top-[60%] left-[70%] w-4 h-4 rounded-full bg-[#1a56db] border-2 border-white shadow-md"></div>
          <div className="absolute top-[40%] left-[20%] w-3 h-3 rounded-full bg-[#1a56db] border-2 border-white shadow-md opacity-70"></div>
        </div>

        {/* Right KPIs */}
        <div className="flex flex-col space-y-6">
          <div className="bg-[#1a56db] rounded-xl shadow-sm p-6 text-white relative overflow-hidden flex-1 flex flex-col justify-center">
            <div className="absolute right-[-20px] bottom-[-20px] opacity-10">
              <span className="text-[120px] font-bold leading-none">₹</span>
            </div>
            <p className="text-xs font-semibold text-blue-200 uppercase tracking-wider mb-2 relative z-10">Total Inventory Value</p>
            <h3 className="text-4xl font-bold mb-4 relative z-10">₹{(summaryData.totalValue / 1000000).toFixed(1)}M</h3>
            <div className="flex items-center text-sm font-medium text-blue-100 relative z-10">
              <TrendingUp className="w-4 h-4 mr-2" />
              +{summaryData.valueGrowth}% from last month
            </div>
          </div>

          <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex-1 flex flex-col justify-center">
            <h3 className="text-sm font-bold text-gray-900 mb-4">Utilization Health</h3>
            
            <div className="mb-4">
              <div className="flex justify-between text-xs font-bold mb-1">
                <span className="text-gray-700 uppercase tracking-wider">Dry Storage</span>
                <span className="text-gray-900">{summaryData.utilization.dry}%</span>
              </div>
              <div className="w-full bg-gray-200 rounded-full h-2">
                <div className="bg-[#1a56db] h-2 rounded-full" style={{ width: `${summaryData.utilization.dry}%` }}></div>
              </div>
            </div>
            
            <div>
              <div className="flex justify-between text-xs font-bold mb-1">
                <span className="text-gray-700 uppercase tracking-wider">Cold Chain</span>
                <span className="text-gray-900">{summaryData.utilization.cold}%</span>
              </div>
              <div className="w-full bg-gray-200 rounded-full h-2">
                <div className="bg-[#1a56db] h-2 rounded-full" style={{ width: `${summaryData.utilization.cold}%` }}></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Warehouses Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {data
          .filter(branch => 
            branch.name?.toLowerCase().includes(searchQuery.toLowerCase()) || 
            branch.location?.toLowerCase().includes(searchQuery.toLowerCase())
          )
          .map((branch, index) => {
          
          // Calculate exact stored items from the real inventory data
          const branchInventory = inventoryData.filter(inv => inv.branch_id === branch.id);
          let storedItems = 0;
          branchInventory.forEach(inv => storedItems += inv.quantity);
          
          const openOrders = 0; // Not currently tracked in branches/inventory
          const capacity = 0; // Not available in DB schema
          
          return (
          <div key={branch.id} className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col hover:shadow-md transition-shadow relative group">
            
            <div className="absolute top-4 right-4 flex space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
              <button onClick={() => openEditModal(branch)} className="p-1.5 bg-gray-100 text-gray-600 rounded-md hover:bg-gray-200">
                <Edit2 className="w-4 h-4" />
              </button>
              <button onClick={() => handleDelete(branch.id)} className="p-1.5 bg-red-100 text-red-600 rounded-md hover:bg-red-200">
                <Trash2 className="w-4 h-4" />
              </button>
            </div>

            <div className="flex justify-between items-start mb-4 pr-16">
              <div>
                <h3 className="text-lg font-bold text-gray-900">{branch.name}</h3>
                <p className="text-sm text-gray-500 flex items-center mt-1">
                  <MapPin className="w-3.5 h-3.5 mr-1" /> {branch.location || 'Location Not Set'}
                </p>
              </div>
            </div>

            <div className="flex space-x-4 mb-6">
              <div className="flex-1 bg-gray-50 p-3 rounded-lg border border-gray-100">
                <p className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Stored Items</p>
                <h4 className="text-xl font-bold text-[#1a56db]">{storedItems}</h4>
              </div>
              <div className="flex-1 bg-gray-50 p-3 rounded-lg border border-gray-100">
                <p className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Open Orders</p>
                <h4 className="text-xl font-bold text-gray-900">{openOrders}</h4>
              </div>
            </div>

            <div className="mb-6">
              <div className="flex justify-between text-xs font-bold mb-1">
                <span className="text-gray-700">Capacity Usage</span>
                <span className="text-gray-500 font-normal">N/A (Not tracked)</span>
              </div>
              <div className="w-full bg-gray-100 rounded-full h-1.5">
                <div 
                  className="h-1.5 rounded-full bg-gray-300"
                  style={{ width: `0%` }}
                ></div>
              </div>
            </div>

          </div>
        )})}
        
        {data.length === 0 && (
          <div className="col-span-full py-12 text-center text-gray-500 bg-white rounded-xl border border-dashed border-gray-300">
            No warehouses found. Click "Add Warehouse" to create one.
          </div>
        )}
      </div>

      {/* Modal */}
      {isModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
          <div className="bg-white rounded-xl shadow-lg w-full max-w-md overflow-hidden">
            <div className="flex justify-between items-center px-6 py-4 border-b border-gray-100">
              <h2 className="text-xl font-bold text-gray-900">
                {currentBranch ? 'Edit Warehouse' : 'Add Warehouse'}
              </h2>
              <button onClick={closeModal} className="text-gray-400 hover:text-gray-600">
                <X className="w-6 h-6" />
              </button>
            </div>
            <form onSubmit={handleSubmit} className="p-6 space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Warehouse Name</label>
                <input required type="text" name="name" value={formData.name} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1a56db] focus:border-[#1a56db]" placeholder="e.g. North Distribution Center" />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Location</label>
                <input type="text" name="location" value={formData.location} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1a56db] focus:border-[#1a56db]" placeholder="e.g. Chicago, IL" />
              </div>
              
              <div className="flex justify-end space-x-3 pt-4 border-t border-gray-100 mt-6">
                <button type="button" onClick={closeModal} className="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                  Cancel
                </button>
                <button type="submit" className="px-4 py-2 bg-[#1a56db] text-white rounded-md text-sm font-medium hover:bg-blue-700">
                  Save
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
