import React from 'react';
import { useAuthStore } from '../../store/authStore';

export const PermissionGuard = ({ children, requiredPermission, fallback = null }) => {
  const { permissions, roles } = useAuthStore();
  
  const hasPermission = () => {
    // Super Admin role overrides all permission checks
    if (roles.includes('Super Admin')) return true;
    
    if (Array.isArray(requiredPermission)) {
      return requiredPermission.some(permission => permissions.includes(permission));
    }
    return permissions.includes(requiredPermission);
  };

  if (!hasPermission()) {
    return fallback;
  }

  return <>{children}</>;
};
