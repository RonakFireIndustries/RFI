import { create } from 'zustand';
import { dailyReportsService } from '../services/dailyReportsService';

export const useDailyReportsStore = create((set, get) => ({
  items: [],
  current: null,
  loading: false,
  error: null,

  fetchItems: async (params = {}) => {
    set({ loading: true, error: null });
    try {
      const data = await dailyReportsService.list(params);
      set({ items: data || [], loading: false });
    } catch (err) {
      set({ error: err.message, loading: false });
    }
  },

  fetchItem: async (id) => {
    set({ loading: true, error: null });
    try {
      const data = await dailyReportsService.get(id);
      set({ current: data, loading: false });
      return data;
    } catch (err) {
      set({ error: err.message, loading: false });
      throw err;
    }
  },

  createItem: async (payload) => {
    set({ loading: true, error: null });
    try {
      const data = await dailyReportsService.create(payload);
      set((state) => ({ items: [...state.items, data], loading: false }));
      return data;
    } catch (err) {
      set({ error: err.message, loading: false });
      throw err;
    }
  },

  updateItem: async (id, payload) => {
    set({ loading: true, error: null });
    try {
      const data = await dailyReportsService.update(id, payload);
      set((state) => ({
        items: state.items.map((item) => (item.id === id ? data : item)),
        current: state.current?.id === id ? data : state.current,
        loading: false,
      }));
      return data;
    } catch (err) {
      set({ error: err.message, loading: false });
      throw err;
    }
  },

  deleteItem: async (id) => {
    set({ loading: true, error: null });
    try {
      await dailyReportsService.remove(id);
      set((state) => ({
        items: state.items.filter((item) => item.id !== id),
        current: state.current?.id === id ? null : state.current,
        loading: false,
      }));
    } catch (err) {
      set({ error: err.message, loading: false });
      throw err;
    }
  },

  approve: async (id, comments = '') => {
    set({ loading: true, error: null });
    try {
      const data = await dailyReportsService.approve(id, comments);
      set((state) => ({
        items: state.items.map((item) => (item.id === id ? data : item)),
        current: state.current?.id === id ? data : state.current,
        loading: false,
      }));
      return data;
    } catch (err) {
      set({ error: err.message, loading: false });
      throw err;
    }
  },

  reject: async (id, comments = '') => {
    set({ loading: true, error: null });
    try {
      const data = await dailyReportsService.reject(id, comments);
      set((state) => ({
        items: state.items.map((item) => (item.id === id ? data : item)),
        current: state.current?.id === id ? data : state.current,
        loading: false,
      }));
      return data;
    } catch (err) {
      set({ error: err.message, loading: false });
      throw err;
    }
  },

  rework: async (id, comments = '') => {
    set({ loading: true, error: null });
    try {
      const data = await dailyReportsService.rework(id, comments);
      set((state) => ({
        items: state.items.map((item) => (item.id === id ? data : item)),
        current: state.current?.id === id ? data : state.current,
        loading: false,
      }));
      return data;
    } catch (err) {
      set({ error: err.message, loading: false });
      throw err;
    }
  },
}));
