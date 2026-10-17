import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

/**
 * Tally-style double-entry accounting API.
 *
 * Slice 1 covers the chart of accounts (groups + ledgers) and financial years.
 * Vouchers, BRS and reports are added by later slices.
 */

export const accountGroupService = {
  list: async (params) => unwrapList((await api.get(`/account-groups${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/account-groups/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/account-groups', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/account-groups/${id}`, payload)).data),
  remove: async (id) => api.delete(`/account-groups/${id}`),
};

export const ledgerService = {
  list: async (params) => unwrapList((await api.get(`/ledgers${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/ledgers/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/ledgers', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/ledgers/${id}`, payload)).data),
  remove: async (id) => api.delete(`/ledgers/${id}`),
};

export const financialYearService = {
  list: async (params) => unwrapList((await api.get(`/financial-years${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/financial-years/${id}`)).data),
  current: async () => unwrapItem((await api.get('/financial-years/current')).data),
  create: async (payload) => unwrapItem((await api.post('/financial-years', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/financial-years/${id}`, payload)).data),
  activate: async (id) => unwrapItem((await api.post(`/financial-years/${id}/activate`)).data),
  remove: async (id) => api.delete(`/financial-years/${id}`),
};

/**
 * Tally-style vouchers (slice 2): Contra, Payment, Receipt and Journal.
 * `partyOutstanding` feeds the bill-wise panel of party vouchers.
 */
export const voucherService = {
  list: async (params) => unwrapList((await api.get(`/vouchers${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/vouchers/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/vouchers', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/vouchers/${id}`, payload)).data),
  remove: async (id) => api.delete(`/vouchers/${id}`),
  partyOutstanding: async (ledgerId) =>
    unwrapItem((await api.get(`/vouchers/party-outstanding/${ledgerId}`)).data),
};
