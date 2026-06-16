import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

export const suppliersService = {
  list: async (params) => unwrapList((await api.get(`/suppliers${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/suppliers/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/suppliers', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/suppliers/${id}`, payload)).data),
  remove: async (id) => api.delete(`/suppliers/${id}`),
};
