import { cloneElement, isValidElement } from 'react';
import Tooltip from '../Shared/Tooltip';
import { usePermission } from '../../hooks/usePermission';
import { permissionName } from '../../utils/permissions';

/**
 * Render an action control, disabled with an explanatory tooltip when the user
 * lacks the permission.
 *
 *   <PermissionGate module="buildings" action="delete">
 *     <button type="button" onClick={remove}><Trash2 /></button>
 *   </PermissionGate>
 *
 * The child keeps its own styling and handler; the gate only injects `disabled`
 * and `aria-disabled`. A disabled <button> does not fire onClick, which is what
 * stops the action — the backend independently rejects it with a 403.
 *
 * `subject` is optional prose for the tooltip, e.g. subject="this building".
 */
export const PermissionGate = ({ module, action = 'view', permission, subject, children }) => {
  const { can, canModule, denialFor } = usePermission();

  const required = permission || (module ? permissionName(module, action) : null);
  const allowed = permission ? can(permission) : canModule(module, action);

  if (allowed) return <>{children}</>;
  if (!isValidElement(children)) return null;

  return (
    <Tooltip content={denialFor(module, action, subject || required)}>
      {cloneElement(children, { disabled: true, 'aria-disabled': true })}
    </Tooltip>
  );
};

export default PermissionGate;
