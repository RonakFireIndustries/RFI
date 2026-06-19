import api from './api';
import { normalizeQuery } from './resourceHelpers';

export const inventoryLocationService = {
  list: async (params) => {
    const res = await api.get(`/locations${normalizeQuery(params)}`);
    return res.data;
  },
  create: async (payload) => {
    const res = await api.post('/locations', payload);
    return res.data;
  },
};
