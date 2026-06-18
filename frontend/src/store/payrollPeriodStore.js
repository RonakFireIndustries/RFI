import { createResourceStore } from './createResourceStore';
import api from '../services/api';

const payrollPeriodService = {
  list: async (params) => {
    const response = await api.get('/payroll-periods');
    return response.data;
  },
  get: async (id) => {
    const response = await api.get(`/payroll-periods/${id}`);
    return response.data;
  },
  create: async (data) => {
    const response = await api.post('/payroll-periods', data);
    return response.data;
  },
  update: async (id, data) => {
    const response = await api.put(`/payroll-periods/${id}`, data);
    return response.data;
  },
  remove: async (id) => {
    await api.delete(`/payroll-periods/${id}`);
  },
};

export const usePayrollPeriodStore = createResourceStore(payrollPeriodService);
