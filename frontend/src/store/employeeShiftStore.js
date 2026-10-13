import { create } from 'zustand';
import { employeeShiftService } from '../services/employeeShiftService';

export const useEmployeeShiftStore = create((set, get) => ({
  assignment: null,
  history: [],
  currentShift: null,
  shiftEmployees: [],
  loading: false,
  error: null,

  fetchAssignment: async (employeeId) => {
    set({ loading: true, error: null });
    try {
      const data = await employeeShiftService.getAssignment(employeeId);
      set({ assignment: data?.assignment ?? data, currentShift: data?.shift ?? data?.assignment?.shift ?? null, loading: false });
      return data;
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  fetchHistory: async (employeeId) => {
    set({ loading: true, error: null });
    try {
      const history = await employeeShiftService.getHistory(employeeId);
      set({ history, loading: false });
      return history;
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  fetchCurrentShift: async (employeeId) => {
    set({ loading: true, error: null });
    try {
      const data = await employeeShiftService.getCurrentShift(employeeId);
      set({ currentShift: data?.shift ?? data?.assignment?.shift ?? null, assignment: data?.assignment ?? data, loading: false });
      return data;
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  fetchShiftEmployees: async (shiftId) => {
    set({ loading: true, error: null });
    try {
      const shiftEmployees = await employeeShiftService.getShiftEmployees(shiftId);
      set({ shiftEmployees, loading: false });
      return shiftEmployees;
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  assignShift: async (employeeId, payload) => {
    set({ loading: true, error: null });
    try {
      const newAssignment = await employeeShiftService.assign(employeeId, payload);
      set({ assignment: newAssignment?.assignment ?? newAssignment, loading: false });
      get().fetchHistory(employeeId);
      get().fetchCurrentShift(employeeId);
      return newAssignment;
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  changeShift: async (employeeId, payload) => {
    set({ loading: true, error: null });
    try {
      const updated = await employeeShiftService.change(employeeId, payload);
      set({ assignment: updated?.assignment ?? updated, loading: false });
      get().fetchHistory(employeeId);
      get().fetchCurrentShift(employeeId);
      return updated;
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  removeShift: async (employeeId, shiftId, remarks) => {
    set({ loading: true, error: null });
    try {
      await employeeShiftService.remove(employeeId, shiftId, remarks);
      set({ assignment: null, currentShift: null, loading: false });
      get().fetchHistory(employeeId);
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  }
}));
