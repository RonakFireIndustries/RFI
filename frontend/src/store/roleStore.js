import api from '../services/api';
import { createResourceStore } from './createResourceStore';

const roleService = {
  list: async () => {
    const response = await api.get('/roles');
    return response.data;
  },
  get: async (id) => {
    const response = await api.get(`/roles/${id}`);
    return response.data;
  },
  create: async (data) => {
    const response = await api.post('/roles', data);
    return response.data;
  },
  update: async (id, data) => {
    const response = await api.put(`/roles/${id}`, data);
    return response.data;
  },
  remove: async (id) => {
    await api.delete(`/roles/${id}`);
  },
};

export const useRoleStore = createResourceStore(roleService);
