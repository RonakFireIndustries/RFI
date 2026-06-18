import api from './api';
import { normalizeQuery } from './resourceHelpers';

export const leaveRequestsService = {
  getAll: (params) => api.get(`/leave-requests${normalizeQuery(params)}`),
  getById: (id) => api.get(`/leave-requests/${id}`),
  create: (data) => api.post('/leave-requests', data),
  update: (id, data) => api.put(`/leave-requests/${id}`, data),
  delete: (id) => api.delete(`/leave-requests/${id}`),
  approve: (id, data) => api.post(`/leave-requests/${id}/approve`, data),
  reject: (id, data) => api.post(`/leave-requests/${id}/reject`, data),
  cancel: (id, data) => api.post(`/leave-requests/${id}/cancel`, data),
};
