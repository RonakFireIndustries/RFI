import api from './api';

export const leaveBalancesService = {
  getAll: (params) => api.get('/leave-balances', { params }),
  initialize: (employeeId, year) => api.post(`/employees/${employeeId}/leave-balances/initialize`, { year }),
};
