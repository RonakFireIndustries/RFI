import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

export const inventoryService = {
  list: async (params) => unwrapList((await api.get(`/inventory${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/inventory/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/inventory', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/inventory/${id}`, payload)).data),
  remove: async (id) => api.delete(`/inventory/${id}`),
  transactions: async (params) => unwrapList((await api.get(`/inventory/transactions${normalizeQuery(params)}`)).data),
  recordTransaction: async (payload) => unwrapItem((await api.post('/inventory/transaction', payload)).data),
};
