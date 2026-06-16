import { useState, useEffect } from 'react';
import {
  Plus, Filter, MoreHorizontal, Image as ImageIcon, AlertTriangle, XCircle, X, Search
} from 'lucide-react';
import api from '../../services/api';

export default function ProductCatalog() {
  const [data, setData] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [activeFilter, setActiveFilter] = useState('All Products');
  const [searchQuery, setSearchQuery] = useState('');
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [currentProduct, setCurrentProduct] = useState(null);

  const [formData, setFormData] = useState({
    name: '',
    sku: '',
    category_id: '',
    purchase_price: '',
    selling_price: '',
    gst_percentage: '',
    status: 'In Stock'
  });

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    setLoading(true);
    try {
      const [prodRes, catRes] = await Promise.all([
        api.get('/products'),
        api.get('/categories')
      ]);
      setData(prodRes.data);
      setCategories(catRes.data);
    } catch (error) {
      console.error("Error fetching data:", error);
    } finally {
      setLoading(false);
    }
  };

  const handleFilterClick = (filter) => {
    setActiveFilter(filter);
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const openAddModal = () => {
    setCurrentProduct(null);
    setFormData({
      name: '', sku: '', category_id: categories[0]?.id || '',
      purchase_price: '', selling_price: '', gst_percentage: '', status: 'In Stock'
    });
    setIsModalOpen(true);
  };

  const openEditModal = (product) => {
    setCurrentProduct(product);
    setFormData({
      name: product.name || '',
      sku: product.sku || '',
      category_id: product.category_id || categories[0]?.id || '',
      purchase_price: product.purchase_price || '',
      selling_price: product.selling_price || '',
      gst_percentage: product.gst_percentage || '',
      status: product.status || 'In Stock'
    });
    setIsModalOpen(true);
  };

  const closeModal = () => setIsModalOpen(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      if (currentProduct) {
        await api.put(`/products/${currentProduct.id}`, formData);
      } else {
        await api.post('/products', formData);
      }
      closeModal();
      fetchData();
    } catch (error) {
      console.error("Error saving product:", error);
      alert("Failed to save product.");
    }
  };

  const handleDelete = async (id) => {
    if (window.confirm("Are you sure you want to delete this product?")) {
      try {
        await api.delete(`/products/${id}`);
        fetchData();
      } catch (error) {
        console.error("Error deleting product:", error);
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

  const filters = ['All Products', ...categories.map(c => c.name)];
  const filteredData = data.filter(p => {
    const matchesFilter = activeFilter === 'All Products' || p.category?.name === activeFilter;
    const matchesSearch = p.name?.toLowerCase().includes(searchQuery.toLowerCase()) ||
      p.sku?.toLowerCase().includes(searchQuery.toLowerCase());
    return matchesFilter && matchesSearch;
  });
  console.log(filteredData);


  return (
    <div className="space-y-6 pb-12">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-4 sm:space-y-0">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Product Catalog</h1>
          <p className="text-sm text-gray-500">Manage and monitor your enterprise inventory and stock levels in real-time.</p>
        </div>
        <div className="flex space-x-3 items-center">
          <div className="relative">
            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <Search className="h-4 w-4 text-gray-400" />
            </div>
            <input
              type="text"
              placeholder="Search products or SKUs..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#1a56db] focus:border-[#1a56db] sm:text-sm"
            />
          </div>
          <button onClick={openAddModal} className="flex items-center px-4 py-2 bg-[#1a56db] text-white rounded-md text-sm font-medium hover:bg-blue-700 whitespace-nowrap">
            <Plus className="w-4 h-4 mr-2" />
            Add Product
          </button>
        </div>
      </div>

      {/* Filters */}
      <div className="flex items-center space-x-2 overflow-x-auto pb-2">
        {filters.map(filter => (
          <button
            key={filter}
            onClick={() => handleFilterClick(filter)}
            className={`whitespace-nowrap px-4 py-2 rounded-full text-sm font-medium transition-colors ${activeFilter === filter
                ? 'bg-[#1a56db] text-white'
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
              }`}
          >
            {filter}
          </button>
        ))}
        <div className="pl-4 border-l border-gray-300 ml-2">
          <button className="flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
            <Filter className="w-4 h-4 mr-2" />
            More Filters
          </button>
        </div>
      </div>

      {/* Product Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        {filteredData.map(product => {
          const stock = parseInt(product.inventories_sum_quantity) || 0;
          const status = stock === 0 ? 'Out of Stock' : (stock < 10 ? 'Low Stock' : 'In Stock');

          return (
            <div key={product.id} className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col hover:shadow-md transition-shadow relative">
              <div className="h-48 relative bg-gray-100 flex items-center justify-center group">
                {product.image ? (
                  <img src={product.image} alt={product.name} className="w-full h-full object-cover" />
                ) : (
                  <ImageIcon className="w-12 h-12 text-gray-300" />
                )}

                <div className="absolute top-3 right-3">
                  <span className={`px-2 py-1 rounded text-xs font-bold shadow-sm ${status === 'In Stock' ? 'bg-white text-gray-700' :
                      status === 'Low Stock' ? 'bg-orange-100 text-orange-700' :
                        'bg-red-100 text-red-700'
                    }`}>
                    {status}
                  </span>
                </div>

                {/* Hover actions */}
                <div className="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity space-x-2">
                  <button onClick={() => openEditModal(product)} className="px-3 py-1.5 bg-white rounded text-sm font-medium text-gray-900 hover:bg-gray-100">Edit</button>
                  <button onClick={() => handleDelete(product.id)} className="px-3 py-1.5 bg-red-600 rounded text-sm font-medium text-white hover:bg-red-700">Delete</button>
                </div>
              </div>

              <div className="p-5 flex-1 flex flex-col">
                <div className="flex justify-between items-start mb-1">
                  <h3 className="font-bold text-gray-900 text-lg truncate max-w-[70%]">{product.name}</h3>
                  <span className="font-bold text-[#1a56db]">₹{Number(product.selling_price).toFixed(2)}</span>
                </div>
                <p className="text-xs text-gray-500 mb-4">SKU: {product.sku}</p>

                <div className="flex justify-between items-center mt-auto">
                  <span className="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs font-medium truncate max-w-[50%]">
                    {product.category?.name || 'Uncategorized'}
                  </span>
                  <span className={`flex items-center text-sm font-medium ${stock === 0 ? 'text-red-500' :
                      stock < 10 ? 'text-orange-500' :
                        'text-gray-600'
                    }`}>
                    {stock === 0 ? <XCircle className="w-4 h-4 mr-1" /> :
                      stock < 10 ? <AlertTriangle className="w-4 h-4 mr-1" /> :
                        <ImageIcon className="w-4 h-4 mr-1 text-gray-400" />}
                    {stock} units
                  </span>
                </div>
              </div>
            </div>
          )
        })}

        {/* Add New Product Card */}
        <div onClick={openAddModal} className="bg-white rounded-xl shadow-sm border-2 border-dashed border-gray-300 overflow-hidden flex flex-col items-center justify-center p-8 hover:bg-gray-50 hover:border-gray-400 transition-colors cursor-pointer min-h-[350px]">
          <div className="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-4 text-gray-400">
            <Plus className="w-6 h-6" />
          </div>
          <h3 className="font-bold text-gray-900 text-lg mb-2">Add New Product</h3>
          <p className="text-sm text-gray-500 text-center">Instantly expand your catalog with new enterprise assets.</p>
        </div>
      </div>

      {/* Modal */}
      {isModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
          <div className="bg-white rounded-xl shadow-lg w-full max-w-2xl overflow-hidden">
            <div className="flex justify-between items-center px-6 py-4 border-b border-gray-100">
              <h2 className="text-xl font-bold text-gray-900">
                {currentProduct ? 'Edit Product' : 'Add Product'}
              </h2>
              <button onClick={closeModal} className="text-gray-400 hover:text-gray-600">
                <X className="w-6 h-6" />
              </button>
            </div>
            <form onSubmit={handleSubmit} className="p-6 space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div className="col-span-2 sm:col-span-1">
                  <label className="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                  <input required type="text" name="name" value={formData.name} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1a56db] focus:border-[#1a56db]" />
                </div>
                <div className="col-span-2 sm:col-span-1">
                  <label className="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                  <input required type="text" name="sku" value={formData.sku} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1a56db] focus:border-[#1a56db]" />
                </div>
                <div className="col-span-2 sm:col-span-1">
                  <label className="block text-sm font-medium text-gray-700 mb-1">Category</label>
                  <select name="category_id" value={formData.category_id} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1a56db] focus:border-[#1a56db]">
                    <option value="">Select Category</option>
                    {categories.map(c => (
                      <option key={c.id} value={c.id}>{c.name}</option>
                    ))}
                  </select>
                </div>
                <div className="col-span-2 sm:col-span-1">
                  <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
                  <select name="status" value={formData.status} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1a56db] focus:border-[#1a56db]">
                    <option value="In Stock">In Stock</option>
                    <option value="Low Stock">Low Stock</option>
                    <option value="Out of Stock">Out of Stock</option>
                  </select>
                </div>
                <div className="col-span-2 sm:col-span-1">
                  <label className="block text-sm font-medium text-gray-700 mb-1">Purchase Price (₹)</label>
                  <input required type="number" step="0.01" name="purchase_price" value={formData.purchase_price} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1a56db] focus:border-[#1a56db]" />
                </div>
                <div className="col-span-2 sm:col-span-1">
                  <label className="block text-sm font-medium text-gray-700 mb-1">Selling Price (₹)</label>
                  <input required type="number" step="0.01" name="selling_price" value={formData.selling_price} onChange={handleInputChange} className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#1a56db] focus:border-[#1a56db]" />
                </div>
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
