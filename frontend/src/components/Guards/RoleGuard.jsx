import React from 'react';
import { useAuthStore } from '../../store/authStore';

export const RoleGuard = ({ children, requiredRole, fallback = null }) => {
  const { roles } = useAuthStore();
  
  const hasRole = () => {
    if (roles.includes('Super Admin')) return true;

    if (Array.isArray(requiredRole)) {
      return requiredRole.some(role => roles.includes(role));
    }
    return roles.includes(requiredRole);
  };

  if (!hasRole()) {
    return fallback;
  }

  return <>{children}</>;
};
