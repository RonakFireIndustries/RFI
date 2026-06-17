import { create } from 'zustand';
import api from '../services/api';

export const useDocumentStore = create((set, get) => ({
  documents: [],
  loading: false,
  error: null,
  expiringDocuments: { '30_days': [], '60_days': [], '90_days': [] },

  fetchDocuments: async (employeeId) => {
    set({ loading: true, error: null });
    try {
      const response = await api.get(`/employees/${employeeId}/documents`);
      set({ documents: response.data.data, loading: false });
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
      set((state) => ({ documents: [response.data.data, ...state.documents], loading: false }));
      return response.data.data;
    } catch (error) {
      set({ error: error.response?.data?.message || error.message, loading: false });
      throw error;
    }
  },

  updateDocument: async (id, formData) => {
    set({ loading: true, error: null });
    try {
      // For PUT with files, Laravel usually needs POST with _method=PUT, but wait, we are using fetch API, let's use POST with _method=PUT in the FormData
      formData.append('_method', 'PUT');
      const response = await api.post(`/documents/${id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      set((state) => ({
        documents: state.documents.map((doc) => (doc.id === id ? response.data.data : doc)),
        loading: false,
      }));
      return response.data.data;
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
      const response = await api.get(`/documents/${id}/download`, { responseType: 'blob' });
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      // Get filename from header if possible, else default
      const contentDisposition = response.headers['content-disposition'];
      let filename = 'document';
      if (contentDisposition) {
        const match = contentDisposition.match(/filename="?([^"]+)"?/);
        if (match && match[1]) filename = match[1];
      }
      link.setAttribute('download', filename);
      document.body.appendChild(link);
      link.click();
      link.remove();
    } catch (error) {
      console.error('Download failed', error);
      throw error;
    }
  },
  
  previewDocument: (id) => {
    return `${api.defaults.baseURL}/documents/${id}/preview`; // Helper for setting src on iframe/img
  }
}));
