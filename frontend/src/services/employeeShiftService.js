import api from './api';
import { unwrapItem, unwrapList } from './resourceHelpers';

export const employeeShiftService = {
  getCurrentShift: async (employeeId) => {
    const response = await api.get(`/employees/${employeeId}/current-shift`);
    return unwrapItem(response.data);
  },

  getAssignment: async (employeeId) => {
    const response = await api.get(`/employees/${employeeId}/shift`);
    return unwrapItem(response.data);
  },

  getHistory: async (employeeId) => {
    const response = await api.get(`/employees/${employeeId}/shifts/history`);
    return unwrapList(response.data);
  },

  getShiftEmployees: async (shiftId) => {
    const response = await api.get(`/shifts/${shiftId}/employees`);
    return unwrapList(response.data);
  },

  assign: async (employeeId, payload) => {
    const response = await api.post(`/employees/${employeeId}/shift`, payload);
    return unwrapItem(response.data);
  },

  change: async (employeeId, payload) => {
    const response = await api.post(`/employees/${employeeId}/shifts/transfer`, payload);
    return unwrapItem(response.data);
  },

  remove: async (employeeId, shiftId, remarks) => {
    const endpoint = shiftId ? `/employees/${employeeId}/shifts/${shiftId}` : `/employees/${employeeId}/shift`;
    const query = remarks ? `${shiftId ? '?' : '?'}remarks=${encodeURIComponent(remarks)}` : '';
    const response = await api.delete(endpoint + query);
    return response.data;
  }
};
