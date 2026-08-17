import { useState, useEffect, useCallback, useMemo, Fragment } from 'react';
import api from '../../services/api';
import { useAccessStore } from '../../store/accessStore';
import {
  Shield, Users, X, GripVertical, Search, CheckCircle2,
} from 'lucide-react';

const ACTION_ORDER = ['view', 'edit', 'remove', 'add'];
const ACTION_META = {
  view: { label: 'View', hint: "Can see this module's data" },
  edit: { label: 'Edit', hint: 'Can change existing records' },
  remove: { label: 'Remove', hint: 'Can delete records' },
  add: { label: 'Add', hint: 'Can create new records' },
};

const MODULE_ORDER = ['dashboard', 'employee', 'department', 'designation', 'site', 'building', 'attendance', 'shift', 'daily-report', 'leave', 'leave-type', 'document', 'product', 'category', 'customer', 'supplier', 'inventory', 'sales-order', 'purchase-order', 'invoice', 'payment', 'payroll', 'payslip', 'salary-structure', 'report', 'permission', 'role', 'user'];

const MODULE_LABELS = {
  dashboard: 'Dashboard', employee: 'Employees', department: 'Departments', designation: 'Designations',
  site: 'Sites', building: 'Buildings', attendance: 'Attendance', shift: 'Shifts',
  'daily-report': 'Daily Reports', leave: 'Leaves', 'leave-type': 'Leave Types',
  document: 'Documents', product: 'Products', category: 'Categories',
  customer: 'Customers', supplier: 'Suppliers', inventory: 'Inventory',
  'sales-order': 'Sales Orders', 'purchase-order': 'Purchase Orders', invoice: 'Invoices',
  payment: 'Payments', payroll: 'Payroll', payslip: 'Payslips',
  'salary-structure': 'Salary Structures', report: 'Reports',
  permission: 'Permissions', role: 'Roles', user: 'Users',
};

const moduleLabel = (module) =>
  MODULE_LABELS[module] || module.charAt(0).toUpperCase() + module.slice(1).replace(/[-_]/g, ' ');

// Maps legacy permission names (no dots) to their real module so chips collapse to one per module.
const LEGACY_MODULE_MAP = {
  employees: 'employee', products: 'product', customers: 'customer', suppliers: 'supplier',
  sites: 'site', categories: 'category', attendance: 'attendance', inventory: 'inventory',
  invoices: 'invoice', payments: 'payment', payroll: 'payroll', leaves: 'leave',
  sales: 'sales-order', salesorders: 'sales-order', purchases: 'purchase-order',
  purchaseorders: 'purchase-order', reports: 'report', warehouses: 'warehouse',
  dashboard: 'dashboard', roles: 'role', permissions: 'permission', users: 'user',
};

const DOT_ACTION_MAP = { view: 'view', create: 'add', edit: 'edit', delete: 'remove' };

const parsePermName = (name) => {
  if (name.includes('.')) {
    const [module, ...rest] = name.split('.');
    const action = DOT_ACTION_MAP[rest.join('.')];
    return action ? { module, action } : null;
  }
  const m = name.match(/^(view|create|edit|update|delete|manage|export|schedule)[ _](.+)$/i);
  if (m) {
    const raw = m[1].toLowerCase();
    const action = raw === 'update' ? 'edit' : raw;
    const module = LEGACY_MODULE_MAP[m[2].toLowerCase().replace(/[\s_]/g, '')];
    if (module && ACTION_META[action]) return { module, action };
  }
  return null;
};

const sortModules = (modules) => [...modules].sort((a, b) => {
  const ia = MODULE_ORDER.indexOf(a);
  const ib = MODULE_ORDER.indexOf(b);
  if (ia === -1 && ib === -1) return a.localeCompare(b);
  if (ia === -1) return 1;
  if (ib === -1) return -1;
  return ia - ib;
});

const getErrorMessage = (error, fallback) =>
  error?.response?.data?.message || error?.message || fallback;

