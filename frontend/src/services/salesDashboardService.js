import api from './api';
import { unwrapItem } from './resourceHelpers';

export const salesDashboardService = {
  getStats: async () => unwrapItem((await api.get('/sales/dashboard')).data),
};
