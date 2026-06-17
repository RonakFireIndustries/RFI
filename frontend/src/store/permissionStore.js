import api from '../services/api';
import { createResourceStore } from './createResourceStore';

const permissionService = {
  list: async () => {
    const response = await api.get('/permissions');
    return response.data;
  },
  get: async (id) => {
    const response = await api.get(`/permissions/${id}`);
    return response.data;
  },
  create: async (data) => {
    const response = await api.post('/permissions', data);
    return response.data;
  },
  update: async (id, data) => {
    const response = await api.put(`/permissions/${id}`, data);
    return response.data;
  },
  remove: async (id) => {
    await api.delete(`/permissions/${id}`);
  },
};

export const usePermissionStore = createResourceStore(permissionService);
