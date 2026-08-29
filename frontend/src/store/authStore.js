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

      refreshUser: async () => {
        const { authService } = await import('../services/authService');
        try {
          const response = await authService.getUser();
          const u = response.data.user;
          const roles = (u.roles || []).map(r => typeof r === 'string' ? r : r.name);
          const permissions = (u.access_permissions || u.permissions || []).map(p => typeof p === 'string' ? p : p.name);
          set({ user: u, roles, permissions, isAuthenticated: true });
          return true;
        } catch (e) {
          if (e?.response?.status === 401) {
            set({ user: null, token: null, roles: [], permissions: [], isAuthenticated: false });
            try { localStorage.removeItem('auth-storage'); } catch (_) {}
          }
          return false;
        }
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
      // Normalize any stale persisted state (e.g. from older builds that stored
      // raw Role/Permission objects instead of name strings) so that rendering
      // ({roles?.[0]}, sidebar gating, etc.) never receives non-string values.
      merge: (persisted, current) => {
        if (!persisted) return current;
        const r = (persisted.roles || []).map((x) => (typeof x === 'string' ? x : x?.name)).filter(Boolean);
        const p = (persisted.permissions || []).map((x) => (typeof x === 'string' ? x : x?.name)).filter(Boolean);
        return { ...current, ...persisted, roles: r, permissions: p, user: persisted.user ?? current.user };
      },
    }
  )
);
