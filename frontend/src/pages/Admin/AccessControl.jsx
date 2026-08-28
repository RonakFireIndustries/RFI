import { useState, useEffect, useCallback, useMemo, Fragment } from 'react';
import {
  Shield, Users, X, Search, CheckCircle2, Star, GripVertical,
} from 'lucide-react';
import { accessControlService } from '../../services/accessControlService';

/*
 * AccessControl (Employee Access)
 *
 * Config-driven drag & drop permission manager.
 *
 *  - The module / action catalog is fetched from the backend
 *    (GET /api/v1/access-control/definitions) — the single source of truth.
 *  - Pick an employee, drag a module into the drop zone, choose the actions
 *    (View / Add / Edit / Remove / ...), and the change saves immediately.
 *  - Super admins are shown as such and are not editable.
 *
 * Gating: the whole page is restricted to super admins server-side; the
 * sidebar only reveals it to super admins.
 */

const getErrorMessage = (error, fallback) =>
  error?.response?.data?.message || error?.message || fallback;

const stringPerms = (array) =>
  (array || []).map((p) => (typeof p === 'string' ? p : p?.name || p?.permission));

function GrantModal({ module, employeeName, options, initialChecked, onSave, onCancel }) {
  const [checked, setChecked] = useState(initialChecked);

  const toggle = (key) =>
    setChecked((prev) => (prev.includes(key) ? prev.filter((k) => k !== key) : [...prev, key]));

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" onClick={onCancel}>
      <div className="bg-white rounded-xl shadow-xl w-full max-w-sm" onClick={(e) => e.stopPropagation()}>
        <div className="px-5 py-4 border-b border-gray-100 flex items-start justify-between">
          <div>
            <h3 className="text-lg font-bold text-gray-900">{module.label}</h3>
            <p className="text-sm text-gray-500 mt-0.5">
              Which permissions should {employeeName} have?
            </p>
          </div>
          <button type="button" onClick={onCancel} className="text-gray-400 hover:text-gray-600">
            <X className="w-5 h-5" />
          </button>
        </div>

        <div className="px-5 py-4 space-y-3">
          {options.length === 0 ? (
            <p className="text-sm text-gray-500">No permissions are available for this module.</p>
          ) : (
            options.map((opt) => (
              <label
                key={opt.permission}
                className="flex items-center gap-3 cursor-pointer rounded-lg border border-gray-200 px-4 py-3 hover:border-blue-300 hover:bg-blue-50 transition-colors"
              >
                <input
                  type="checkbox"
                  checked={checked.includes(opt.permission)}
                  onChange={() => toggle(opt.permission)}
                  className="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                />
                <div>
                  <span className="text-sm font-medium text-gray-800">{opt.label}</span>
                  <p className="text-xs text-gray-400 mt-0.5">{opt.permission}</p>
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
            onClick={() => onSave(checked)}
            className="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
          >
            Save
          </button>
        </div>
      </div>
    </div>
  );
}

export default function AccessControl() {
  const [categories, setCategories] = useState([]);
  const [users, setUsers] = useState([]);
  const [selectedUserId, setSelectedUserId] = useState(null);
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);
  const [dialogModule, setDialogModule] = useState(null);
  const [dragModule, setDragModule] = useState(null);
  const [employeeSearch, setEmployeeSearch] = useState('');
  const [notice, setNotice] = useState(null);

  const fetchAll = useCallback(async () => {
    setLoading(true);
    try {
      const [defs, usersRes] = await Promise.all([
        accessControlService.definitions(),
        accessControlService.users(),
      ]);
      setCategories(defs.data?.categories || []);
      setUsers(usersRes.data || []);
    } catch (err) {
      setNotice({ type: 'error', text: getErrorMessage(err, 'Could not load access data.') });
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => { fetchAll(); }, [fetchAll]);

  // Flatten modules for quick lookups
  const moduleLookup = useMemo(() => {
    const map = {};
    for (const cat of categories) {
      for (const mod of cat.modules) map[mod.module] = mod;
    }
    return map;
  }, [categories]);

  const selectedUser = useMemo(
    () => users.find((u) => u.id === selectedUserId) || null,
    [users, selectedUserId],
  );

  const filteredUsers = useMemo(() => {
    const term = employeeSearch.trim().toLowerCase();
    if (!term) return users;
    return users.filter((u) =>
      `${u.name} ${u.email} ${u.employee?.emp_id || ''} ${u.employee?.department || ''} ${u.employee?.designation || ''}`
        .toLowerCase()
        .includes(term),
    );
  }, [users, employeeSearch]);

  const refetchUser = useCallback(async (userId) => {
    try {
      const res = await accessControlService.userPermissions(userId);
      const direct = stringPerms(res.data?.direct);
      setUsers((prev) =>
        prev.map((u) => (u.id === userId ? { ...u, permissions: direct } : u)),
      );
      return direct;
    } catch (err) {
      setNotice({ type: 'error', text: getErrorMessage(err, 'Could not refresh permissions.') });
      return [];
    }
  }, []);

  // Which actions this employee currently has for a given module
  const actionsFor = (user, moduleKey) => {
    const userPerms = user ? stringPerms(user.permissions) : [];
    const mod = moduleLookup[moduleKey];
    if (!mod) return [];
    return mod.actions.filter((a) => userPerms.includes(a.permission));
  };

  const openDialog = (moduleKey) => {
    const mod = moduleLookup[moduleKey];
    if (!mod || !selectedUser) return;
    setDialogModule(moduleKey);
  };

  const saveDialog = async (selectedPerms) => {
    if (!selectedUser || !dialogModule) return;
    const mod = moduleLookup[dialogModule];
    if (!mod) return;

    setBusy(true);
    try {
      const current = stringPerms(selectedUser.permissions);
      const modulePerms = mod.actions.map((a) => a.permission);

      // Full desired set = current grants, minus everything from this module,
      // plus the newly checked module permissions.
      const desired = [
        ...current.filter((p) => !modulePerms.includes(p)),
        ...selectedPerms,
      ];

      await accessControlService.updatePermissions(selectedUserId, Array.from(new Set(desired)));
      await refetchUser(selectedUserId);
      setNotice({ type: 'success', text: `${mod.label} access saved for ${selectedUser.name}.` });
    } catch (err) {
      setNotice({ type: 'error', text: getErrorMessage(err, 'Could not save access changes.') });
    } finally {
      setBusy(false);
      setDialogModule(null);
    }
  };

  /** Remove ALL direct grants for a module from the selected user. */
  const removeModule = async (moduleKey) => {
    if (!selectedUser) return;
    const mod = moduleLookup[moduleKey];
    if (!mod) return;
    setBusy(true);
    try {
      const current = actionsFor(selectedUser, moduleKey).map((a) => a.permission);
      const remaining = stringPerms(selectedUser.permissions).filter((p) => !current.includes(p));
      await accessControlService.updatePermissions(selectedUserId, remaining);
      await refetchUser(selectedUserId);
      setNotice({ type: 'success', text: `Removed ${mod.label} access from ${selectedUser.name}.` });
    } catch (err) {
      setNotice({ type: 'error', text: getErrorMessage(err, 'Could not remove this access.') });
    } finally {
      setBusy(false);
    }
  };

  const handleDragStart = (e, moduleKey) => {
    e.dataTransfer.setData('text/plain', moduleKey);
    e.dataTransfer.effectAllowed = 'copy';
    setDragModule(moduleKey);
  };

  const handleDrop = (e) => {
    e.preventDefault();
    const moduleKey = e.dataTransfer.getData('text/plain');
    setDragModule(null);
    if (moduleKey && moduleLookup[moduleKey] && selectedUser) {
      openDialog(moduleKey);
    }
  };

  const dialog = dialogModule ? moduleLookup[dialogModule] : null;

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
                  onChange={(e) => setEmployeeSearch(e.target.value)}
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
                filteredUsers.map((user) => {
                  const permCount = stringPerms(user.permissions).length;
                  return (
                    <button
                      key={user.id}
                      type="button"
                      onClick={() => setSelectedUserId(user.id)}
                      className={`w-full text-left px-4 py-3 transition-colors hover:bg-gray-50 ${
                        selectedUserId === user.id ? 'bg-blue-50 border-l-2 border-blue-600' : ''
                      }`}
                    >
                      <div className="flex items-center justify-between gap-2">
                        <span className={`text-sm font-medium truncate ${selectedUserId === user.id ? 'text-blue-700' : 'text-gray-800'}`}>
                          {user.name}
                        </span>
                        <span className="shrink-0 text-[10px] font-medium px-1.5 py-0.5 rounded bg-gray-100 text-gray-500">
                          {permCount}
                        </span>
                      </div>
                      <p className="text-xs text-gray-400 truncate">{user.email}</p>
                      <p className="text-[11px] text-gray-400 mt-0.5">
                        {user.employee
                          ? [user.employee.designation, user.employee.department, user.employee.emp_id]
                              .filter(Boolean)
                              .join(' · ') || 'No designation'
                          : 'No employee profile'}
                      </p>
                    </button>
                  );
                })
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
            ) : selectedUser.is_super_admin ? (
              <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center">
                <Star className="w-12 h-12 text-amber-400 mx-auto mb-4" />
                <h3 className="text-lg font-semibold text-gray-700">Super Admin</h3>
                <p className="text-sm text-gray-400 mt-1">
                  {selectedUser.name} has full access to every module and cannot be edited here.
                </p>
              </div>
            ) : (
              <>
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
                            .filter(Boolean)
                            .join(' · ')}
                        </p>
                      )}
                    </div>
                  </div>
                </div>

                <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-5">
                  <div>
                    <h3 className="text-sm font-semibold text-gray-900 mb-3">Modules</h3>
                    {categories.map((cat) => (
                      <div key={cat.category} className="mb-4">
                        <div className="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">
                          {cat.category}
                        </div>
                        <div className="flex flex-wrap gap-2">
                          {cat.modules.map((mod) => {
                            const granted = actionsFor(selectedUser, mod.module).length > 0;
                            return (
                              <div
                                key={mod.module}
                                draggable
                                onDragStart={(e) => handleDragStart(e, mod.module)}
                                onDragEnd={() => setDragModule(null)}
                                title={`Drag ${mod.label} into the box`}
                                className={`inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm cursor-grab active:cursor-grabbing select-none border transition-colors ${
                                  granted
                                    ? 'bg-blue-600 text-white border-blue-600'
                                    : 'bg-gray-50 text-gray-700 border-gray-200 hover:border-blue-300 hover:bg-blue-50'
                                }`}
                              >
                                <GripVertical className="w-3.5 h-3.5 opacity-60" />
                                {mod.label}
                                {granted && <CheckCircle2 className="w-3.5 h-3.5" />}
                              </div>
                            );
                          })}
                        </div>
                      </div>
                    ))}
                  </div>

                  <div
                    onDragOver={(e) => e.preventDefault()}
                    onDrop={handleDrop}
                    className={`rounded-xl border-2 border-dashed p-8 text-center transition-colors ${
                      dragModule
                        ? 'border-blue-500 bg-blue-50'
                        : 'border-gray-300 bg-gray-50 hover:border-blue-300'
                    }`}
                  >
                    <p className="text-sm font-medium text-gray-600">
                      {dragModule
                        ? `Drop "${moduleLookup[dragModule]?.label || dragModule}" to choose its permissions`
                        : `Drop a module here to give ${selectedUser.name} access`}
                    </p>
                    <p className="text-xs text-gray-400 mt-1">
                      The window will ask which permissions to grant.
                    </p>
                  </div>

                  <div>
                    <h4 className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                      Granted to {selectedUser.name}
                    </h4>
                    {(() => {
                      const granted = Object.keys(moduleLookup).filter(
                        (k) => actionsFor(selectedUser, k).length > 0,
                      );
                      if (granted.length === 0) {
                        return (
                          <p className="text-sm text-gray-400 text-center py-2">
                            No access granted yet — drag a module from above into the box.
                          </p>
                        );
                      }
                      return (
                        <div className="flex flex-wrap gap-2">
                          {granted.map((moduleKey) => {
                            const mod = moduleLookup[moduleKey];
                            const actions = actionsFor(selectedUser, moduleKey);
                            return (
                              <span
                                key={moduleKey}
                                className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm bg-blue-50 text-blue-800 border border-blue-200"
                              >
                                {mod.label}
                                <span className="text-[11px] text-blue-500">
                                  {actions.map((a) => a.label).join(', ')}
                                </span>
                                <button
                                  type="button"
                                  disabled={busy}
                                  onClick={() => removeModule(moduleKey)}
                                  title={`Remove all ${mod.label} access`}
                                  className="text-blue-400 hover:text-red-500 disabled:opacity-50"
                                >
                                  <X className="w-4 h-4" />
                                </button>
                              </span>
                            );
                          })}
                        </div>
                      );
                    })()}
                  </div>
                </div>
              </>
            )}
          </div>
        </div>
      </div>

      {dialog && selectedUser && (
        <GrantModal
          module={dialog}
          employeeName={selectedUser.name}
          options={dialog.actions}
          initialChecked={actionsFor(selectedUser, dialog.module).map((a) => a.permission)}
          onSave={saveDialog}
          onCancel={() => setDialogModule(null)}
        />
      )}
    </div>
  );
}
