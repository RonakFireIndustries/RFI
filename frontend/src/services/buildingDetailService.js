import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

export const buildingDetailService = {
  getWings: async (buildingId) => unwrapList((await api.get(`/buildings/${buildingId}/wings`)).data),
  createWing: async (buildingId, payload) => unwrapItem((await api.post(`/buildings/${buildingId}/wings`, payload)).data),
  updateWing: async (buildingId, wingId, payload) => unwrapItem((await api.put(`/buildings/${buildingId}/wings/${wingId}`, payload)).data),
  removeWing: async (buildingId, wingId) => api.delete(`/buildings/${buildingId}/wings/${wingId}`),

  getFireSystems: async (buildingId) => unwrapList((await api.get(`/buildings/${buildingId}/fire-systems`)).data),
  createFireSystem: async (buildingId, payload) => unwrapItem((await api.post(`/buildings/${buildingId}/fire-systems`, payload)).data),
  updateFireSystem: async (buildingId, systemId, payload) => unwrapItem((await api.put(`/buildings/${buildingId}/fire-systems/${systemId}`, payload)).data),
  removeFireSystem: async (buildingId, systemId) => api.delete(`/buildings/${buildingId}/fire-systems/${systemId}`),

  getContacts: async (buildingId) => unwrapList((await api.get(`/buildings/${buildingId}/contacts`)).data),
  createContact: async (buildingId, payload) => unwrapItem((await api.post(`/buildings/${buildingId}/contacts`, payload)).data),
  updateContact: async (buildingId, contactId, payload) => unwrapItem((await api.put(`/buildings/${buildingId}/contacts/${contactId}`, payload)).data),
  removeContact: async (buildingId, contactId) => api.delete(`/buildings/${buildingId}/contacts/${contactId}`),

  getStatuses: async () => unwrapList((await api.get('/building-statuses')).data),
  assignStatus: async (buildingId, statusId) => unwrapItem((await api.post(`/buildings/${buildingId}/statuses`, { building_status_id: statusId })).data),
  removeStatus: async (buildingId, statusId) => api.delete(`/buildings/${buildingId}/statuses/${statusId}`),
};
