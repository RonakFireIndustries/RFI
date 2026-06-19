import api from './api';
import { normalizeQuery } from './resourceHelpers';

export const inventoryDashboardService = {
  get: async (params) => (await api.get(`/inventory/dashboard${normalizeQuery(params)}`)).data,
};
