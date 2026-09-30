/**
 * Canonical permission helpers.
 *
 * Permission names are "{module}.{action}" and are defined by the backend in
 * config/access.php. This file is the frontend mirror of that contract; it
 * deliberately contains no React so it can be unit tested and reused outside
 * components.
 *
 * Semantics match the sidebar gate that already existed in
 * components/Layout/DashboardLayout.jsx, and match the backend's
 * App\Support\Access::can():
 *   - super admins (is_super_admin column, or the __super_admin__ marker) pass
 *     every check
 *   - a requirement of '*' means "no gate"
 *   - an empty/absent requirement list means "no gate"
 *   - a list of requirements is an OR
 */

export const SUPER_ADMIN_PERMISSION = '__super_admin__';
export const ANY_PERMISSION = '*';

/**
 * Action labels, kept in sync with the 'actions' map in
 * backend/config/access.php so the wording matches the Access Control screen.
 */
const ACTION_LABELS = {
  view: 'view',
  create: 'add',
  update: 'edit',
  delete: 'remove',
  manage: 'manage',
  approve: 'approve',
  reject: 'reject',
  submit: 'submit',
  assign: 'assign',
  transfer: 'transfer',
  convert: 'convert',
  issue: 'issue',
  receive: 'receive',
  export: 'export',
  schedule: 'schedule',
  email: 'email',
  download: 'download',
  checkin: 'check in',
  checkout: 'check out',
};

export const isSuperAdmin = (user, permissions) =>
  !!user?.is_super_admin || (permissions || []).includes(SUPER_ADMIN_PERMISSION);

/**
 * Build a canonical permission name. Defaults to the 'view' action.
 */
export const permissionName = (module, action = 'view') =>
  action ? `${module}.${action}` : `${module}.view`;

/**
 * Check a requirement list against the signed-in user's permissions.
 * `ctx` is `{ user, permissions }`.
 */
export const can = (ctx, required) => {
  const list = ctx?.permissions || [];
  if (isSuperAdmin(ctx?.user, list)) return true;

  if (!required) return true;
  const requirements = Array.isArray(required) ? required : [required];
  if (requirements.length === 0) return true;
  if (requirements.includes(ANY_PERMISSION)) return true;

  return requirements.some((permission) => list.includes(permission));
};

/**
 * Human-readable reason shown in the disabled-state tooltip.
 */
export const denialMessage = ({ module, action, required, subject } = {}) => {
  const permission = required || (module ? permissionName(module, action) : null);
  const verb = ACTION_LABELS[action] || action || 'use';
  const what = subject || (module ? module.replace(/-/g, ' ') : 'this record');
  return permission
    ? `Requires the ${permission} permission to ${verb} ${what}.`
    : `You do not have permission to ${verb} ${what}.`;
};
