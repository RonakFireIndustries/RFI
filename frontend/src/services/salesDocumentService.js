import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

export const salesDocumentService = {
  list: async (params) => unwrapList((await api.get(`/sales-documents${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/sales-documents/${id}`)).data),
  /**
   * Upload one document. Pass `{ onProgress: (percent) => ... }` to drive a
   * progress bar; XHR is used because fetch cannot report upload progress.
   */
  upload: async (formData, options) => unwrapItem((await api.upload('/sales-documents', formData, options)).data),
  remove: async (id) => api.delete(`/sales-documents/${id}`),
  downloadUrl: (id) => `/api/v1/sales-documents/${id}/download`,
};