function GrantModal({ module, employeeName, actions, initialChecked, onSave, onCancel }) {
  const [checked, setChecked] = useState(initialChecked);

  const toggle = (action) =>
    setChecked(prev => (prev.includes(action) ? prev.filter(a => a !== action) : [...prev, action]));

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" onClick={onCancel}>
      <div className="bg-white rounded-xl shadow-xl w-full max-w-sm" onClick={e => e.stopPropagation()}>
        <div className="px-5 py-4 border-b border-gray-100 flex items-start justify-between">
          <div>
            <h3 className="text-lg font-bold text-gray-900">{moduleLabel(module)}</h3>
            <p className="text-sm text-gray-500 mt-0.5">
              Which permissions should {employeeName} have?
            </p>
          </div>
          <button type="button" onClick={onCancel} className="text-gray-400 hover:text-gray-600">
            <X className="w-5 h-5" />
          </button>
        </div>

        <div className="px-5 py-4 space-y-3">
          {actions.length === 0 ? (
            <p className="text-sm text-gray-500">No permissions are available for this module.</p>
          ) : (
            actions.map(action => (
              <label key={action} className="flex items-center gap-3 cursor-pointer rounded-lg border border-gray-200 px-4 py-3 hover:border-blue-300 hover:bg-blue-50 transition-colors">
                <input
                  type="checkbox"
                  checked={checked.includes(action)}
                  onChange={() => toggle(action)}
                  className="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                />
                <div>
                  <span className="text-sm font-medium text-gray-800">{ACTION_META[action].label}</span>
                  <p className="text-xs text-gray-400">{ACTION_META[action].hint}</p>
                </div>
              </label>
            ))
          )}
        </div>

        <div className="px-5 py-4 border-t border-gray-100 flex justify-end gap-2">
          <button
            type="button"
            onClick={onCancel}
            className="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors"
          >
            Cancel
          </button>
          <button
            type="button"
            onClick={onSave}
            className="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
          >
            Grant
          </button>
        </div>
      </div>
    </div>
  );
}

