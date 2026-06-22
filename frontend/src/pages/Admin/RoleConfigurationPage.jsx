import { useState, useEffect, useCallback } from 'react';
import api from '../../services/api';
import {
  Shield, Save, RotateCcw, ChevronDown, ChevronRight,
  CheckCircle2, XCircle, AlertTriangle, Loader2, Users,
  Settings, FileText, Sliders, Lock, Unlock,
  Search, Layers, Eye, Edit3, Trash2, Plus, Globe,
} from 'lucide-react';

function Skeleton({ className = '' }) {
  return <div className={`animate-pulse bg-gray-200 rounded ${className}`} />;
}

export default function RoleConfigurationPage() {
  const [roles, setRoles] = useState([]);
  const [permissions, setPermissions] = useState([]);
  const [scopes, setScopes] = useState([]);
  const [templates, setTemplates] = useState([]);
  const [exceptions, setExceptions] = useState([]);
  const [deps, setDeps] = useState([]);
  const [selectedRole, setSelectedRole] = useState(null);
  const [assignedPerms, setAssignedPerms] = useState([]);
  const [roleSummary, setRoleSummary] = useState(null);
  const [auditSettings, setAuditSettings] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [expandedModules, setExpandedModules] = useState({});
  const [searchQuery, setSearchQuery] = useState('');
  const [activeTab, setActiveTab] = useState('permissions');
  const [expandedExceptions, setExpandedExceptions] = useState({});

  const fetchAll = useCallback(async () => {
    setLoading(true);
    try {
      const [rolesRes, permsRes, scopesRes, templatesRes, exceptionsRes, depsRes] = await Promise.all([
        api.get('/roles'),
        api.get('/role-config/permissions-by-module'),
        api.get('/role-config/scopes'),
        api.get('/role-config/templates'),
        api.get('/role-config/exceptions'),
        api.get('/role-config/dependencies').catch(() => ({ data: [] })),
      ]);
      setRoles(rolesRes.data || []);
      setPermissions(permsRes.data || {});
      setScopes(scopesRes.data || []);
      setTemplates(templatesRes.data || []);
      setExceptions(exceptionsRes.data || []);
      setDeps(depsRes.data || []);
    } catch (err) {
      console.error('Failed to load configuration data', err);
      setRoles([]);
      setPermissions({});
      setScopes([]);
      setTemplates([]);
      setExceptions([]);
      setDeps([]);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => { fetchAll(); }, [fetchAll]);

  const fetchRoleDetail = useCallback(async (role) => {
    if (!role) return;
    setSelectedRole(role);
    setAssignedPerms(role.permissions?.map(p => p.name) || []);
    setRoleSummary(null);
    setAuditSettings(null);
    try {
      const [summaryRes, auditRes] = await Promise.all([
        api.get(`/role-config/roles/${role.id}/summary`),
        api.get(`/role-config/roles/${role.id}/audit`),
      ]);
      setRoleSummary(summaryRes.data || null);
      setAuditSettings(auditRes.data || null);
    } catch (err) {
      console.error('Failed to load role detail', err);
    }
  }, []);

  const moduleIcons = {
    view: Eye, create: Plus, edit: Edit3, delete: Trash2, manage: Settings, read: Eye, write: Edit3,
  };
  const moduleLabels = { read: 'Read', write: 'Write', edit: 'Edit', delete: 'Delete' };

  const getModuleIcon = (action) => {
    const Icon = moduleIcons[action] || Settings;
    return <Icon className="w-3.5 h-3.5" />;
  };

  const togglePerm = (permName, module) => {
    setAssignedPerms(prev => {
      const parts = permName.split('.');
      const action = parts[1] || '';
      const removeActions = [];

      if (action === 'delete') {
        removeActions.push(parts[0] + '.edit', parts[0] + '.create', parts[0] + '.view');
      } else if (action === 'edit') {
        removeActions.push(parts[0] + '.create', parts[0] + '.view');
      } else if (action === 'create') {
        removeActions.push(parts[0] + '.view');
      }

      if (prev.includes(permName)) {
        return prev.filter(p => p !== permName);
      } else {
        const newPerms = [...prev, permName];
        removeActions.forEach(rp => {
          if (!newPerms.includes(rp)) newPerms.push(rp);
        });
        return newPerms;
      }
    });
  };

  const isActionPermitted = (permName) => assignedPerms.includes(permName);

  const toggleException = async (ex) => {
    try {
      const { data } = await api.put(`/role-config/exceptions/${ex.id}`, {
        is_enabled: !ex.is_enabled,
      });
      setExceptions(prev => prev.map(e => e.id === ex.id ? (data.data || { ...e, is_enabled: !e.is_enabled }) : e));
    } catch (err) {
      alert('Failed to update exception');
    }
  };

  const handleSavePermissions = async () => {
    if (!selectedRole) return;
    setSaving(true);
    try {
      await api.put(`/roles/${selectedRole.id}`, { name: selectedRole.name, permissions: assignedPerms });
      const summaryRes = await api.get(`/role-config/roles/${selectedRole.id}/summary`);
      setRoleSummary(summaryRes.data || null);
      const rolesRes = await api.get('/roles');
      setRoles(rolesRes.data || []);
      const updatedRole = (rolesRes.data || []).find(r => r.id === selectedRole.id);
      if (updatedRole) setSelectedRole(updatedRole);
      alert('Permissions saved successfully');
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to save permissions');
    } finally {
      setSaving(false);
    }
  };

  const handleDiscard = () => {
    if (!selectedRole) return;
    setAssignedPerms(selectedRole.permissions?.map(p => p.name) || []);
    alert('Changes discarded');
  };

  const handleApplyTemplate = async (templateId) => {
    if (!selectedRole) return;
    try {
      const { data } = await api.post(`/role-config/roles/${selectedRole.id}/apply-template`, { template_id: templateId });
      const updated = data.data || data;
      setAssignedPerms(updated.permissions?.map(p => p.name) || []);
      alert('Template applied successfully');
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to apply template');
    }
  };

  const handleSaveAudit = async () => {
    if (!selectedRole || !auditSettings) return;
    try {
      const { data } = await api.put(`/role-config/roles/${selectedRole.id}/audit`, auditSettings);
      setAuditSettings(data.data || auditSettings);
      alert('Audit settings saved');
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to save audit settings');
    }
  };

  const moduleOrder = ['general', 'view', 'employee', 'department', 'designation', 'site', 'attendance', 'shift', 'daily_report', 'leave', 'leave_type', 'document', 'product', 'category', 'customer', 'supplier', 'inventory', 'sales', 'purchase', 'invoice', 'payment', 'payroll', 'salary', 'report'];

  const moduleLabels2 = {
    employee: 'Employees', department: 'Departments', designation: 'Designations',
    site: 'Sites', attendance: 'Attendance', shift: 'Shifts',
    daily_report: 'Daily Reports', leave: 'Leaves', leave_type: 'Leave Types',
    document: 'Documents', product: 'Products', category: 'Categories',
    customer: 'Customers', supplier: 'Suppliers', inventory: 'Inventory',
    sales: 'Sales', purchase: 'Purchases', invoice: 'Invoices',
    payment: 'Payments', payroll: 'Payroll', salary: 'Salary',
    report: 'Reports', general: 'General', view: 'View',
  };

  const sortedModules = Object.keys(permissions).sort((a, b) => {
    const ia = moduleOrder.indexOf(a);
    const ib = moduleOrder.indexOf(b);
    if (ia === -1 && ib === -1) return a.localeCompare(b);
    if (ia === -1) return 1;
    if (ib === -1) return -1;
    return ia - ib;
  });

  const getDependencyInfo = (permName) => {
    const dep = deps.find(d => d.permission === permName);
    if (dep) return dep.depends_on;
    return null;
  };

  if (loading) {
    return (
      <div className="p-6 max-w-7xl mx-auto space-y-6">
        <Skeleton className="h-8 w-64" />
        <div className="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-6">
          <Skeleton className="h-96 w-full" />
          <Skeleton className="h-96 w-full" />
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen" style={{ background: '#F5F6FA' }}>
      <div className="max-w-7xl mx-auto p-6">
        <div className="flex items-center justify-between mb-6">
          <div>
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-medium mb-2">
              <Shield className="w-3.5 h-3.5" />
              Role Configuration
            </div>
            <h1 className="text-2xl font-bold text-gray-900" style={{ fontFamily: 'Inter' }}>
              Role Configuration & Permissions
            </h1>
          </div>
          <div className="relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              type="text"
              placeholder="Quick search (Ctrl + K)"
              value={searchQuery}
              onChange={e => setSearchQuery(e.target.value)}
              className="pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm w-64 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
            />
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-4 sm:gap-6">
          {/* Left Sidebar - Role List */}
          <div className="space-y-4">
            <div className="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
              <div className="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
                <Users className="w-4 h-4 text-blue-600" />
                <h3 className="text-sm font-semibold text-gray-900">Roles</h3>
              </div>
              <div className="divide-y divide-gray-100 max-h-[600px] overflow-y-auto">
                {!Array.isArray(roles) || roles.length === 0 ? (
                  <div className="p-6 text-center text-sm text-gray-400">No roles available.</div>
                ) : (
                  roles.map(role => (
                  <button
                    key={role.id}
                    onClick={() => fetchRoleDetail(role)}
                    className={`w-full text-left px-4 py-3 transition-colors hover:bg-gray-50 ${selectedRole?.id === role.id ? 'bg-blue-50 border-l-2 border-blue-600' : ''}`}
                  >
                    <div className="flex items-center justify-between">
                      <span className={`text-sm font-medium ${selectedRole?.id === role.id ? 'text-blue-700' : 'text-gray-800'}`}>
                        {role.name}
                      </span>
                      <span className="text-[10px] font-medium px-1.5 py-0.5 rounded bg-gray-100 text-gray-500">
                        {role.permissions?.length || 0}
                      </span>
                    </div>
                    <p className="text-xs text-gray-400 mt-0.5">
                      {role.guard_name || 'web'} · {role.permissions?.length || 0} permissions
                    </p>
                  </button>
                )))}
              </div>
            </div>

            {/* Role Summary Widget */}
            {selectedRole && roleSummary && (
              <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-4 space-y-3">
                <h4 className="text-xs font-semibold text-gray-500 uppercase tracking-wider">Summary</h4>
                <div className="flex items-center justify-between">
                  <span className="text-xs text-gray-500">Total Nodes</span>
                  <span className="text-lg font-bold text-blue-600">{roleSummary.total_nodes || 0}</span>
                </div>
                <div className="flex items-center justify-between">
                  <span className="text-xs text-gray-500">Access Level</span>
                  <span className="text-xs font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700">
                    {roleSummary.access_level || 'Basic'}
                  </span>
                </div>
                <div className="flex items-center justify-between">
                  <span className="text-xs text-gray-500">Assigned Users</span>
                  <span className="text-sm font-semibold text-gray-800">{roleSummary.user_count || 0}</span>
                </div>
              </div>
            )}
          </div>

          {/* Main Workspace */}
          <div className="space-y-6">
            {!selectedRole ? (
              <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center">
                <Shield className="w-12 h-12 text-gray-300 mx-auto mb-4" />
                <h3 className="text-lg font-semibold text-gray-500">Select a Role</h3>
                <p className="text-sm text-gray-400 mt-1">Choose a role from the left panel to configure its permissions.</p>
              </div>
            ) : (
              <>
                {/* Role Header */}
                <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                  <div className="flex items-start justify-between">
                    <div>
                      <h2 className="text-xl font-bold text-gray-900">{selectedRole.name}</h2>
                      <p className="text-sm text-gray-500 mt-1">
                        Role-based access control configuration
                      </p>
                    </div>
                    <div className="flex items-center gap-2">
                      <button
                        onClick={handleSavePermissions}
                        disabled={saving}
                        className="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors"
                      >
                        {saving ? <Loader2 className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />}
                        {saving ? 'Saving...' : 'Save Changes'}
                      </button>
                      <button
                        onClick={handleDiscard}
                        className="inline-flex items-center gap-1.5 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors"
                      >
                        <RotateCcw className="w-4 h-4" />
                        Discard
                      </button>
                    </div>
                  </div>

                  {/* Template Selector */}
                  {templates.length > 0 && (
                    <div className="mt-4 pt-4 border-t border-gray-100 flex items-center gap-3">
                      <span className="text-xs font-medium text-gray-500">Apply Template:</span>
                      <select
                        onChange={e => { if (e.target.value) handleApplyTemplate(e.target.value); }}
                        className="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 outline-none"
                        defaultValue=""
                      >
                        <option value="" disabled>Select template...</option>
                        {templates.map(t => (
                          <option key={t.id} value={t.id}>{t.name}</option>
                        ))}
                      </select>
                    </div>
                  )}
                </div>

                {/* Tab Navigation */}
                <div className="flex gap-1 bg-white rounded-xl border border-gray-200 shadow-sm p-1">
                  {[
                    { key: 'permissions', label: 'Permissions', icon: Shield },
                    { key: 'exceptions', label: 'Exceptions', icon: AlertTriangle },
                    { key: 'audit', label: 'Audit', icon: FileText },
                  ].map(tab => {
                    const Icon = tab.icon;
                    return (
                      <button
                        key={tab.key}
                        onClick={() => setActiveTab(tab.key)}
                        className={`flex-1 flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium rounded-lg transition-colors ${
                          activeTab === tab.key
                            ? 'bg-blue-600 text-white shadow-sm'
                            : 'text-gray-500 hover:bg-gray-50'
                        }`}
                      >
                        <Icon className="w-4 h-4" />
                        {tab.label}
                      </button>
                    );
                  })}
                </div>

                {/* Permissions Matrix */}
                {activeTab === 'permissions' && (
                  <div className="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div className="px-5 py-4 border-b border-gray-100">
                      <h3 className="text-sm font-semibold text-gray-900">Permission Matrix</h3>
                      <p className="text-xs text-gray-400 mt-0.5">
                        Configure module-level permissions. Granting a higher permission automatically enables required lower permissions.
                      </p>
                    </div>
                    <div className="overflow-x-auto">
                      <table className="w-full text-sm">
                        <thead>
                          <tr className="border-b border-gray-100 bg-gray-50">
                            <th className="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Module</th>
                            <th className="text-center px-3 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Read</th>
                            <th className="text-center px-3 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Write</th>
                            <th className="text-center px-3 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Edit</th>
                            <th className="text-center px-3 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Delete</th>
                            <th className="text-left px-3 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Scope</th>
                          </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-50">
                          {sortedModules
                            .filter(m => !searchQuery || m.includes(searchQuery.toLowerCase()) || (moduleLabels2[m] || m).toLowerCase().includes(searchQuery.toLowerCase()))
                            .map(module => {
                              const perms = permissions[module] || [];
                              const hasView = perms.some(p => p.name.endsWith('.view') || p.name.endsWith('_edit') || p.name.endsWith('_update') || p.name.includes('view'));
                              const hasCreate = perms.some(p => p.name.endsWith('.create') || p.name.endsWith('_create'));
                              const hasEdit = perms.some(p => p.name.endsWith('.edit') || p.name.endsWith('_update'));
                              const hasDelete = perms.some(p => p.name.endsWith('.delete'));
                              const isExpanded = expandedModules[module];

                              return (
                                <tr key={module} className="hover:bg-gray-50/50">
                                  <td className="px-5 py-3">
                                    <div className="flex items-center gap-2">
                                      <button onClick={() => setExpandedModules(prev => ({ ...prev, [module]: !prev[module] }))} className="text-gray-300 hover:text-gray-500">
                                        {isExpanded ? <ChevronDown className="w-3.5 h-3.5" /> : <ChevronRight className="w-3.5 h-3.5" />}
                                      </button>
                                      <div>
                                        <span className="font-medium text-gray-800 text-sm">{moduleLabels2[module] || module.charAt(0).toUpperCase() + module.slice(1).replace(/_/g, ' ')}</span>
                                        <p className="text-[10px] text-gray-400">{perms.length} permission(s)</p>
                                      </div>
                                    </div>
                                  </td>
                                  <td className="px-3 py-3 text-center">
                                    {hasView && (
                                      <input
                                        type="checkbox"
                                        checked={perms.some(p => isActionPermitted(p.name))}
                                        onChange={() => {
                                          const viewPerm = perms.find(p => p.name.endsWith('.view') || p.name.includes('view'))?.name || perms[0]?.name;
                                          if (viewPerm) togglePerm(viewPerm, module);
                                        }}
                                        className="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                      />
                                    )}
                                  </td>
                                  <td className="px-3 py-3 text-center">
                                    {hasCreate && (
                                      <input
                                        type="checkbox"
                                        checked={perms.some(p => isActionPermitted(p.name) && (p.name.endsWith('.create') || p.name.endsWith('_create')))}
                                        onChange={() => {
                                          const createPerm = perms.find(p => p.name.endsWith('.create') || p.name.endsWith('_create'))?.name;
                                          if (createPerm) togglePerm(createPerm, module);
                                        }}
                                        className="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                      />
                                    )}
                                  </td>
                                  <td className="px-3 py-3 text-center">
                                    {hasEdit && (
                                      <input
                                        type="checkbox"
                                        checked={perms.some(p => isActionPermitted(p.name) && (p.name.endsWith('.edit') || p.name.endsWith('_update') || p.name.endsWith('_edit')))}
                                        onChange={() => {
                                          const editPerm = perms.find(p => p.name.endsWith('.edit') || p.name.endsWith('_update'))?.name;
                                          if (editPerm) togglePerm(editPerm, module);
                                        }}
                                        className="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                      />
                                    )}
                                  </td>
                                  <td className="px-3 py-3 text-center">
                                    {hasDelete && (
                                      <input
                                        type="checkbox"
                                        checked={perms.some(p => isActionPermitted(p.name) && (p.name.endsWith('.delete') || p.name.endsWith('_delete')))}
                                        onChange={() => {
                                          const deletePerm = perms.find(p => p.name.endsWith('.delete') || p.name.endsWith('_delete'))?.name;
                                          if (deletePerm) togglePerm(deletePerm, module);
                                        }}
                                        className="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                      />
                                    )}
                                  </td>
                                  <td className="px-3 py-3">
                                    <select className="text-xs border border-gray-200 rounded px-2 py-1 text-gray-600 focus:ring-1 focus:ring-blue-500 outline-none" defaultValue="organization">
                                      {scopes.map(s => (
                                        <option key={s.id} value={s.name}>{s.label}</option>
                                      ))}
                                    </select>
                                  </td>
                                </tr>
                              );
                            })}
                        </tbody>
                      </table>
                    </div>
                    {sortedModules.length === 0 && (
                      <div className="p-8 text-center text-sm text-gray-400">No permissions configured.</div>
                    )}
                  </div>
                )}

                {/* System Exceptions */}
                {activeTab === 'exceptions' && (
                  <div className="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div className="px-5 py-4 border-b border-gray-100">
                      <h3 className="text-sm font-semibold text-gray-900">System Exceptions</h3>
                      <p className="text-xs text-gray-400 mt-0.5">
                        Configure exception rules that allow specific roles to bypass standard controls.
                      </p>
                    </div>
                    <div className="divide-y divide-gray-100">
                      {exceptions.length === 0 && (
                        <div className="p-8 text-center text-sm text-gray-400">No exception rules configured.</div>
                      )}
                      {exceptions.map(ex => (
                        <div key={ex.id} className="px-5 py-4">
                          <div className="flex items-center justify-between">
                            <div className="flex-1">
                              <div className="flex items-center gap-2">
                                <span className="text-sm font-medium text-gray-800">{ex.name}</span>
                                <span className="text-[10px] font-medium px-1.5 py-0.5 rounded bg-gray-100 text-gray-500">{ex.category}</span>
                              </div>
                              <p className="text-xs text-gray-400 mt-0.5">{ex.description}</p>
                            </div>
                            <div className="flex items-center gap-3">
                              <label className="relative inline-flex items-center cursor-pointer">
                                <input
                                  type="checkbox"
                                  checked={ex.is_enabled}
                                  onChange={() => toggleException(ex)}
                                  className="sr-only peer"
                                />
                                <div className="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600" />
                              </label>
                              <span className={`text-xs font-medium ${ex.is_enabled ? 'text-green-600' : 'text-gray-400'}`}>
                                {ex.is_enabled ? 'Enabled' : 'Disabled'}
                              </span>
                            </div>
                          </div>
                          {ex.is_enabled && (
                            <div className="mt-3 pl-4 border-l-2 border-blue-100 space-y-2">
                              <label className="flex items-center gap-2 text-xs text-gray-600">
                                <input
                                  type="checkbox"
                                  checked={ex.requires_approval}
                                  onChange={async () => {
                                    try {
                                      await api.put(`/role-config/exceptions/${ex.id}`, { requires_approval: !ex.requires_approval });
                                      setExceptions(prev => prev.map(e => e.id === ex.id ? { ...e, requires_approval: !e.requires_approval } : e));
                                    } catch (err) { alert('Update failed'); }
                                  }}
                                  className="w-3.5 h-3.5 text-blue-600 border-gray-300 rounded"
                                />
                                Requires approval
                              </label>
                            </div>
                          )}
                        </div>
                      ))}
                    </div>
                  </div>
                )}

                {/* Audit Configuration */}
                {activeTab === 'audit' && auditSettings && (
                  <div className="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div className="px-5 py-4 border-b border-gray-100">
                      <h3 className="text-sm font-semibold text-gray-900">Audit Configuration</h3>
                      <p className="text-xs text-gray-400 mt-0.5">
                        Configure audit behavior for the {selectedRole.name} role.
                      </p>
                    </div>
                    <div className="p-5 space-y-5">
                      <div>
                        <label className="block text-xs font-medium text-gray-600 mb-1.5">Logging Verbosity</label>
                        <select
                          value={auditSettings.logging_verbosity}
                          onChange={e => setAuditSettings(prev => ({ ...prev, logging_verbosity: e.target.value }))}
                          className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                        >
                          {['low', 'medium', 'high', 'critical'].map(v => (
                            <option key={v} value={v}>{v.charAt(0).toUpperCase() + v.slice(1)}</option>
                          ))}
                        </select>
                      </div>

                      <div>
                        <label className="block text-xs font-medium text-gray-600 mb-1.5">Audit Retention Period (days)</label>
                        <input
                          type="number"
                          value={auditSettings.retention_days}
                          onChange={e => setAuditSettings(prev => ({ ...prev, retention_days: parseInt(e.target.value) || 90 }))}
                          className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                          min={1}
                          max={3650}
                        />
                      </div>

                      <div className="space-y-3">
                        {[
                          { key: 'reason_required', label: 'Reason Required', desc: 'Require a reason for permission changes' },
                          { key: 'change_tracking_enabled', label: 'Change Tracking Enabled', desc: 'Track all permission modifications' },
                          { key: 'snapshot_creation_enabled', label: 'Snapshot Creation Enabled', desc: 'Create snapshots before permission changes' },
                        ].map(item => (
                          <label key={item.key} className="flex items-start gap-3 cursor-pointer">
                            <input
                              type="checkbox"
                              checked={auditSettings[item.key]}
                              onChange={e => setAuditSettings(prev => ({ ...prev, [item.key]: e.target.checked }))}
                              className="mt-0.5 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            />
                            <div>
                              <span className="text-sm font-medium text-gray-700">{item.label}</span>
                              <p className="text-xs text-gray-400">{item.desc}</p>
                            </div>
                          </label>
                        ))}
                      </div>

                      <div className="flex justify-end">
                        <button
                          onClick={handleSaveAudit}
                          className="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                        >
                          <Save className="w-4 h-4" />
                          Save Audit Settings
                        </button>
                      </div>
                    </div>
                  </div>
                )}
              </>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
