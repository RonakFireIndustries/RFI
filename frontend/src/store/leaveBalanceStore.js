import { createResourceStore } from './createResourceStore';
import { leaveBalancesService } from '../services/leaveBalancesService';
import { create } from 'zustand';

export const useLeaveBalanceStore = create((set, get) => ({
  items: [],
  loading: false,
  error: null,
  fetchItems: async (params) => {
    set({ loading: true, error: null });
    try {
      const response = await leaveBalancesService.getAll(params);
      set({ items: response.data?.leave_balances || [], loading: false });
    } catch (error) {
      set({ error: error.message, loading: false });
    }
  },
  initialize: async (employeeId, year) => {
    set({ loading: true, error: null });
    try {
      await leaveBalancesService.initialize(employeeId, year);
      await get().fetchItems({ employee_id: employeeId });
    } catch (error) {
      set({ error: error.message, loading: false });
    }
  }
}));
