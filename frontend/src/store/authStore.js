import { create } from 'zustand';
import { persist } from 'zustand/middleware';

export const useAuthStore = create(
  persist(
    (set) => ({
      user: null,
      token: null,
      roles: [],
      permissions: [],
      isAuthenticated: false,
      
      setAuth: (user, token, roles = [], permissions = []) => {
        set({ user, token, roles, permissions, isAuthenticated: true });
      },
      
      logout: async () => {
        try {
          const { authService } = await import('../services/authService');
          await authService.logout();
        } catch (e) {
          console.error("Backend logout failed", e);
        }
        set({ user: null, token: null, roles: [], permissions: [], isAuthenticated: false });
      },
    }),
    {
      name: 'auth-storage',
    }
  )
);
