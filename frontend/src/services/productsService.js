import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

export const productsService = {
  list: async (params) => unwrapList((await api.get(`/products${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/products/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/products', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/products/${id}`, payload)).data),
  remove: async (id) => api.delete(`/products/${id}`),
};
