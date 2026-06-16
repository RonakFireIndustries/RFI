import { useState, useEffect } from 'react';
import { Database, LogOut, Loader2, Table as TableIcon, Edit2, Trash2, Plus, X } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import { useAuthStore } from '../../store/authStore';
import api from '../../services/api';

export default function SuperAdminDashboard() {
  const [tables, setTables] = useState([]);
  const [activeTable, setActiveTable] = useState(null);
  const [schema, setSchema] = useState([]);
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  
  // Modal state
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [modalMode, setModalMode] = useState('add'); // 'add' or 'edit'
  const [formData, setFormData] = useState({});
  const [saving, setSaving] = useState(false);

  const logout = useAuthStore((state) => state.logout);
  const navigate = useNavigate();

  useEffect(() => {
    fetchTables();
  }, []);

  useEffect(() => {
    if (activeTable) {
      fetchTableData(activeTable);
    }
  }, [activeTable]);

  const fetchTables = async () => {
    try {
      const res = await api.get('/admin/tables');
      setTables(res.data);
      if (res.data.length > 0) {
        setActiveTable(res.data[0]);
      }
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const fetchTableData = async (tableName) => {
    setLoading(true);
    try {
      const [schemaRes, dataRes] = await Promise.all([
        api.get(`/admin/tables/${tableName}/schema`),
        api.get(`/admin/tables/${tableName}`)
      ]);
      setSchema(schemaRes.data);
      setData(dataRes.data);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleLogout = () => {
    logout();
    navigate('/superadmin');
  };

  const openAddModal = () => {
    setModalMode('add');
    const emptyForm = {};
    schema.forEach(col => {
      if (col.name !== 'id' && col.name !== 'created_at' && col.name !== 'updated_at') {
        emptyForm[col.name] = '';
      }
    });
    setFormData(emptyForm);
    setIsModalOpen(true);
  };

  const openEditModal = (row) => {
    setModalMode('edit');
    setFormData({ ...row });
    setIsModalOpen(true);
  };

  const handleSave = async (e) => {
    e.preventDefault();
    setSaving(true);
    try {
      if (modalMode === 'add') {
        await api.post(`/admin/tables/${activeTable}`, formData);
      } else {
        await api.put(`/admin/tables/${activeTable}/${formData.id}`, formData);
      }
      setIsModalOpen(false);
      fetchTableData(activeTable);
    } catch (err) {
      console.error('Error saving record', err);
      alert('Error saving record. Check console.');
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Are you sure you want to permanently delete this record?')) return;
    try {
      await api.delete(`/admin/tables/${activeTable}/${id}`);
      fetchTableData(activeTable);
    } catch (err) {
      console.error('Error deleting record', err);
      alert('Error deleting record. Might be constrained by foreign keys.');
    }
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  if (loading && !activeTable) {
    return <div className="min-h-screen bg-gray-900 flex items-center justify-center"><Loader2 className="w-8 h-8 text-red-500 animate-spin" /></div>;
  }

  return (
    <div className="min-h-screen bg-gray-900 text-gray-100 flex flex-col md:flex-row">
      {/* Sidebar */}
      <div className="w-full md:w-64 bg-gray-800 border-r border-gray-700 flex flex-col h-screen shrink-0 sticky top-0">
        <div className="p-4 border-b border-gray-700 flex items-center justify-between">
          <div className="flex items-center space-x-2">
            <Database className="w-6 h-6 text-red-500" />
            <span className="font-bold text-lg tracking-wide text-white">Super Admin</span>
          </div>
        </div>
        <div className="flex-1 overflow-y-auto py-4">
          <div className="px-4 mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Database Tables</div>
          <nav className="space-y-1 px-2">
            {tables.map(table => (
              <button
                key={table}
                onClick={() => setActiveTable(table)}
                className={`w-full flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors ${
                  activeTable === table 
                    ? 'bg-red-500/10 text-red-400 border border-red-500/20' 
                    : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                }`}
              >
                <TableIcon className={`mr-3 h-4 w-4 ${activeTable === table ? 'text-red-400' : 'text-gray-400'}`} />
                {table}
              </button>
            ))}
          </nav>
        </div>
        <div className="p-4 border-t border-gray-700">
          <button
            onClick={handleLogout}
            className="w-full flex items-center px-3 py-2 text-sm font-medium text-gray-400 hover:text-white hover:bg-gray-700 rounded-md transition-colors"
          >
            <LogOut className="mr-3 h-4 w-4" />
            Sign Out
          </button>
        </div>
      </div>

      {/* Main Content */}
      <div className="flex-1 flex flex-col h-screen overflow-hidden">
        <div className="p-6 border-b border-gray-800 bg-gray-900 flex justify-between items-center">
          <div>
            <h1 className="text-2xl font-bold text-white uppercase tracking-wider flex items-center">
              <span className="text-red-500 mr-2">/</span> {activeTable}
            </h1>
            <p className="text-gray-400 text-sm mt-1">Direct database table manipulation. Use with caution.</p>
          </div>
          <button 
            onClick={openAddModal}
            className="flex items-center px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 transition-colors shadow-lg shadow-red-900/20"
          >
            <Plus className="w-4 h-4 mr-2" />
            Insert Record
          </button>
        </div>

        <div className="flex-1 overflow-auto p-6 bg-[#0f172a]">
          {loading ? (
            <div className="flex justify-center items-center h-64">
              <Loader2 className="w-8 h-8 text-red-500 animate-spin" />
            </div>
          ) : (
            <div className="bg-gray-800 rounded-xl shadow-xl border border-gray-700 overflow-hidden">
              <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-700">
                  <thead className="bg-gray-900/50">
                    <tr>
                      {schema.map(col => (
                        <th key={col.name} className="px-6 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider whitespace-nowrap">
                          {col.name} {col.is_primary && <span className="text-red-500 ml-1">PK</span>}
                        </th>
                      ))}
                      <th className="px-6 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider sticky right-0 bg-gray-900/50">
                        Actions
                      </th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-700/50 bg-gray-800">
                    {data.length === 0 ? (
                      <tr>
                        <td colSpan={schema.length + 1} className="px-6 py-8 text-center text-gray-400">
                          No records found in {activeTable}
                        </td>
                      </tr>
                    ) : (
                      data.map((row, idx) => (
                        <tr key={row.id || idx} className="hover:bg-gray-750 transition-colors group">
                          {schema.map(col => (
                            <td key={col.name} className="px-6 py-4 whitespace-nowrap text-sm text-gray-300 max-w-xs truncate">
                              {row[col.name] !== null ? String(row[col.name]) : <span className="text-gray-600 italic">null</span>}
                            </td>
                          ))}
                          <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium sticky right-0 bg-gray-800 group-hover:bg-gray-750 transition-colors">
                            <button onClick={() => openEditModal(row)} className="text-blue-400 hover:text-blue-300 mr-4 transition-colors">
                              <Edit2 className="w-4 h-4 inline" />
                            </button>
                            <button onClick={() => handleDelete(row.id)} className="text-red-400 hover:text-red-300 transition-colors">
                              <Trash2 className="w-4 h-4 inline" />
                            </button>
                          </td>
                        </tr>
                      ))
                    )}
                  </tbody>
                </table>
              </div>
            </div>
          )}
        </div>
      </div>

      {/* CRUD Modal */}
      {isModalOpen && (
        <div className="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
          <div className="bg-gray-800 rounded-xl max-w-2xl w-full max-h-[90vh] flex flex-col shadow-2xl border border-gray-700">
            <div className="flex justify-between items-center p-6 border-b border-gray-700 bg-gray-900/50 rounded-t-xl">
              <h2 className="text-xl font-bold text-white flex items-center">
                <Database className="w-5 h-5 mr-2 text-red-500" />
                {modalMode === 'add' ? 'Insert into' : 'Update record in'} <span className="ml-2 text-red-400">{activeTable}</span>
              </h2>
              <button onClick={() => setIsModalOpen(false)} className="text-gray-400 hover:text-white transition-colors">
                <X className="w-6 h-6" />
              </button>
            </div>
            
            <div className="flex-1 overflow-y-auto p-6 custom-scrollbar">
              <form id="crud-form" onSubmit={handleSave} className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {schema.map(col => {
                  if (col.name === 'id' || col.name === 'created_at' || col.name === 'updated_at') {
                    if (modalMode === 'add') return null;
                    return (
                      <div key={col.name} className="md:col-span-2">
                        <label className="block text-sm font-medium text-gray-500 mb-1 capitalize">{col.name.replace(/_/g, ' ')}</label>
                        <input
                          type="text"
                          value={formData[col.name] || ''}
                          disabled
                          className="w-full bg-gray-900 border border-gray-700 rounded-md px-3 py-2 text-gray-500 cursor-not-allowed text-sm"
                        />
                      </div>
                    );
                  }

                  return (
                    <div key={col.name} className={col.type === 'text' ? 'md:col-span-2' : ''}>
                      <label className="block text-sm font-medium text-gray-300 mb-1 capitalize">
                        {col.name.replace(/_/g, ' ')}
                        {col.is_primary && <span className="text-red-500 ml-1">*</span>}
                      </label>
                      <input
                        type={col.type === 'integer' ? 'number' : 'text'}
                        name={col.name}
                        value={formData[col.name] || ''}
                        onChange={handleInputChange}
                        className="w-full bg-gray-900 border border-gray-700 rounded-md px-3 py-2 text-white placeholder-gray-600 focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-colors text-sm"
                        placeholder={`Enter ${col.name}`}
                      />
                    </div>
                  );
                })}
              </form>
            </div>

            <div className="p-6 border-t border-gray-700 bg-gray-900/50 rounded-b-xl flex justify-end space-x-3">
              <button
                type="button"
                onClick={() => setIsModalOpen(false)}
                className="px-4 py-2 border border-gray-600 text-gray-300 rounded-md hover:bg-gray-800 transition-colors text-sm font-medium"
              >
                Cancel
              </button>
              <button
                type="submit"
                form="crud-form"
                disabled={saving}
                className="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors text-sm font-medium flex items-center"
              >
                {saving && <Loader2 className="w-4 h-4 mr-2 animate-spin" />}
                {modalMode === 'add' ? 'Insert Record' : 'Save Changes'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
