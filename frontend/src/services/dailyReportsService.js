import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

export const dailyReportsService = {
  list: async (params = {}) => unwrapList((await api.get(`/daily-reports${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/daily-reports/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/daily-reports', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/daily-reports/${id}`, payload)).data),
  remove: async (id) => api.delete(`/daily-reports/${id}`),

  // Workflow actions
  approve: async (id, comments = '') => unwrapItem((await api.post(`/daily-reports/${id}/approve`, { comments })).data),
  reject: async (id, comments = '') => unwrapItem((await api.post(`/daily-reports/${id}/reject`, { comments })).data),
  rework: async (id, comments = '') => unwrapItem((await api.post(`/daily-reports/${id}/rework`, { comments })).data),
};
