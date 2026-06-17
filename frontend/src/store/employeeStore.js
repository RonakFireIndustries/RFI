import { create } from 'zustand';
import api from '../services/api';

export const useEmployeeStore = create((set, get) => ({
  employees: [],
  employee: null,
  pagination: null,
  loading: false,
  error: null,

  fetchEmployees: async (params = {}) => {
    set({ loading: true, error: null });
    try {
      const response = await api.get('/employees', { params });
      set({ 
        employees: response.data.data.employees,
        pagination: response.data.data.pagination,
        loading: false 
      });
    } catch (error) {
      set({ error: error.message, loading: false });
    }
  },

  fetchEmployee: async (id) => {
    set({ loading: true, error: null });
    try {
      const response = await api.get(`/employees/${id}`);
      set({ employee: response.data.data.employee, loading: false });
      return response.data.data.employee;
    } catch (error) {
      set({ error: error.message, loading: false });
      throw error;
    }
  },

  createEmployee: async (data) => {
    set({ loading: true, error: null });
    try {
      const response = await api.post('/employees', data);
      const newEmployee = response.data.data.employee;
      set(state => ({ 
        employees: [newEmployee, ...state.employees],
        loading: false 
      }));
      return newEmployee;
    } catch (error) {
      set({ error: error.message, loading: false });
      throw error;
    }
  },

  updateEmployee: async (id, data) => {
    set({ loading: true, error: null });
    try {
      const response = await api.put(`/employees/${id}`, data);
      const updatedEmployee = response.data.data.employee;
      set(state => ({
        employees: state.employees.map(e => e.id === id ? updatedEmployee : e),
        employee: state.employee?.id === id ? updatedEmployee : state.employee,
        loading: false
      }));
      return updatedEmployee;
    } catch (error) {
      set({ error: error.message, loading: false });
      throw error;
    }
  },

  deleteEmployee: async (id) => {
    set({ loading: true, error: null });
    try {
      await api.delete(`/employees/${id}`);
      set(state => ({
        employees: state.employees.filter(e => e.id !== id),
        loading: false
      }));
    } catch (error) {
      set({ error: error.message, loading: false });
      throw error;
    }
  }
}));
