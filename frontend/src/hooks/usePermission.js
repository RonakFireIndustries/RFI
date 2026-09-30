import { useCallback } from 'react';
import { useAuthStore } from '../store/authStore';
import {
  can as canWithContext,
  denialMessage,
  isSuperAdmin as checkSuperAdmin,
  permissionName,
} from '../utils/permissions';

/**
 * Access to the signed-in user's canonical permissions.
 *
 *   const { canModule, denialFor } = usePermission();
 *   canModule('buildings', 'create')   // -> buildings.create
 *
 * IMPORTANT: a missing `module` fails OPEN. Pages that have not declared their
 * module yet keep working rather than being silently locked down; the backend
 * still enforces every action via authorize(), so this is a UX layer only.
 */
export const usePermission = () => {
  const user = useAuthStore((state) => state.user);
  const permissions = useAuthStore((state) => state.permissions);

  const list = permissions || [];
  const superAdmin = checkSuperAdmin(user, list);
  const context = { user, permissions: list };

  const can = useCallback((required) => canWithContext(context, required), [user, list]);

  const canModule = useCallback(
    (module, action = 'view') => {
      if (superAdmin) return true;
      if (!module) {
        if (import.meta.env?.DEV) {
          console.warn(
            `[permissions] No module declared, allowing "${action}". ` +
              'Add a module="<access.php key>" prop to gate this action.'
          );
        }
        return true;
      }
      return canWithContext(context, permissionName(module, action));
    },
    [user, list, superAdmin]
  );

  const denialFor = useCallback(
    (module, action, subject) => denialMessage({ module, action, subject }),
    []
  );

  return { permissions: list, isSuperAdmin: superAdmin, can, canModule, denialFor };
};
