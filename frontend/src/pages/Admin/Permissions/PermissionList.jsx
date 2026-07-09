import React, { useEffect } from 'react';
import { Plus, Edit, Trash2 } from 'lucide-react';
import { usePermissionStore } from '../../../store/permissionStore';
import { Link } from 'react-router-dom';

export default function PermissionList() {
  const { items: permissions, fetchItems, deleteItem, loading } = usePermissionStore();

  useEffect(() => {
    fetchItems();
  }, []);

  const handleDelete = async (id) => {
    if (window.confirm('Are you sure you want to delete this permission?')) {
      await deleteItem(id);
    }
  };

  if (loading) return <div>Loading permissions...</div>;

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-bold text-gray-900">Permission Management</h1>
        <Link
          to="/dashboard/permissions/create"
          className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-primary-foreground bg-primary hover:bg-primary/90"
        >
          <Plus className="-ml-1 mr-2 h-5 w-5" aria-hidden="true" />
          New Permission
        </Link>
      </div>

      <div className="bg-white shadow overflow-hidden sm:rounded-md">
        <ul className="divide-y divide-gray-200">
          {permissions.map((permission) => (
            <li key={permission.id}>
              <div className="px-4 py-4 sm:px-6 flex items-center justify-between">
                <div>
                  <h3 className="text-lg font-medium text-primary">{permission.name}</h3>
                </div>
                <div className="flex items-center space-x-3">
                  <Link to={`/dashboard/permissions/${permission.id}/edit`} className="text-indigo-600 hover:text-indigo-900">
                    <Edit className="h-5 w-5" />
                  </Link>
                  <button onClick={() => handleDelete(permission.id)} className="text-red-600 hover:text-red-900">
                    <Trash2 className="h-5 w-5" />
                  </button>
                </div>
              </div>
            </li>
          ))}
        </ul>
      </div>
    </div>
  );
}
