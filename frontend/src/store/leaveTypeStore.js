import { leaveTypesService } from '../services/leaveTypesService';
import { create } from 'zustand';

const payload = (response) => response?.data?.data || response?.data || response;
const leaveTypes = (response) => payload(response)?.leave_types || payload(response)?.data?.leave_types || payload(response)?.data || [];
const leaveType = (response) => payload(response)?.leave_type || payload(response)?.data?.leave_type || payload(response);

export const useLeaveTypeStore = create((set, get) => ({
  items: [],
  selected: null,
  loading: false,
  error: null,

  fetchItems: async (params) => {
    set({ loading: true, error: null });
    try {
      const response = await leaveTypesService.getAll(params);
      const items = leaveTypes(response);
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
      const response = await leaveTypesService.getById(id);
      const selected = leaveType(response);
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
      const response = await leaveTypesService.create(payload);
      const item = leaveType(response);
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
      const response = await leaveTypesService.update(id, payload);
      const item = leaveType(response);
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
      await leaveTypesService.delete(id);
      set({ items: get().items.filter((item) => item.id !== id), loading: false });
    } catch (error) {
      set({ error: error.message, loading: false });
      throw error;
    }
  },

  clearSelected: () => set({ selected: null }),
}));
