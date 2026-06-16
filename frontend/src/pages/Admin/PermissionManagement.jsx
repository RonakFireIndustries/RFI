import { useState, useEffect } from 'react';
import api from '../../services/api';
import { Shield, CheckCircle2, Loader2 } from 'lucide-react';

export default function PermissionManagement() {
  const [roles, setRoles] = useState([]);
  const [permissions, setPermissions] = useState([]);
  const [selectedRole, setSelectedRole] = useState(null);
  const [assignedPermissions, setAssignedPermissions] = useState([]);
  const [saving, setSaving] = useState(false);
  const [loading, setLoading] = useState(true);
  const [message, setMessage] = useState('');

  useEffect(() => {
    const fetchData = async () => {
      setLoading(true);
      try {
        const [rolesRes, permissionsRes] = await Promise.all([
          api.get('/roles'),
          api.get('/permissions'),
        ]);

        setRoles(rolesRes.data);
        setPermissions(permissionsRes.data);

        if (rolesRes.data.length > 0) {
          setSelectedRole(rolesRes.data[0]);
          setAssignedPermissions(rolesRes.data[0].permissions || []);
        }
      } catch (error) {
        console.error('Failed to load permissions data', error);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, []);

  const handleRoleChange = (roleName) => {
    const role = roles.find((item) => item.name === roleName);
    if (!role) return;
    setSelectedRole(role);
    setAssignedPermissions(role.permissions || []);
    setMessage('');
  };

  const togglePermission = (permissionName) => {
    setAssignedPermissions((current) => {
      if (current.includes(permissionName)) {
        return current.filter((item) => item !== permissionName);
      }
      return [...current, permissionName];
    });
  };

  const handleSave = async () => {
    if (!selectedRole) return;
    setSaving(true);
    setMessage('');

    try {
      const response = await api.put(`/roles/${selectedRole.name}/permissions`, {
        permissions: assignedPermissions,
      });

      const updatedRole = response.data.role;
      setRoles((currentRoles) =>
        currentRoles.map((role) =>
          role.name === updatedRole.name ? updatedRole : role
        )
      );
      setSelectedRole(updatedRole);
      setMessage('Role permission defaults updated successfully.');
    } catch (error) {
      console.error('Failed to update role permissions', error);
      setMessage('Unable to save changes. Check the console for details.');
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-full">
        <Loader2 className="w-10 h-10 animate-spin text-[#1a56db]" />
      </div>
    );
  }

  return (
    <div className="space-y-6 pb-12">
      <div className="flex flex-col sm:flex-row sm:justify-between sm:items-end gap-4">
        <div>
          <div className="inline-flex items-center gap-2 mb-2 rounded-full bg-[#eff6ff] px-3 py-1 text-sm font-medium text-[#1d4ed8]">
            <Shield className="w-4 h-4" />
            Permission Management
          </div>
          <h1 className="text-2xl font-bold text-gray-900">Role Permission Defaults</h1>
          <p className="text-sm text-gray-500 max-w-2xl">
            Use this page to manage which website sections are enabled by default for each role. Employee-level permission overrides can still be applied from the employee profile.
          </p>
        </div>
      </div>

      <div className="grid grid-cols-1 xl:grid-cols-[320px_1fr] gap-6">
        <div className="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Roles</h2>
          <div className="space-y-3">
            {roles.map((role) => (
              <button
                key={role.name}
                type="button"
                onClick={() => handleRoleChange(role.name)}
                className={`w-full text-left px-4 py-3 rounded-2xl transition ${
                  selectedRole?.name === role.name
                    ? 'bg-[#1a56db] text-white shadow-sm'
                    : 'bg-gray-50 text-gray-700 hover:bg-gray-100'
                }`}
              >
                <span className="font-medium">{role.name}</span>
                <p className="text-xs text-gray-500 mt-1">{role.permissions?.length || 0} permissions assigned</p>
              </button>
            ))}
          </div>
        </div>

        <div className="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
          <div className="flex items-center justify-between gap-4 mb-6">
            <div>
              <p className="text-sm text-gray-500 uppercase tracking-wide">Selected Role</p>
              <h2 className="text-xl font-semibold text-gray-900">{selectedRole?.name || 'No role selected'}</h2>
            </div>
            <span className="inline-flex items-center gap-2 rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-700">
              <CheckCircle2 className="w-4 h-4" />
              Default assignment
            </span>
          </div>

          <div className="grid gap-3">
            {permissions.map((permission) => (
              <button
                key={permission}
                type="button"
                onClick={() => togglePermission(permission)}
                className={`flex items-center justify-between w-full rounded-2xl border px-4 py-3 text-left transition ${
                  assignedPermissions.includes(permission)
                    ? 'border-[#1a56db] bg-[#eff6ff] text-[#1a56db]'
                    : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300 hover:bg-gray-50'
                }`}
              >
                <span>{permission}</span>
                <span className="text-sm font-semibold">
                  {assignedPermissions.includes(permission) ? 'Enabled' : 'Disabled'}
                </span>
              </button>
            ))}
          </div>

          <div className="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div className="text-sm text-gray-500">{message}</div>
            <button
              type="button"
              onClick={handleSave}
              disabled={saving || !selectedRole}
              className="inline-flex items-center justify-center rounded-2xl bg-[#1a56db] px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
            >
              {saving ? 'Saving...' : 'Save Role Defaults'}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
