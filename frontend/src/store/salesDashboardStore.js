import { create } from 'zustand';
import { salesDashboardService } from '../services/salesDashboardService';

export const useSalesDashboardStore = create((set) => ({
  stats: null,
  loading: false,
  error: null,

  fetchStats: async () => {
    set({ loading: true, error: null });
    try {
      const stats = await salesDashboardService.getStats();
      set({ stats, loading: false });
      return stats;
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },
}));
