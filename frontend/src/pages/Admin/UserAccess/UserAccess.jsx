import React, { useState, useEffect } from 'react';
import api from '../../../services/api';
import { useAccessStore } from '../../../store/accessStore';
import { useRoleStore } from '../../../store/roleStore';
import { usePermissionStore } from '../../../store/permissionStore';

export default function UserAccess() {
  const [users, setUsers] = useState([]);
  const [selectedUser, setSelectedUser] = useState(null);
  
  const { userRoles, userPermissions, fetchUserRoles, fetchUserPermissions, assignRole, removeRole, assignPermissions, removePermission } = useAccessStore();
  const { items: allRoles, fetchItems: fetchRoles } = useRoleStore();
  const { items: allPermissions, fetchItems: fetchPermissionsList } = usePermissionStore();

  const [selectedRoles, setSelectedRoles] = useState([]);
  const [selectedPerms, setSelectedPerms] = useState([]);

  useEffect(() => {
    fetchUsers();
    fetchRoles();
    fetchPermissionsList();
  }, []);

  const fetchUsers = async () => {
    try {
      const response = await api.get('/employees'); // or users endpoint
      setUsers(Array.isArray(response.data?.employees) ? response.data.employees : []);
    } catch (error) {
      console.error('Failed to fetch users', error);
    }
  };

  const handleSelectUser = async (user) => {
    setSelectedUser(user);
    const userId = user.id || user.user_id; // Depend on if it's employee or user model
    if (userId) {
      await fetchUserRoles(userId);
      await fetchUserPermissions(userId);
    }
  };

  const handleSaveRoles = async () => {
    if (!selectedUser) return;
    const userId = selectedUser.id || selectedUser.user_id;
    await assignRole(userId, selectedRoles);
    setSelectedRoles([]);
  };

  const handleSavePermissions = async () => {
    if (!selectedUser) return;
    const userId = selectedUser.id || selectedUser.user_id;
    await assignPermissions(userId, selectedPerms);
    setSelectedPerms([]);
  };

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold text-gray-900">User Access Management</h1>
      
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {/* User Selection */}
        <div className="bg-white shadow sm:rounded-lg overflow-hidden h-96 flex flex-col">
          <div className="px-4 py-5 border-b border-gray-200">
            <h3 className="text-lg leading-6 font-medium text-gray-900">Select User</h3>
          </div>
          <div className="flex-1 overflow-y-auto p-4 space-y-2">
            {users.map((user) => (
              <div 
                key={user.id} 
                onClick={() => handleSelectUser(user)}
                className={`p-3 rounded-md cursor-pointer transition-colors border ${selectedUser?.id === user.id ? 'bg-[#e1effe] border-[#1a56db]' : 'border-gray-200 hover:bg-gray-50'}`}
              >
                <div className="font-medium text-gray-900">{user.full_name ? `${user.full_name}` : user.name}</div>
                <div className="text-sm text-gray-500">{user.email}</div>
              </div>
            ))}
          </div>
        </div>

        {/* Roles & Permissions */}
        <div className="md:col-span-2 space-y-6">
          {selectedUser ? (
            <>
              {/* Roles Panel */}
              <div className="bg-white shadow sm:rounded-lg px-4 py-5 sm:p-6">
                <h3 className="text-lg leading-6 font-medium text-gray-900 mb-4">Roles for {selectedUser.first_name || selectedUser.name}</h3>
                
                <div className="mb-4 flex flex-wrap gap-2">
                  {userRoles.map(role => (
                    <span key={role.id} className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                      {role.name}
                      <button type="button" onClick={() => removeRole(selectedUser.id || selectedUser.user_id, role.id)} className="ml-1.5 inline-flex items-center justify-center h-4 w-4 rounded-full text-indigo-400 hover:bg-indigo-200 hover:text-indigo-500 focus:outline-none">
                        <span className="sr-only">Remove role</span>
                        <svg className="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8"><path strokeLinecap="round" strokeWidth="1.5" d="M1 1l6 6m0-6L1 7" /></svg>
                      </button>
                    </span>
                  ))}
                </div>

                <div className="flex space-x-2">
                  <select 
                    multiple
                    value={selectedRoles}
                    onChange={(e) => setSelectedRoles(Array.from(e.target.selectedOptions, option => option.value))}
                    className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-[#1a56db] focus:border-[#1a56db] sm:text-sm rounded-md"
                  >
                    {allRoles.filter(r => !userRoles.find(ur => ur.id === r.id)).map(role => (
                      <option key={role.id} value={role.name}>{role.name}</option>
                    ))}
                  </select>
                  <button onClick={handleSaveRoles} className="mt-1 inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[#1a56db] hover:bg-[#1e4ebd]">
                    Add
                  </button>
                </div>
              </div>

              {/* Permissions Panel */}
              <div className="bg-white shadow sm:rounded-lg px-4 py-5 sm:p-6">
                <h3 className="text-lg leading-6 font-medium text-gray-900 mb-4">Direct Permissions</h3>
                
                <div className="mb-4 flex flex-wrap gap-2">
                  {userPermissions.map(perm => (
                    <span key={perm.id || perm.name} className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                      {perm.name}
                      <button type="button" onClick={() => removePermission(selectedUser.id || selectedUser.user_id, perm.id)} className="ml-1.5 inline-flex items-center justify-center h-4 w-4 rounded-full text-green-400 hover:bg-green-200 hover:text-green-500 focus:outline-none">
                        <span className="sr-only">Remove permission</span>
                        <svg className="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8"><path strokeLinecap="round" strokeWidth="1.5" d="M1 1l6 6m0-6L1 7" /></svg>
                      </button>
                    </span>
                  ))}
                </div>

                <div className="flex space-x-2">
                  <select 
                    multiple
                    value={selectedPerms}
                    onChange={(e) => setSelectedPerms(Array.from(e.target.selectedOptions, option => option.value))}
                    className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-[#1a56db] focus:border-[#1a56db] sm:text-sm rounded-md"
                  >
                    {allPermissions.filter(p => !userPermissions.find(up => up.name === p.name)).map(perm => (
                      <option key={perm.id} value={perm.name}>{perm.name}</option>
                    ))}
                  </select>
                  <button onClick={handleSavePermissions} className="mt-1 inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[#1a56db] hover:bg-[#1e4ebd]">
                    Add
                  </button>
                </div>
              </div>
            </>
          ) : (
            <div className="bg-white shadow sm:rounded-lg p-6 flex items-center justify-center h-full text-gray-500">
              Select a user from the list to manage their access.
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
