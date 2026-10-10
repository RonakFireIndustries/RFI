import api from './api';

export const dashboardService = {
  getDashboard: async () => {
    const response = await api.get('/dashboard');
    return response.data;
  },
  getMyDashboard: async () => {
    const response = await api.get('/dashboard/my');
    return response.data;
  },
};
