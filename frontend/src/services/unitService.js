import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

export const unitService = {
  list: async (params) => unwrapList((await api.get(`/units${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/units/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/units', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/units/${id}`, payload)).data),
  remove: async (id) => api.delete(`/units/${id}`),
};
