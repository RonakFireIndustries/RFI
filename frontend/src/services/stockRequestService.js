import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

export const stockRequestService = {
  list: async (params) => unwrapList((await api.get(`/stock/requests${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/stock/requests/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/stock/requests', payload)).data),
  approve: async (id) => unwrapItem((await api.post(`/stock/requests/${id}/approve`)).data),
  issue: async (id) => unwrapItem((await api.post(`/stock/requests/${id}/issue`)).data),
  receive: async (id) => unwrapItem((await api.post(`/stock/requests/${id}/receive`)).data),
};
