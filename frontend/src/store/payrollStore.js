import { create } from 'zustand';
import api from '../services/api';

export const usePayrollStore = create((set, get) => ({
  payrolls: [],
  selectedPayroll: null,
  loading: false,
  error: null,

  fetchPayrolls: async (params = {}) => {
    set({ loading: true, error: null });
    try {
      const query = new URLSearchParams(params).toString();
      const response = await api.get(`/payroll?${query}`);
      set({ payrolls: response.data, loading: false });
    } catch (error) {
      set({ error, loading: false });
    }
  },

  fetchPayroll: async (id) => {
    set({ loading: true, error: null });
    try {
      const response = await api.get(`/payroll/${id}`);
      set({ selectedPayroll: response.data, loading: false });
    } catch (error) {
      set({ error, loading: false });
    }
  },

  generatePayroll: async (payload) => {
    set({ loading: true, error: null });
    try {
      const response = await api.post('/payroll/generate', payload);
      set({ loading: false });
      return response.data;
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  regeneratePayroll: async (payrollId) => {
    set({ loading: true, error: null });
    try {
      const response = await api.post('/payroll/regenerate', { payroll_id: payrollId });
      set({ loading: false });
      return response.data;
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  approvePayroll: async (payrollId) => {
    set({ loading: true, error: null });
    try {
      await api.post(`/payroll/${payrollId}/approve`);
      await get().fetchPayrolls();
      set({ loading: false });
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  unlockPayroll: async (payrollId) => {
    set({ loading: true, error: null });
    try {
      await api.post(`/payroll/${payrollId}/unlock`);
      await get().fetchPayrolls();
      set({ loading: false });
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  lockPayroll: async (payrollId) => {
    set({ loading: true, error: null });
    try {
      await api.post(`/payroll/${payrollId}/lock`);
      await get().fetchPayrolls();
      set({ loading: false });
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  deletePayroll: async (payrollId) => {
    set({ loading: true, error: null });
    try {
      await api.delete(`/payroll/${payrollId}`);
      await get().fetchPayrolls();
      set({ loading: false });
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  generatePayslip: async (payrollId) => {
    set({ loading: true, error: null });
    try {
      const response = await api.post(`/payrolls/${payrollId}/payslips/generate`);
      set({ loading: false });
      return response.data;
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },
}));
