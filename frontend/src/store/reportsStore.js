import { create } from 'zustand';
import { reportsService } from '../services/reportsService';

export const useReportsStore = create((set, get) => ({
  reports: [],
  categories: [],
  generations: [],
  schedules: [],
  stats: null,
  selectedReport: null,
  loading: false,
  error: null,

  fetchCategories: async () => {
    try {
      const categories = await reportsService.categories();
      set({ categories });
      return categories;
    } catch (error) {
      set({ error: error?.response?.data?.message || error.message });
      return [];
    }
  },

  fetchReports: async (params) => {
    set({ loading: true, error: null });
    try {
      const reports = await reportsService.list(params);
      set({ reports, loading: false });
      return reports;
    } catch (error) {
      set({ error: error?.response?.data?.message || error.message, loading: false });
      return [];
    }
  },

  fetchReport: async (id) => {
    set({ loading: true, error: null });
    try {
      const selectedReport = await reportsService.get(id);
      set({ selectedReport, loading: false });
      return selectedReport;
    } catch (error) {
      set({ error: error?.response?.data?.message || error.message, loading: false });
      return null;
    }
  },

  createReport: async (payload) => {
    try {
      const item = await reportsService.create(payload);
      set({ reports: [item, ...get().reports] });
      return item;
    } catch (error) {
      throw error;
    }
  },

  updateReport: async (id, payload) => {
    try {
      const item = await reportsService.update(id, payload);
      set({ reports: get().reports.map(r => r.id === id ? { ...r, ...item } : r) });
      return item;
    } catch (error) {
      throw error;
    }
  },

  deleteReport: async (id) => {
    try {
      await reportsService.delete(id);
      set({ reports: get().reports.filter(r => r.id !== id) });
    } catch (error) {
      throw error;
    }
  },

  fetchGenerations: async (params) => {
    try {
      const generations = await reportsService.generations(params);
      set({ generations });
      return generations;
    } catch (error) {
      set({ error: error?.response?.data?.message || error.message });
      return [];
    }
  },

  generateReport: async (id, params) => {
    try {
      const generation = await reportsService.generate(id, params);
      const generations = get().generations;
      set({ generations: [generation, ...generations].slice(0, 50) });
      return generation;
    } catch (error) {
      throw error;
    }
  },

  fetchSchedules: async (params) => {
    try {
      const schedules = await reportsService.schedules(params);
      set({ schedules });
      return schedules;
    } catch (error) {
      set({ error: error?.response?.data?.message || error.message });
      return [];
    }
  },

  createSchedule: async (payload) => {
    try {
      const item = await reportsService.createSchedule(payload);
      set({ schedules: [...get().schedules, item] });
      return item;
    } catch (error) {
      throw error;
    }
  },

  toggleSchedule: async (id) => {
    try {
      const item = await reportsService.toggleSchedule(id);
      set({ schedules: get().schedules.map(s => s.id === id ? { ...s, ...item } : s) });
      return item;
    } catch (error) {
      throw error;
    }
  },

  deleteSchedule: async (id) => {
    try {
      await reportsService.deleteSchedule(id);
      set({ schedules: get().schedules.filter(s => s.id !== id) });
    } catch (error) {
      throw error;
    }
  },

  fetchStats: async () => {
    try {
      const stats = await reportsService.stats();
      set({ stats });
      return stats;
    } catch (error) {
      set({ error: error?.response?.data?.message || error.message });
      return null;
    }
  },

  fetchAll: async (params) => {
    set({ loading: true, error: null });
    try {
      const [reports, categories, generations, schedules, stats] = await Promise.all([
        reportsService.list(params),
        reportsService.categories(),
        reportsService.generations({ per_page: 5 }),
        reportsService.schedules(),
        reportsService.stats(),
      ]);
      set({ reports, categories, generations, schedules, stats, loading: false });
    } catch (error) {
      set({ error: error?.response?.data?.message || error.message, loading: false });
    }
  },
}));
