import { createResourceStore } from './createResourceStore';
import api from '../services/api';
import { normalizeQuery, unwrapItem, unwrapList } from '../services/resourceHelpers';

export const cashVoucherService = {
  list: async (params) => unwrapList((await api.get(`/cash-vouchers${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/cash-vouchers/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/cash-vouchers', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/cash-vouchers/${id}`, payload)).data),
  remove: async (id) => api.delete(`/cash-vouchers/${id}`),
  approve: async (id) => unwrapItem((await api.post(`/cash-vouchers/${id}/approve`)).data),
  reject: async (id, remarks) => unwrapItem((await api.post(`/cash-vouchers/${id}/reject`, { remarks })).data),
  markPaid: async (id) => unwrapItem((await api.post(`/cash-vouchers/${id}/mark-paid`)).data),
};

export const useCashVoucherStore = createResourceStore(cashVoucherService);
