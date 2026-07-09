import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { usePermissionStore } from '../../../store/permissionStore';

export default function PermissionForm() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { createItem, updateItem, fetchItem } = usePermissionStore();
  
  const [name, setName] = useState('');
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (id) {
      loadPermission();
    }
  }, [id]);

  const loadPermission = async () => {
    const permission = await fetchItem(id);
    setName(permission.name);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    try {
      const payload = { name };
      if (id) {
        await updateItem(id, payload);
      } else {
        await createItem(payload);
      }
      navigate('/dashboard/permissions');
    } catch (error) {
      alert('Error saving permission: ' + (error.response?.data?.message || error.message));
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="bg-white shadow sm:rounded-lg max-w-xl mx-auto">
      <div className="px-4 py-5 sm:p-6">
        <h3 className="text-lg leading-6 font-medium text-gray-900">
          {id ? 'Edit Permission' : 'Create Permission'}
        </h3>
        <form onSubmit={handleSubmit} className="mt-5 space-y-6">
          <div>
            <label htmlFor="name" className="block text-sm font-medium text-gray-700">Permission Name</label>
            <input
              type="text"
              id="name"
              value={name}
              onChange={(e) => setName(e.target.value)}
              placeholder="e.g. module.action"
              className="mt-1 block w-full border border-input rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"
              required
            />
          </div>

          <div className="flex justify-end space-x-3">
            <button
              type="button"
              onClick={() => navigate('/dashboard/permissions')}
              className="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={loading}
              className="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-primary-foreground bg-primary hover:bg-primary/90 focus:outline-none"
            >
              {loading ? 'Saving...' : 'Save Permission'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
