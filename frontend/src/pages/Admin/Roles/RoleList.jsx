import React, { useEffect } from 'react';
import { Plus, Edit, Trash2 } from 'lucide-react';
import { useRoleStore } from '../../../store/roleStore';
import { Link } from 'react-router-dom';

export default function RoleList() {
  const { items: roles, fetchItems, deleteItem, loading } = useRoleStore();

  useEffect(() => {
    fetchItems();
  }, []);

  const handleDelete = async (id, name) => {
    if (name === 'Super Admin') {
      alert('Cannot delete Super Admin role.');
      return;
    }
    if (window.confirm('Are you sure you want to delete this role?')) {
      await deleteItem(id);
    }
  };

  if (loading) return <div>Loading roles...</div>;

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-bold text-gray-900">Role Management</h1>
        <Link
          to="/dashboard/roles/create"
          className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-primary/90"
        >
          <Plus className="-ml-1 mr-2 h-5 w-5" aria-hidden="true" />
          New Role
        </Link>
      </div>

      <div className="bg-white shadow overflow-hidden sm:rounded-md">
        <ul className="divide-y divide-gray-200">
          {roles.map((role) => (
            <li key={role.id}>
              <div className="px-4 py-4 sm:px-6 flex items-center justify-between">
                <div>
                  <h3 className="text-lg font-medium text-primary">{role.name}</h3>
                  <div className="mt-1 flex flex-wrap gap-1">
                    {role.permissions?.map((p) => (
                      <span key={p.id} className="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                        {p.name}
                      </span>
                    ))}
                  </div>
                </div>
                <div className="flex items-center space-x-3">
                  <Link to={`/dashboard/roles/${role.id}/edit`} className="text-indigo-600 hover:text-indigo-900">
                    <Edit className="h-5 w-5" />
                  </Link>
                  {role.name !== 'Super Admin' && (
                    <button onClick={() => handleDelete(role.id, role.name)} className="text-red-600 hover:text-red-900">
                      <Trash2 className="h-5 w-5" />
                    </button>
                  )}
                </div>
              </div>
            </li>
          ))}
        </ul>
      </div>
    </div>
  );
}
