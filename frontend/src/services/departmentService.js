import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

export const departmentService = {
  list: async (params) => unwrapList((await api.get(`/departments${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/departments/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/departments', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/departments/${id}`, payload)).data),
  remove: async (id) => api.delete(`/departments/${id}`),
};
