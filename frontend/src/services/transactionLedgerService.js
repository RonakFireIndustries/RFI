import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

export const transactionLedgerService = {
  list: async (params) => unwrapList((await api.get(`/stock/transactions${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/stock/transactions/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/stock/transactions', payload)).data),
  summary: async (params) => (await api.get(`/stock/transactions-summary${normalizeQuery(params)}`)).data,
};
