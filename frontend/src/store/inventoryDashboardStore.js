import { create } from 'zustand';
import { inventoryDashboardService } from '../services/inventoryDashboardService';

export const useInventoryDashboardStore = create((set) => ({
  data: null,
  loading: false,
  error: null,

  fetch: async (params) => {
    set({ loading: true, error: null });
    try {
      const data = await inventoryDashboardService.get(params);
      set({ data, loading: false });
      return data;
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },
}));
