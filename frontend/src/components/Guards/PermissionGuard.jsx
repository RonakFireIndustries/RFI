import { usePermission } from '../../hooks/usePermission';

/**
 * Render `children` only when the user holds the permission.
 *
 * Use this for non-interactive content (whole panels, columns, sections) where
 * a disabled state makes no sense. For buttons, prefer PermissionGate, which
 * shows the control greyed out with an explanation instead of hiding it.
 *
 *   <PermissionGuard module="buildings" action="create">
 *     <AddBuildingForm />
 *   </PermissionGuard>
 *
 * A missing `module` fails open (renders children) and warns in dev — see
 * usePermission.
 */
export const PermissionGuard = ({ module, action = 'view', permission, children, fallback = null }) => {
  const { can, canModule } = usePermission();

  const allowed = permission ? can(permission) : canModule(module, action);

  return allowed ? <>{children}</> : fallback;
};

export default PermissionGuard;
