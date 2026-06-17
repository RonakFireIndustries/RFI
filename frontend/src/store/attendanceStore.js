import { create } from 'zustand';
import { attendancesService } from '../services/attendancesService';

export const useAttendanceStore = create((set, get) => ({
  // Base resource store properties
  items: [],
  selected: null,
  loading: false,
  error: null,

  fetchItems: async (params) => {
    set({ loading: true, error: null });
    try {
      const items = await attendancesService.list(params);
      set({ items, loading: false });
      return items;
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  fetchItem: async (id) => {
    set({ loading: true, error: null });
    try {
      const selected = await attendancesService.get(id);
      set({ selected, loading: false });
      return selected;
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  createItem: async (payload) => {
    const item = await attendancesService.create(payload);
    set({ items: [item, ...get().items] });
    return item;
  },

  updateItem: async (id, payload) => {
    const item = await attendancesService.update(id, payload);
    set({ items: get().items.map((current) => current.id === id ? item : current) });
    return item;
  },

  deleteItem: async (id) => {
    await attendancesService.remove(id);
    set({ items: get().items.filter((item) => item.id !== id) });
  },

  clearSelected: () => set({ selected: null }),

  // Custom properties for Attendance.jsx
  logs: [],
  report: null,

  fetchLogs: async (params = {}) => {
    set({ loading: true, error: null });
    try {
      const items = await attendancesService.list({ per_page: 1000, ...params });
      set({ 
        logs: items, 
        report: { attendance_records: items },
        loading: false 
      });
    } catch (error) {
      set({ error, loading: false });
    }
  },

  checkIn: async (payload) => {
    const data = await attendancesService.checkIn(payload);
    return data;
  },

  checkOut: async (payload) => {
    const data = await attendancesService.checkOut(payload);
    return data;
  }
}));
