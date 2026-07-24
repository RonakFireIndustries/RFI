import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

export const salesDocumentService = {
  list: async (params) => unwrapList((await api.get(`/sales-documents${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/sales-documents/${id}`)).data),
  upload: async (formData) => unwrapItem((await api.post('/sales-documents', formData, { headers: { 'Content-Type': 'multipart/form-data' } })).data),
  remove: async (id) => api.delete(`/sales-documents/${id}`),
  downloadUrl: (id) => `/api/v1/sales-documents/${id}/download`,
};
