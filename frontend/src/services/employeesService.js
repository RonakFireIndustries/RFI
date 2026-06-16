import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

export const employeesService = {
  list: async (params) => unwrapList((await api.get(`/employees${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/employees/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/employees', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/employees/${id}`, payload)).data),
  remove: async (id) => api.delete(`/employees/${id}`),
};
