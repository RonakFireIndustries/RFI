import React, { useState, useEffect, useMemo } from 'react';
import api from '../../../services/api';
import { useAccessStore } from '../../../store/accessStore';
import { useRoleStore } from '../../../store/roleStore';
import { usePermissionStore } from '../../../store/permissionStore';

const getErrorMessage = (error, fallback) =>
  error?.response?.data?.message || error?.message || fallback;

export default function UserAccess() {
  const [users, setUsers] = useState([]);
  const [usersLoading, setUsersLoading] = useState(true);
  const [usersError, setUsersError] = useState(null);
  const [selectedUserId, setSelectedUserId] = useState(null);
  const [search, setSearch] = useState('');
  const [rolesLoading, setRolesLoading] = useState(false);
  const [permsLoading, setPermsLoading] = useState(false);
  const [selectedRoles, setSelectedRoles] = useState([]);
  const [selectedPerms, setSelectedPerms] = useState([]);
  const [feedback, setFeedback] = useState(null);

  const {
    userRoles,
    userPermissions,
    fetchUserRoles,
    fetchUserPermissions,
    assignRole,
    removeRole,
    assignPermissions,
    removePermission,
  } = useAccessStore();

  const { items: allRoles, fetchItems: fetchRoles } = useRoleStore();
  const { items: allPermissions, fetchItems: fetchPermissionsList } = usePermissionStore();

  useEffect(() => {
    fetchUsers();
    fetchRoles();
    fetchPermissionsList();
  }, []);

  const fetchUsers = async () => {
    setUsersLoading(true);
    setUsersError(null);
    try {
      const response = await api.get('/users');
      setUsers(Array.isArray(response.data) ? response.data : []);
    } catch (error) {
      setUsersError(getErrorMessage(error, 'Could not load the user list.'));
    } finally {
      setUsersLoading(false);
    }
  };

  const selectedUser = useMemo(
    () => users.find((user) => user.id === selectedUserId) || null,
    [users, selectedUserId],
  );

  const filteredUsers = useMemo(() => {
    const term = search.trim().toLowerCase();
    if (!term) return users;
    return users.filter((user) =>
      `${user.name} ${user.email} ${(user.roles || []).map((r) => r.name).join(' ')}`
        .toLowerCase()
        .includes(term),
    );
  }, [users, search]);

  const handleSelectUser = async (user) => {
    setFeedback(null);
    setSelectedUserId(user.id);
    setSelectedRoles([]);
    setSelectedPerms([]);
    setRolesLoading(true);
    setPermsLoading(true);
    try {
      await Promise.all([fetchUserRoles(user.id), fetchUserPermissions(user.id)]);
    } catch (error) {
      setFeedback({ type: 'error', text: getErrorMessage(error, 'Could not load this user\'s access details.') });
    } finally {
      setRolesLoading(false);
      setPermsLoading(false);
    }
  };

  const syncUserRoles = (roles) => {
    setUsers((prev) =>
      prev.map((user) => (user.id === selectedUserId ? { ...user, roles } : user)),
    );
  };

  const refreshPermissions = async () => {
    try {
      await fetchUserPermissions(selectedUserId);
    } catch (error) {
      setFeedback({ type: 'error', text: getErrorMessage(error, 'Could not refresh permissions.') });
    }
  };

  const handleAddRoles = async () => {
    if (!selectedUser || selectedRoles.length === 0) return;
    setFeedback(null);
    setRolesLoading(true);
    try {
      const updatedRoles = await assignRole(selectedUserId, selectedRoles);
      syncUserRoles(updatedRoles);
      await refreshPermissions();
      setFeedback({ type: 'success', text: 'Role added successfully.' });
      setSelectedRoles([]);
    } catch (error) {
      setFeedback({ type: 'error', text: getErrorMessage(error, 'Could not add the role.') });
    } finally {
      setRolesLoading(false);
    }
  };

  const handleRemoveRole = async (roleId, roleName) => {
    if (!selectedUser) return;
    setFeedback(null);
    setRolesLoading(true);
    try {
      const updatedRoles = await removeRole(selectedUserId, roleId);
      syncUserRoles(updatedRoles);
      await refreshPermissions();
      setFeedback({ type: 'success', text: `Removed the "${roleName}" role.` });
    } catch (error) {
      setFeedback({ type: 'error', text: getErrorMessage(error, `Could not remove the "${roleName}" role.`) });
    } finally {
      setRolesLoading(false);
    }
  };

  const handleAddPermissions = async () => {
    if (!selectedUser || selectedPerms.length === 0) return;
    setFeedback(null);
    setPermsLoading(true);
    try {
      await assignPermissions(selectedUserId, selectedPerms);
      setFeedback({ type: 'success', text: 'Permission added successfully.' });
      setSelectedPerms([]);
    } catch (error) {
      setFeedback({ type: 'error', text: getErrorMessage(error, 'Could not add the permission.') });
    } finally {
      setPermsLoading(false);
    }
  };

  const handleRemovePermission = async (permissionId, permissionName) => {
    if (!selectedUser) return;
    setFeedback(null);
    setPermsLoading(true);
    try {
      await removePermission(selectedUserId, permissionId);
      setFeedback({ type: 'success', text: `Removed the "${permissionName}" permission.` });
    } catch (error) {
      setFeedback({ type: 'error', text: getErrorMessage(error, `Could not remove the "${permissionName}" permission.`) });
    } finally {
      setPermsLoading(false);
    }
  };

  const roleOptions = allRoles.filter(
    (role) => !userRoles.some((assigned) => assigned.id === role.id),
  );
  const permOptions = allPermissions.filter(
    (perm) => !userPermissions.some((assigned) => assigned.name === perm.name),
  );

  return (
    <div className="space-y-6">
      <div className="bg-white shadow sm:rounded-lg px-6 py-5">
        <h1 className="text-2xl font-bold text-gray-900">User Access</h1>
        <p className="mt-2 text-sm text-gray-600">
          Decide what each person can do in the system. Pick a person from the list, then assign
          them <strong>roles</strong> (job positions such as &ldquo;Manager&rdquo; or
          &ldquo;Salesperson&rdquo;). Each role automatically comes with the permissions it needs,
          so you usually don&apos;t have to think about individual permissions.
        </p>
      </div>

      {feedback && (
        <div
          className={`px-4 py-3 rounded-md border text-sm flex items-center justify-between ${
            feedback.type === 'success'
              ? 'bg-green-50 border-green-200 text-green-800'
              : 'bg-red-50 border-red-200 text-red-800'
          }`}
        >
          <span>{feedback.text}</span>
          <button
            type="button"
            onClick={() => setFeedback(null)}
            className="ml-4 text-xs underline hover:opacity-70"
          >
            Dismiss
          </button>
        </div>
      )}

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {/* User list */}
        <div className="bg-white shadow sm:rounded-lg overflow-hidden h-[32rem] flex flex-col">
          <div className="px-4 py-4 border-b border-gray-200">
            <h3 className="text-base font-semibold text-gray-900">People</h3>
            <p className="mt-1 text-xs text-gray-500">Choose a person to manage their access.</p>
            <input
              type="text"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search name or email..."
              className="mt-3 block w-full rounded-md border-gray-300 shadow-sm focus:ring-ring focus:border-ring sm:text-sm"
            />
          </div>
          <div className="flex-1 overflow-y-auto p-3 space-y-2 bg-gray-50">
            {usersLoading ? (
              <div className="flex items-center justify-center h-full text-sm text-gray-500">
                Loading people...
              </div>
            ) : usersError ? (
              <div className="flex flex-col items-center justify-center h-full text-center p-4">
                <p className="text-sm text-red-600">{usersError}</p>
                <button
                  type="button"
                  onClick={fetchUsers}
                  className="mt-3 text-sm font-medium text-primary hover:underline"
                >
                  Try again
                </button>
              </div>
            ) : filteredUsers.length === 0 ? (
              <div className="flex items-center justify-center h-full text-sm text-gray-500 text-center p-4">
                {search
                  ? 'No one matches your search.'
                  : 'No people found yet. Users appear here once they have an account.'}
              </div>
            ) : (
              filteredUsers.map((user) => (
                <button
                  type="button"
                  key={user.id}
                  onClick={() => handleSelectUser(user)}
                  className={`w-full text-left p-3 rounded-md transition-colors border ${
                    selectedUser?.id === user.id
                      ? 'bg-primary/10 border-primary/40'
                      : 'border-gray-200 bg-white hover:bg-primary/5'
                  }`}
                >
                  <div className="flex items-center justify-between gap-2">
                    <span className="font-medium text-sm text-gray-900 truncate">{user.name}</span>
                    <span className="shrink-0 text-xs text-gray-400">
                      {(user.roles || []).length} role{(user.roles || []).length === 1 ? '' : 's'}
                    </span>
                  </div>
                  <div className="text-xs text-gray-500 truncate">{user.email}</div>
                  {(user.roles || []).length > 0 && (
                    <div className="mt-1.5 flex flex-wrap gap-1">
                      {user.roles.slice(0, 3).map((role) => (
                        <span
                          key={role.id}
                          className="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-indigo-100 text-indigo-700"
                        >
                          {role.name}
                        </span>
                      ))}
                      {(user.roles || []).length > 3 && (
                        <span className="text-[10px] text-gray-400">
                          +{(user.roles || []).length - 3} more
                        </span>
                      )}
                    </div>
                  )}
                </button>
              ))
            )}
          </div>
        </div>

        {/* Details */}
        <div className="md:col-span-2 space-y-6">
          {!selectedUser ? (
            <div className="bg-white shadow sm:rounded-lg p-8 flex flex-col items-center justify-center text-center">
              <div className="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center mb-4">
                <svg className="w-7 h-7 text-primary" fill="none" stroke="currentColor" strokeWidth="1.5" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
              </div>
              <h3 className="text-lg font-semibold text-gray-900">No person selected</h3>
              <ol className="mt-3 text-sm text-gray-600 list-decimal list-inside text-left space-y-1.5">
                <li>Click a person&apos;s name on the left.</li>
                <li>Add the roles (job positions) that match their work.</li>
                <li>Optional: grant individual permissions if a role isn&apos;t quite right.</li>
              </ol>
            </div>
          ) : (
            <>
              <div className="bg-white shadow sm:rounded-lg px-5 py-4">
                <div className="flex items-center gap-4">
                  <div className="w-12 h-12 rounded-full bg-primary text-white flex items-center justify-center text-lg font-bold">
                    {selectedUser.name.charAt(0).toUpperCase()}
                  </div>
                  <div>
                    <h2 className="text-lg font-semibold text-gray-900">{selectedUser.name}</h2>
                    <p className="text-sm text-gray-500">{selectedUser.email}</p>
                  </div>
                </div>
                <p className="mt-3 text-xs text-gray-500">
                  This person currently has <strong>{userRoles.length}</strong> role
                  {userRoles.length === 1 ? '' : 's'} and{' '}
                  <strong>{userPermissions.length}</strong> permission
                  {userPermissions.length === 1 ? '' : 's'} overall.
                </p>
              </div>

              {/* Roles panel */}
              <div className="bg-white shadow sm:rounded-lg px-5 py-5">
                <h3 className="text-base font-semibold text-gray-900">
                  Roles <span className="font-normal text-gray-400">(job positions)</span>
                </h3>
                <p className="mt-1 text-sm text-gray-600">
                  A role is a job position, like &ldquo;Sales Manager&rdquo;. Assigning a role gives
                  the person everything that position is allowed to do. Add a role below, or remove
                  one with the &times; button.
                </p>

                {userRoles.length === 0 ? (
                  <p className="mt-4 text-sm text-gray-400 italic">
                    No roles assigned yet.
                  </p>
                ) : (
                  <div className="mt-4 flex flex-wrap gap-2">
                    {userRoles.map((role) => (
                      <span
                        key={role.id}
                        className="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"
                      >
                        {role.name}
                        <button
                          type="button"
                          disabled={rolesLoading}
                          onClick={() => handleRemoveRole(role.id, role.name)}
                          title={`Remove the "${role.name}" role`}
                          className="ml-1.5 inline-flex items-center justify-center h-4 w-4 rounded-full text-indigo-400 hover:bg-indigo-200 hover:text-indigo-600 focus:outline-none disabled:opacity-50"
                        >
                          <span className="sr-only">Remove role</span>
                          <svg className="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8"><path strokeLinecap="round" strokeWidth="1.5" d="M1 1l6 6m0-6L1 7" /></svg>
                        </button>
                      </span>
                    ))}
                  </div>
                )}

                <div className="mt-5 flex flex-col sm:flex-row gap-3">
                  <select
                    multiple
                    value={selectedRoles}
                    onChange={(e) => setSelectedRoles(Array.from(e.target.selectedOptions, (option) => option.value))}
                    className="block w-full rounded-md border-gray-300 shadow-sm focus:ring-ring focus:border-ring sm:text-sm"
                    title="Hold Ctrl (or Cmd) to pick more than one role"
                  >
                    {roleOptions.length === 0 ? (
                      <option value="" disabled>
                        All available roles are already assigned
                      </option>
                    ) : (
                      roleOptions.map((role) => (
                        <option key={role.id} value={role.name}>
                          {role.name}
                        </option>
                      ))
                    )}
                  </select>
                  <button
                    type="button"
                    onClick={handleAddRoles}
                    disabled={selectedRoles.length === 0 || rolesLoading}
                    className="shrink-0 inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    {rolesLoading ? 'Saving...' : 'Add Role'}
                  </button>
                </div>
                <p className="mt-2 text-xs text-gray-400">
                  Tip: hold <kbd className="px-1 border border-gray-300 rounded">Ctrl</kbd> (or{' '}
                  <kbd className="px-1 border border-gray-300 rounded">Cmd</kbd> on Mac) to choose
                  several roles at once. The system role &ldquo;Super Admin&rdquo; cannot be removed
                  from the main administrator account.
                </p>
              </div>

              {/* Permissions panel */}
              <div className="bg-white shadow sm:rounded-lg px-5 py-5">
                <h3 className="text-base font-semibold text-gray-900">
                  Permissions <span className="font-normal text-gray-400">(what they can do)</span>
                </h3>
                <p className="mt-1 text-sm text-gray-600">
                  These are the individual actions the person is allowed to take. They are usually
                  inherited automatically from the roles above. You can also add or remove a single
                  permission when one role is nearly right but not exactly.
                </p>

                {userPermissions.length === 0 ? (
                  <p className="mt-4 text-sm text-gray-400 italic">
                    No permissions yet. Add a role above and they will appear here automatically.
                  </p>
                ) : (
                  <div className="mt-4 flex flex-wrap gap-2">
                    {userPermissions.map((perm) => (
                      <span
                        key={perm.id || perm.name}
                        className="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"
                      >
                        {perm.name}
                        <button
                          type="button"
                          disabled={permsLoading}
                          onClick={() => handleRemovePermission(perm.id, perm.name)}
                          title={`Remove the "${perm.name}" permission`}
                          className="ml-1.5 inline-flex items-center justify-center h-4 w-4 rounded-full text-green-400 hover:bg-green-200 hover:text-green-600 focus:outline-none disabled:opacity-50"
                        >
                          <span className="sr-only">Remove permission</span>
                          <svg className="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8"><path strokeLinecap="round" strokeWidth="1.5" d="M1 1l6 6m0-6L1 7" /></svg>
                        </button>
                      </span>
                    ))}
                  </div>
                )}

                <div className="mt-5 flex flex-col sm:flex-row gap-3">
                  <select
                    multiple
                    value={selectedPerms}
                    onChange={(e) => setSelectedPerms(Array.from(e.target.selectedOptions, (option) => option.value))}
                    className="block w-full rounded-md border-gray-300 shadow-sm focus:ring-ring focus:border-ring sm:text-sm"
                    title="Hold Ctrl (or Cmd) to pick more than one permission"
                  >
                    {permOptions.length === 0 ? (
                      <option value="" disabled>
                        All available permissions are already granted
                      </option>
                    ) : (
                      permOptions.map((perm) => (
                        <option key={perm.id} value={perm.name}>
                          {perm.name}
                        </option>
                      ))
                    )}
                  </select>
                  <button
                    type="button"
                    onClick={handleAddPermissions}
                    disabled={selectedPerms.length === 0 || permsLoading}
                    className="shrink-0 inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    {permsLoading ? 'Saving...' : 'Add Permission'}
                  </button>
                </div>
              </div>
            </>
          )}
        </div>
      </div>
    </div>
  );
}
