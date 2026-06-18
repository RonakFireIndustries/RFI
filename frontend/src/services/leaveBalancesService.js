import api from './api';
import { normalizeQuery } from './resourceHelpers';

export const leaveBalancesService = {
  getAll: (params) => api.get(`/leave-balances${normalizeQuery(params)}`),
  initialize: (employeeId, year) => api.post(`/employees/${employeeId}/leave-balances/initialize`, { year }),
};
