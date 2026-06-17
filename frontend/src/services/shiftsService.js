import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

export const shiftsService = {
  list: async (params = {}) => unwrapList((await api.get(`/shifts${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/shifts/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/shifts', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/shifts/${id}`, payload)).data),
  remove: async (id) => api.delete(`/shifts/${id}`),
};
