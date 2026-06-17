import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

export const attendancesService = {
  list: async (params = {}) => unwrapList((await api.get(`/attendances${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/attendances/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/attendances', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/attendances/${id}`, payload)).data),
  remove: async (id) => api.delete(`/attendances/${id}`),
  checkIn: async (payload) => unwrapItem((await api.post('/attendance/check-in', payload)).data),
  checkOut: async (payload) => unwrapItem((await api.post('/attendance/check-out', payload)).data),
};
