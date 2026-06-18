import { leaveBalancesService } from '../services/leaveBalancesService';
import { create } from 'zustand';

const payload = (response) => response?.data?.data || response?.data || response;
const leaveBalances = (response) => payload(response)?.leave_balances || payload(response)?.data?.leave_balances || payload(response)?.data || [];

export const useLeaveBalanceStore = create((set, get) => ({
  items: [],
  loading: false,
  error: null,
  fetchItems: async (params) => {
    set({ loading: true, error: null });
    try {
      const response = await leaveBalancesService.getAll(params);
      set({ items: leaveBalances(response), loading: false });
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
