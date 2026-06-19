import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

export const unitConversionService = {
  list: async (params) => unwrapList((await api.get(`/unit-conversions${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/unit-conversions/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/unit-conversions', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/unit-conversions/${id}`, payload)).data),
  remove: async (id) => api.delete(`/unit-conversions/${id}`),
  convert: async (from, to, quantity) => unwrapItem((await api.get(`/unit-conversions/convert/${from}/${to}/${quantity}`)).data),
};
