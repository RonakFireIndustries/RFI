import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

export const buildingService = {
  list: async (params) => unwrapList((await api.get(`/buildings${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/buildings/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/buildings', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/buildings/${id}`, payload)).data),
  remove: async (id) => api.delete(`/buildings/${id}`),
  /**
   * Promote a building into a working site.
   *
   * Idempotent - converting an already-converted building refreshes the site
   * it produced instead of creating a second one.
   */
  convertToSite: async (id) => unwrapItem((await api.post(`/buildings/${id}/convert-to-site`)).data),
};
