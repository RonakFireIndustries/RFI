import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

export const siteService = {
  list: async (params) => unwrapList((await api.get(`/sites${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/sites/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/sites', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/sites/${id}`, payload)).data),
  remove: async (id) => api.delete(`/sites/${id}`),
};
