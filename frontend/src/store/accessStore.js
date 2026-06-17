import { create } from 'zustand';
import api from '../services/api';

export const useAccessStore = create((set) => ({
  userRoles: [],
  userPermissions: [],
  loading: false,
  error: null,

  fetchUserRoles: async (userId) => {
    set({ loading: true, error: null });
    try {
      const response = await api.get(`/users/${userId}/roles`);
      set({ userRoles: response.data, loading: false });
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  assignRole: async (userId, roles) => {
    set({ loading: true, error: null });
    try {
      const response = await api.post(`/users/${userId}/roles`, { roles });
      set({ userRoles: response.data, loading: false });
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  removeRole: async (userId, roleId) => {
    set({ loading: true, error: null });
    try {
      await api.delete(`/users/${userId}/roles/${roleId}`);
      set((state) => ({
        userRoles: state.userRoles.filter((r) => r.id !== roleId),
        loading: false,
      }));
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  fetchUserPermissions: async (userId) => {
    set({ loading: true, error: null });
    try {
      const response = await api.get(`/users/${userId}/permissions`);
      set({ userPermissions: response.data, loading: false });
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  assignPermissions: async (userId, permissions) => {
    set({ loading: true, error: null });
    try {
      const response = await api.post(`/users/${userId}/permissions`, { permissions });
      set({ userPermissions: response.data, loading: false });
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  removePermission: async (userId, permissionId) => {
    set({ loading: true, error: null });
    try {
      await api.delete(`/users/${userId}/permissions/${permissionId}`);
      set((state) => ({
        userPermissions: state.userPermissions.filter((p) => p.id !== permissionId),
        loading: false,
      }));
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },
}));
