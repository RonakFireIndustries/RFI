import api from './api';
import { normalizeQuery, unwrapList, unwrapItem } from './resourceHelpers';

export const reportsService = {
  list: async (params) => unwrapList((await api.get(`/reports-module/reports${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/reports-module/reports/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/reports-module/reports', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/reports-module/reports/${id}`, payload)).data),
  delete: async (id) => api.delete(`/reports-module/reports/${id}`),
  categories: async () => unwrapList((await api.get('/reports-module/categories')).data),
  stats: async () => unwrapItem((await api.get('/reports-module/stats')).data),
  generate: async (id, params) => unwrapItem((await api.post(`/reports-module/reports/${id}/generate`, params)).data),
  generations: async (params) => unwrapList((await api.get(`/reports-module/generations${normalizeQuery(params)}`)).data),
  downloadGeneration: async (id) => api.getBlob(`/reports-module/generations/${id}/download`),
  schedules: async (params) => unwrapList((await api.get(`/reports-module/schedules${normalizeQuery(params)}`)).data),
  createSchedule: async (payload) => unwrapItem((await api.post('/reports-module/schedules', payload)).data),
  updateSchedule: async (id, payload) => unwrapItem((await api.put(`/reports-module/schedules/${id}`, payload)).data),
  deleteSchedule: async (id) => api.delete(`/reports-module/schedules/${id}`),
  toggleSchedule: async (id) => unwrapItem((await api.post(`/reports-module/schedules/${id}/toggle`)).data),
};