export default function AccessControl() {
  const [users, setUsers] = useState([]);
  const [permissions, setPermissions] = useState({});
  const [roles, setRoles] = useState([]);
  const [selectedUserId, setSelectedUserId] = useState(null);
  const [loading, setLoading] = useState(true);
  const [dialogModule, setDialogModule] = useState(null);
  const [dialogChecks, setDialogChecks] = useState([]);
  const [dragModule, setDragModule] = useState(null);
  const [employeeSearch, setEmployeeSearch] = useState('');
  const [notice, setNotice] = useState(null);
  const [busy, setBusy] = useState(false);

  const {
    userPermissions,
    userRoles,
    fetchUserPermissions,
    fetchUserRoles,
    assignPermissions,
    removePermission,
  } = useAccessStore();

  const fetchAll = useCallback(async () => {
    setLoading(true);
    try {
      const [usersRes, permsRes] = await Promise.all([
        api.get('/users'),
        api.get('/role-config/permissions-by-module'),
      ]);
      setUsers(usersRes.data || []);
      setPermissions(permsRes.data || {});
    } catch (err) {
      setNotice({ type: 'error', text: getErrorMessage(err, 'Could not load access data.') });
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => { fetchAll(); }, [fetchAll]);

  useEffect(() => {
    api.get('/roles')
      .then(res => setRoles(res.data || []))
      .catch(() => setRoles([]));
  }, []);

  const selectedUser = useMemo(
    () => users.find(u => u.id === selectedUserId) || null,
    [users, selectedUserId],
  );

  const filteredUsers = useMemo(() => {
    const term = employeeSearch.trim().toLowerCase();
    if (!term) return users;
    return users.filter(u =>
      `${u.name} ${u.email} ${u.employee?.emp_id || ''} ${u.employee?.department || ''} ${u.employee?.designation || ''}`
        .toLowerCase().includes(term),
    );
  }, [users, employeeSearch]);

  const allPerms = useMemo(() => Object.values(permissions || {}).flat(), [permissions]);

  const modules = useMemo(
    () => sortModules(
      Object.keys(permissions || {}).filter(mod => (permissions[mod] || []).some(p => p.name.includes('.'))),
    ),
    [permissions],
  );

  const moduleActions = useMemo(() => {
    const map = {};
    for (const mod of modules) map[mod] = { view: [], add: [], edit: [], remove: [] };
    for (const p of allPerms) {
      const parsed = parsePermName(p.name);
      if (parsed && map[parsed.module] && map[parsed.module][parsed.action]) {
        map[parsed.module][parsed.action].push(p);
      }
    }
    return map;
  }, [allPerms, modules]);

  const userPermNames = useMemo(() => userPermissions.map(p => p.name), [userPermissions]);

  const rolePermNames = useMemo(() => {
    const names = new Set();
    for (const userRole of userRoles) {
      const role = roles.find(r => String(r.id) === String(userRole.id));
      for (const perm of role?.permissions || []) names.add(perm.name);
    }
    return names;
  }, [roles, userRoles]);

  const roleNamesForModule = (module) => {
    const entries = moduleActions[module] || {};
    const permIds = new Set(Object.values(entries).flat().map(p => p.id));
    const roleNames = new Set();
    for (const userRole of userRoles) {
      const role = roles.find(r => String(r.id) === String(userRole.id));
      if ((role?.permissions || []).some(p => permIds.has(p.id))) roleNames.add(role.name);
    }
    return [...roleNames];
  };

  const directActionsForModule = (module) => {
    const entries = moduleActions[module] || {};
    return ACTION_ORDER.filter(action =>
      entries[action] && entries[action].some(p => userPermNames.includes(p.name)),
    );
  };

  const roleActionsForModule = (module) => {
    const entries = moduleActions[module] || {};
    return ACTION_ORDER.filter(action =>
      entries[action] && entries[action].some(p => rolePermNames.has(p.name)),
    );
  };

  const hasAccess = (module) =>
    directActionsForModule(module).length > 0 || roleActionsForModule(module).length > 0;

  const grantedModules = useMemo(
    () => modules.filter(m => hasAccess(m)),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [modules, userPermissions, userRoles],
  );

  const selectUser = async (user) => {
    setNotice(null);
    setSelectedUserId(user.id);
    setBusy(true);
    try {
      await Promise.all([
        fetchUserPermissions(user.id),
        fetchUserRoles(user.id),
      ]);
    } catch (err) {
      setNotice({ type: 'error', text: getErrorMessage(err, "Could not load this person's access.") });
    } finally {
      setBusy(false);
    }
  };

  const openDialog = (module) => {
    if (!moduleActions[module]) return;
    setDialogModule(module);
    setDialogChecks(directActionsForModule(module));
  };

  const saveDialog = async () => {
    if (!selectedUser || !dialogModule) return;
    const entries = moduleActions[dialogModule] || {};
    const current = directActionsForModule(dialogModule);
    const toAdd = dialogChecks.filter(a => !current.includes(a))
      .map(a => (entries[a] || []).find(p => p.name.includes('.')) || (entries[a] || [])[0])
      .filter(Boolean);
    const toRemove = current.filter(a => !dialogChecks.includes(a))
      .flatMap(a => (entries[a] || []).filter(mp => userPermissions.some(p => p.id === mp.id)));

    setBusy(true);
    try {
      if (toAdd.length > 0) await assignPermissions(selectedUserId, toAdd.map(p => p.name));
      for (const perm of toRemove) await removePermission(selectedUserId, perm.id);
      await fetchUserPermissions(selectedUserId);
      setNotice({ type: 'success', text: `${moduleLabel(dialogModule)} access saved for ${selectedUser.name}.` });
    } catch (err) {
      setNotice({ type: 'error', text: getErrorMessage(err, 'Could not save access changes.') });
    } finally {
      setBusy(false);
      setDialogModule(null);
      setDialogChecks([]);
    }
  };

  const removeModule = async (module) => {
    if (!selectedUser) return;
    const entries = moduleActions[module] || {};
    const ids = new Set(Object.values(entries).flat().map(p => p.id));
    const toRemove = userPermissions.filter(p => ids.has(p.id));
    if (toRemove.length === 0) return;
    setBusy(true);
    try {
      for (const perm of toRemove) await removePermission(selectedUserId, perm.id);
      await fetchUserPermissions(selectedUserId);
      setNotice({ type: 'success', text: `Removed ${moduleLabel(module)} access from ${selectedUser.name}.` });
    } catch (err) {
      setNotice({ type: 'error', text: getErrorMessage(err, 'Could not remove this access.') });
    } finally {
      setBusy(false);
    }
  };

  const handleDragStart = (e, module) => {
    e.dataTransfer.setData('text/plain', module);
    e.dataTransfer.effectAllowed = 'copy';
    setDragModule(module);
  };

  const handleDrop = (e) => {
    e.preventDefault();
    const module = e.dataTransfer.getData('text/plain');
    setDragModule(null);
    if (module && moduleActions[module]) openDialog(module);
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-muted">
        <div className="max-w-6xl mx-auto p-6 space-y-6">
          <div className="animate-pulse bg-gray-200 rounded h-8 w-64" />
          <div className="grid grid-cols-1 lg:grid-cols-[300px_1fr] gap-6">
            <div className="animate-pulse bg-white rounded-xl border border-gray-200 shadow-sm h-96" />
            <div className="animate-pulse bg-white rounded-xl border border-gray-200 shadow-sm h-96" />
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-muted">
      <div className="max-w-6xl mx-auto p-6 space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-medium mb-2">
              <Shield className="w-3.5 h-3.5" />
              Access Control
            </div>
            <h1 className="text-2xl font-bold text-gray-900" style={{ fontFamily: 'Inter' }}>
              Employee Access
            </h1>
          </div>
        </div>

        {/* Beginner guide */}
        <div className="bg-white rounded-xl border border-gray-200 shadow-sm px-5 py-4">
          <p className="text-sm text-gray-600">
            Pick an <strong>employee</strong>, then <strong>drag</strong> a module (like{' '}
            <strong>Products</strong>) into the box. A window will ask which permissions (View,
            Edit, Remove, Add) that person may have. Changes save immediately.
          </p>
        </div>

        {notice && (
          <div
            className={`px-4 py-3 rounded-lg border text-sm flex items-center justify-between ${
              notice.type === 'success'
                ? 'bg-green-50 border-green-200 text-green-800'
                : 'bg-red-50 border-red-200 text-red-800'
            }`}
          >
            <span>{notice.text}</span>
            <button type="button" onClick={() => setNotice(null)} className="ml-4 text-xs underline hover:opacity-70">
              Dismiss
            </button>
          </div>
        )}

        <div className="grid grid-cols-1 lg:grid-cols-[300px_1fr] gap-6">
          {/* Employees sidebar */}
          <div className="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden h-[40rem] flex flex-col">
            <div className="px-4 py-3 border-b border-gray-100">
              <div className="flex items-center gap-2">
                <Users className="w-4 h-4 text-blue-600" />
                <h3 className="text-sm font-semibold text-gray-900">Employees</h3>
              </div>
              <div className="relative mt-3">
                <Search className="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" />
                <input
                  type="text"
                  value={employeeSearch}
                  onChange={e => setEmployeeSearch(e.target.value)}
                  placeholder="Search name, ID, department..."
                  className="pl-8 pr-3 py-1.5 w-full border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 outline-none"
                />
              </div>
            </div>
            <div className="flex-1 overflow-y-auto divide-y divide-gray-50">
              {filteredUsers.length === 0 ? (
                <div className="p-6 text-center text-sm text-gray-400">
                  {employeeSearch ? 'No one matches your search.' : 'No employees found yet.'}
                </div>
              ) : (
                filteredUsers.map(user => (
                  <button
                    key={user.id}
                    type="button"
                    onClick={() => selectUser(user)}
                    className={`w-full text-left px-4 py-3 transition-colors hover:bg-gray-50 ${
                      selectedUserId === user.id ? 'bg-blue-50 border-l-2 border-blue-600' : ''
                    }`}
                  >
                    <div className="flex items-center justify-between gap-2">
                      <span className={`text-sm font-medium truncate ${selectedUserId === user.id ? 'text-blue-700' : 'text-gray-800'}`}>
                        {user.name}
                      </span>
                      <span className="shrink-0 text-[10px] font-medium px-1.5 py-0.5 rounded bg-gray-100 text-gray-500">
                        {user.permissions_count || 0}
                      </span>
                    </div>
                    <p className="text-xs text-gray-400 truncate">{user.email}</p>
                    <p className="text-[11px] text-gray-400 mt-0.5">
                      {user.employee
                        ? [
                            user.employee.designation,
                            user.employee.department,
                            user.employee.emp_id,
                          ].filter(Boolean).join(' · ') || 'No designation'
                        : 'No employee profile'}
                    </p>
                  </button>
                ))
              )}
            </div>
          </div>

          {/* Main workspace */}
          <div className="space-y-6">
            {!selectedUser ? (
              <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center">
                <Users className="w-12 h-12 text-gray-300 mx-auto mb-4" />
                <h3 className="text-lg font-semibold text-gray-500">Select an Employee</h3>
                <p className="text-sm text-gray-400 mt-1">
                  Pick a person from the left, then drag a module into the box.
                </p>
              </div>
            ) : (
              <>
                {/* Employee header */}
                <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                  <div className="flex items-center gap-4">
                    <div className="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center text-lg font-bold">
                      {selectedUser.name.charAt(0).toUpperCase()}
                    </div>
                    <div>
                      <h2 className="text-xl font-bold text-gray-900">{selectedUser.name}</h2>
                      <p className="text-sm text-gray-500">{selectedUser.email}</p>
                      {selectedUser.employee && (
                        <p className="text-xs text-gray-400 mt-0.5">
                          {[selectedUser.employee.designation, selectedUser.employee.department, selectedUser.employee.emp_id]
                            .filter(Boolean).join(' · ')}
                        </p>
                      )}
                    </div>
                  </div>
                </div>

                {/* Drag & drop */}
                <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-5">
                  <div>
                    <h3 className="text-sm font-semibold text-gray-900 mb-3">Modules</h3>
                    <div className="flex flex-wrap gap-2">
                      {modules.map(module => (
                        <div
                          key={module}
                          draggable
                          onDragStart={e => handleDragStart(e, module)}
                          onDragEnd={() => setDragModule(null)}
                          title={`Drag ${moduleLabel(module)} into the box`}
                          className={`inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm cursor-grab active:cursor-grabbing select-none border transition-colors ${
                            hasAccess(module)
                              ? 'bg-blue-600 text-white border-blue-600'
                              : 'bg-gray-50 text-gray-700 border-gray-200 hover:border-blue-300 hover:bg-blue-50'
                          }`}
                        >
                          <GripVertical className="w-3.5 h-3.5 opacity-60" />
                          {moduleLabel(module)}
                          {hasAccess(module) && <CheckCircle2 className="w-3.5 h-3.5" />}
                        </div>
                      ))}
                    </div>
                  </div>

                  <div
                    onDragOver={e => { e.preventDefault(); }}
                    onDrop={handleDrop}
                    className={`rounded-xl border-2 border-dashed p-8 text-center transition-colors ${
                      dragModule
                        ? 'border-blue-500 bg-blue-50'
                        : 'border-gray-300 bg-gray-50 hover:border-blue-300'
                    }`}
                  >
                    <p className="text-sm font-medium text-gray-600">
                      {dragModule
                        ? `Drop "${moduleLabel(dragModule)}" to choose its permissions`
                        : `Drop a module here to give ${selectedUser.name} access`}
                    </p>
                    <p className="text-xs text-gray-400 mt-1">
                      The window will ask which permissions to grant.
                    </p>
                  </div>

                  {/* Already granted */}
                  {grantedModules.length > 0 ? (
                    <div>
                      <h4 className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                        Granted to {selectedUser.name}
                      </h4>
                      <div className="flex flex-wrap gap-2">
                        {grantedModules.map(module => {
                          const direct = directActionsForModule(module);
                          const role = roleActionsForModule(module);
                          return (
                            <Fragment key={module}>
                              {direct.length > 0 && (
                                <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm bg-blue-50 text-blue-800 border border-blue-200">
                                  {moduleLabel(module)}
                                  <span className="text-[11px] text-blue-500">
                                    {direct.map(a => ACTION_META[a].label).join(', ')}
                                  </span>
                                  <button
                                    type="button"
                                    disabled={busy}
                                    onClick={() => removeModule(module)}
                                    title={`Remove all ${moduleLabel(module)} access`}
                                    className="text-blue-400 hover:text-red-500 disabled:opacity-50"
                                  >
                                    <X className="w-4 h-4" />
                                  </button>
                                </span>
                              )}
                              {role.length > 0 && (
                                <span
                                  title={`Granted via role${roleNamesForModule(module).length > 0 ? `: ${roleNamesForModule(module).join(', ')}` : ''} — inherited from role, not removable here`}
                                  className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm bg-amber-50 text-amber-800 border border-amber-200"
                                >
                                  {moduleLabel(module)}
                                  <span className="text-[10px] font-medium uppercase text-amber-600">
                                    via role
                                  </span>
                                  <span className="text-[11px] text-amber-500">
                                    {role.map(a => ACTION_META[a].label).join(', ')}
                                  </span>
                                </span>
                              )}
                            </Fragment>
                          );
                        })}
                      </div>
                    </div>
                  ) : (
                    <p className="text-sm text-gray-400 text-center py-2">
                      No access granted yet — drag a module from above into the box.
                    </p>
                  )}
                </div>
              </>
            )}
          </div>
        </div>
      </div>

      {dialogModule && selectedUser && (
        <GrantModal
          module={dialogModule}
          employeeName={selectedUser.name}
          actions={moduleActions[dialogModule]
            ? ACTION_ORDER.filter(a => (moduleActions[dialogModule][a] || []).length > 0)
            : []}
          initialChecked={dialogChecks}
          onSave={saveDialog}
          onCancel={() => { setDialogModule(null); setDialogChecks([]); }}
        />
      )}
    </div>
  );
}
