import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

export const opportunityService = {
  list: async (params) => unwrapList((await api.get(`/opportunities${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/opportunities/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/opportunities', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/opportunities/${id}`, payload)).data),
  remove: async (id) => api.delete(`/opportunities/${id}`),
};
