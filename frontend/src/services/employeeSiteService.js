import api from './api';
import { unwrapItem, unwrapList } from './resourceHelpers';

export const employeeSiteService = {
  getAssignments: async (employeeId) => {
    const response = await api.get(`/employees/${employeeId}/sites`);
    return unwrapList(response.data);
  },
  
  getHistory: async (employeeId) => {
    const response = await api.get(`/employees/${employeeId}/sites/history`);
    return unwrapList(response.data);
  },

  getCurrentSite: async (employeeId) => {
    const response = await api.get(`/employees/${employeeId}/current-site`);
    return unwrapItem(response.data);
  },

  getSiteEmployees: async (siteId) => {
    const response = await api.get(`/sites/${siteId}/employees`);
    return unwrapList(response.data);
  },

  assign: async (employeeId, payload) => {
    const response = await api.post(`/employees/${employeeId}/sites`, payload);
    return unwrapItem(response.data);
  },

  transfer: async (employeeId, payload) => {
    const response = await api.post(`/employees/${employeeId}/sites/transfer`, payload);
    return unwrapItem(response.data);
  },

  remove: async (employeeId, siteId, remarks) => {
    const endpoint = `/employees/${employeeId}/sites/${siteId}` + (remarks ? `?remarks=${encodeURIComponent(remarks)}` : '');
    const response = await api.delete(endpoint);
    return response.data;
  }
};
