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
      
      logout: () => {
        set({ user: null, token: null, roles: [], permissions: [], isAuthenticated: false });
      },
    }),
    {
      name: 'auth-storage',
    }
  )
);
