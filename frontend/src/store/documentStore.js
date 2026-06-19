import { create } from 'zustand';
import api, { BASE_URL } from '../services/api';

export const useDocumentStore = create((set, get) => ({
  documents: [],
  loading: false,
  error: null,
  expiringDocuments: { '30_days': [], '60_days': [], '90_days': [] },

  fetchDocuments: async (employeeId) => {
    set({ loading: true, error: null });
    try {
      const response = await api.get(`/employees/${employeeId}/documents`);
      const raw = response.data.data;
      set({ documents: Array.isArray(raw) ? raw : (raw?.data ?? []), loading: false });
    } catch (error) {
      set({ error: error.message, loading: false });
    }
  },

  uploadDocument: async (employeeId, formData) => {
    set({ loading: true, error: null });
    try {
      const response = await api.post(`/employees/${employeeId}/documents`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      const doc = response.data.data?.data ?? response.data.data;
      set((state) => ({ documents: [doc, ...state.documents].filter(Boolean), loading: false }));
      return doc;
    } catch (error) {
      set({ error: error.response?.data?.message || error.message, loading: false });
      throw error;
    }
  },

  updateDocument: async (id, formData) => {
    set({ loading: true, error: null });
    try {
      formData.append('_method', 'PUT');
      const response = await api.post(`/documents/${id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      const doc = response.data.data?.data ?? response.data.data;
      set((state) => ({
        documents: state.documents.map((d) => (d.id === id ? doc : d)),
        loading: false,
      }));
      return doc;
    } catch (error) {
      set({ error: error.response?.data?.message || error.message, loading: false });
      throw error;
    }
  },

  deleteDocument: async (id) => {
    set({ loading: true, error: null });
    try {
      await api.delete(`/documents/${id}`);
      set((state) => ({
        documents: state.documents.filter((doc) => doc.id !== id),
        loading: false,
      }));
    } catch (error) {
      set({ error: error.message, loading: false });
      throw error;
    }
  },

  fetchExpiringDocuments: async () => {
    set({ loading: true, error: null });
    try {
      const response = await api.get('/documents/expiring');
      set({ expiringDocuments: response.data.data, loading: false });
    } catch (error) {
      set({ error: error.message, loading: false });
    }
  },

  downloadDocument: async (id) => {
    try {
      const blob = await api.getBlob(`/documents/${id}/download`);
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', 'document');
      document.body.appendChild(link);
      link.click();
      link.remove();
    } catch (error) {
      console.error('Download failed', error);
      throw error;
    }
  },
  
  previewDocument: (id) => {
    return `${BASE_URL}/documents/${id}/preview`;
  }
}));
