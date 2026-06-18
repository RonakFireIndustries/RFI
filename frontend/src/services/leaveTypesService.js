import api from './api';
import { normalizeQuery } from './resourceHelpers';

export const leaveTypesService = {
  getAll: (params) => api.get(`/leave-types${normalizeQuery(params)}`),
  getById: (id) => api.get(`/leave-types/${id}`),
  create: (data) => api.post('/leave-types', data),
  update: (id, data) => api.put(`/leave-types/${id}`, data),
  delete: (id) => api.delete(`/leave-types/${id}`),
};
