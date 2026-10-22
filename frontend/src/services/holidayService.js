import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

export const holidayService = {
  list: async (params) => unwrapList((await api.get(`/holidays${normalizeQuery(params)}`)).data),
  years: async () => {
    const res = (await api.get('/holidays/years')).data;
    return res?.data?.years || res?.years || [];
  },
  get: async (id) => unwrapItem((await api.get(`/holidays/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/holidays', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/holidays/${id}`, payload)).data),
  remove: async (id) => api.delete(`/holidays/${id}`),
};
