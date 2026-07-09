import api from './api';

export const notificationService = {
  list: async (params) => {
    const res = await api.get('/notifications', { params });
    return res.data?.data ?? res.data ?? [];
  },

  getUnreadCount: async () => {
    const res = await api.get('/notifications/unread-count');
    return res.data?.count ?? res.data ?? 0;
  },

  markRead: async (id) => {
    await api.put(`/notifications/${id}/read`);
  },

  markAllRead: async () => {
    await api.put('/notifications/read-all');
  },

  delete: async (id) => {
    await api.delete(`/notifications/${id}`);
  },

  send: async (payload) => {
    const res = await api.post('/notifications/send', payload);
    return res.data;
  },
};
