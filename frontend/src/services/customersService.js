import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

export const customersService = {
  list: async (params) => unwrapList((await api.get(`/customers${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/customers/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/customers', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/customers/${id}`, payload)).data),
  remove: async (id) => api.delete(`/customers/${id}`),
};
