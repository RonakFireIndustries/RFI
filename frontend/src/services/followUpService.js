import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

export const followUpService = {
  list: async (params) => unwrapList((await api.get(`/follow-ups${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/follow-ups/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/follow-ups', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/follow-ups/${id}`, payload)).data),
  remove: async (id) => api.delete(`/follow-ups/${id}`),
  markComplete: async (id) => unwrapItem((await api.post(`/follow-ups/${id}/complete`)).data),
  myFollowUps: async () => unwrapList((await api.get('/my-follow-ups')).data),
};
