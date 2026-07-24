import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

export const siteVisitService = {
  list: async (params) => unwrapList((await api.get(`/site-visits${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/site-visits/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/site-visits', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/site-visits/${id}`, payload)).data),
  remove: async (id) => api.delete(`/site-visits/${id}`),
  uploadPhotos: async (id, formData) => unwrapItem((await api.post(`/site-visits/${id}/photos`, formData, { headers: { 'Content-Type': 'multipart/form-data' } })).data),
  deletePhoto: async (siteVisitId, photoId) => api.delete(`/site-visits/${siteVisitId}/photos/${photoId}`),
  uploadVoiceNotes: async (id, formData) => unwrapItem((await api.post(`/site-visits/${id}/voice-notes`, formData, { headers: { 'Content-Type': 'multipart/form-data' } })).data),
  deleteVoiceNote: async (siteVisitId, noteId) => api.delete(`/site-visits/${siteVisitId}/voice-notes/${noteId}`),
};
