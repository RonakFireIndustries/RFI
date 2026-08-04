import api from './api';
import { normalizeQuery } from './resourceHelpers';

export const inventoryLocationService = {
  list: async (params) => {
    const res = await api.get(`/locations${normalizeQuery(params)}`);
    return res.data;
  },
  get: async (id) => {
    const res = await api.get(`/locations/${id}`);
    return res.data;
  },
  create: async (payload) => {
    const res = await api.post('/locations', payload);
    return res.data;
  },
  update: async (id, payload) => {
    const res = await api.put(`/locations/${id}`, payload);
    return res.data;
  },
  remove: async (id) => api.delete(`/locations/${id}`),
};
