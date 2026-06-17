import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useRoleStore } from '../../../store/roleStore';
import { usePermissionStore } from '../../../store/permissionStore';

export default function RoleForm() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { createItem, updateItem, fetchItem } = useRoleStore();
  const { items: allPermissions, fetchItems: fetchPermissions } = usePermissionStore();
  
  const [name, setName] = useState('');
  const [selectedPermissions, setSelectedPermissions] = useState([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    fetchPermissions();
    if (id) {
      loadRole();
    }
  }, [id]);

  const loadRole = async () => {
    const role = await fetchItem(id);
    setName(role.name);
    setSelectedPermissions(role.permissions?.map(p => p.name) || []);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    try {
      const payload = { name, permissions: selectedPermissions };
      if (id) {
        await updateItem(id, payload);
      } else {
        await createItem(payload);
      }
      navigate('/dashboard/roles');
    } catch (error) {
      alert('Error saving role: ' + (error.response?.data?.message || error.message));
    } finally {
      setLoading(false);
    }
  };

  const handlePermissionToggle = (permissionName) => {
    setSelectedPermissions(prev => 
      prev.includes(permissionName)
        ? prev.filter(p => p !== permissionName)
        : [...prev, permissionName]
    );
  };

  return (
    <div className="bg-white shadow sm:rounded-lg max-w-3xl mx-auto">
      <div className="px-4 py-5 sm:p-6">
        <h3 className="text-lg leading-6 font-medium text-gray-900">
          {id ? 'Edit Role' : 'Create Role'}
        </h3>
        <form onSubmit={handleSubmit} className="mt-5 space-y-6">
          <div>
            <label htmlFor="name" className="block text-sm font-medium text-gray-700">Role Name</label>
            <input
              type="text"
              id="name"
              value={name}
              onChange={(e) => setName(e.target.value)}
              className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-[#1a56db] focus:border-[#1a56db] sm:text-sm"
              required
            />
          </div>

          <div>
            <h4 className="text-sm font-medium text-gray-700 mb-2">Permissions</h4>
            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
              {allPermissions.map((permission) => (
                <div key={permission.id} className="relative flex items-start">
                  <div className="flex items-center h-5">
                    <input
                      id={`permission-${permission.id}`}
                      type="checkbox"
                      checked={selectedPermissions.includes(permission.name)}
                      onChange={() => handlePermissionToggle(permission.name)}
                      className="focus:ring-[#1a56db] h-4 w-4 text-[#1a56db] border-gray-300 rounded"
                    />
                  </div>
                  <div className="ml-3 text-sm">
                    <label htmlFor={`permission-${permission.id}`} className="font-medium text-gray-700">
                      {permission.name}
                    </label>
                  </div>
                </div>
              ))}
            </div>
          </div>

          <div className="flex justify-end space-x-3">
            <button
              type="button"
              onClick={() => navigate('/dashboard/roles')}
              className="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={loading}
              className="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[#1a56db] hover:bg-[#1e4ebd] focus:outline-none"
            >
              {loading ? 'Saving...' : 'Save Role'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
