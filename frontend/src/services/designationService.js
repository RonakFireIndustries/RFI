import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

export const designationService = {
  list: async (params) => unwrapList((await api.get(`/designations${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/designations/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/designations', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/designations/${id}`, payload)).data),
  remove: async (id) => api.delete(`/designations/${id}`),
};
