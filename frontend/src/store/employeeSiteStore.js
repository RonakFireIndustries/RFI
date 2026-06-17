import { create } from 'zustand';
import { employeeSiteService } from '../services/employeeSiteService';

export const useEmployeeSiteStore = create((set, get) => ({
  assignments: [],
  history: [],
  currentSite: null,
  siteEmployees: [],
  loading: false,
  error: null,

  fetchAssignments: async (employeeId) => {
    set({ loading: true, error: null });
    try {
      const assignments = await employeeSiteService.getAssignments(employeeId);
      set({ assignments, loading: false });
      return assignments;
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  fetchHistory: async (employeeId) => {
    set({ loading: true, error: null });
    try {
      const history = await employeeSiteService.getHistory(employeeId);
      set({ history, loading: false });
      return history;
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  fetchCurrentSite: async (employeeId) => {
    set({ loading: true, error: null });
    try {
      const currentSite = await employeeSiteService.getCurrentSite(employeeId);
      set({ currentSite, loading: false });
      return currentSite;
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  fetchSiteEmployees: async (siteId) => {
    set({ loading: true, error: null });
    try {
      const siteEmployees = await employeeSiteService.getSiteEmployees(siteId);
      set({ siteEmployees, loading: false });
      return siteEmployees;
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  assignEmployee: async (employeeId, payload) => {
    set({ loading: true, error: null });
    try {
      const newAssignment = await employeeSiteService.assign(employeeId, payload);
      set({ 
        assignments: [...get().assignments, newAssignment],
        loading: false 
      });
      // Refresh history & current site
      get().fetchHistory(employeeId);
      get().fetchCurrentSite(employeeId);
      return newAssignment;
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  transferEmployee: async (employeeId, payload) => {
    set({ loading: true, error: null });
    try {
      const updatedAssignment = await employeeSiteService.transfer(employeeId, payload);
      // Replace or update local assignment
      const filtered = get().assignments.filter(a => a.site_id !== payload.current_site_id);
      set({ 
        assignments: [...filtered, updatedAssignment],
        loading: false 
      });
      // Refresh history & current site
      get().fetchHistory(employeeId);
      get().fetchCurrentSite(employeeId);
      return updatedAssignment;
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  removeEmployee: async (employeeId, siteId, remarks) => {
    set({ loading: true, error: null });
    try {
      await employeeSiteService.remove(employeeId, siteId, remarks);
      set({ 
        assignments: get().assignments.filter(a => a.site_id !== siteId),
        loading: false 
      });
      // Refresh history & current site
      get().fetchHistory(employeeId);
      get().fetchCurrentSite(employeeId);
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  }
}));
