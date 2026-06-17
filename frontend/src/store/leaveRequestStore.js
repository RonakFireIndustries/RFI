import { leaveRequestsService } from '../services/leaveRequestsService';
import { create } from 'zustand';

export const useLeaveRequestStore = create((set, get) => ({
  items: [],
  selected: null,
  loading: false,
  error: null,

  fetchItems: async (params) => {
    set({ loading: true, error: null });
    try {
      const response = await leaveRequestsService.getAll(params);
      const items = response.data || response;
      set({ items, loading: false });
      return items;
    } catch (error) {
      set({ error: error.message, loading: false });
      throw error;
    }
  },

  fetchItem: async (id) => {
    set({ loading: true, error: null });
    try {
      const response = await leaveRequestsService.getById(id);
      const selected = response.data || response;
      set({ selected, loading: false });
      return selected;
    } catch (error) {
      set({ error: error.message, loading: false });
      throw error;
    }
  },

  createItem: async (payload) => {
    set({ loading: true, error: null });
    try {
      const response = await leaveRequestsService.create(payload);
      const item = response.data || response;
      set({ items: [item, ...get().items], loading: false });
      return item;
    } catch (error) {
      set({ error: error.message, loading: false });
      throw error;
    }
  },

  updateItem: async (id, payload) => {
    set({ loading: true, error: null });
    try {
      const response = await leaveRequestsService.update(id, payload);
      const item = response.data || response;
      set({ items: get().items.map((current) => current.id === id ? item : current), loading: false });
      return item;
    } catch (error) {
      set({ error: error.message, loading: false });
      throw error;
    }
  },

  deleteItem: async (id) => {
    set({ loading: true, error: null });
    try {
      await leaveRequestsService.delete(id);
      set({ items: get().items.filter((item) => item.id !== id), loading: false });
    } catch (error) {
      set({ error: error.message, loading: false });
      throw error;
    }
  },

  clearSelected: () => set({ selected: null }),

  approveLeave: async (id, data) => {
    set({ loading: true, error: null });
    try {
      await leaveRequestsService.approve(id, data);
      await get().fetchItems();
      set({ loading: false });
    } catch (error) {
      set({ error: error.message, loading: false });
      throw error;
    }
  },

  rejectLeave: async (id, data) => {
    set({ loading: true, error: null });
    try {
      await leaveRequestsService.reject(id, data);
      await get().fetchItems();
      set({ loading: false });
    } catch (error) {
      set({ error: error.message, loading: false });
      throw error;
    }
  },

  cancelLeave: async (id, data) => {
    set({ loading: true, error: null });
    try {
      await leaveRequestsService.cancel(id, data);
      await get().fetchItems();
      set({ loading: false });
    } catch (error) {
      set({ error: error.message, loading: false });
      throw error;
    }
  }
}));
