import { create } from 'zustand';
import { notificationService } from '../services/notificationService';

export const useNotificationStore = create((set, get) => ({
  notifications: [],
  unreadCount: 0,
  loading: false,
  error: null,
  polling: false,

  fetchNotifications: async (params) => {
    set({ loading: true, error: null });
    try {
      const notifications = await notificationService.list(params);
      set({ notifications, loading: false });
    } catch (error) {
      set({ error, loading: false });
    }
  },

  fetchUnreadCount: async () => {
    try {
      const unreadCount = await notificationService.getUnreadCount();
      set({ unreadCount });
    } catch {
      // silent fail for count
    }
  },

  markRead: async (id) => {
    await notificationService.markRead(id);
    set((state) => ({
      notifications: state.notifications.map((n) =>
        n.id === id ? { ...n, is_read: true } : n
      ),
      unreadCount: Math.max(0, state.unreadCount - 1),
    }));
  },

  markAllRead: async () => {
    await notificationService.markAllRead();
    set((state) => ({
      notifications: state.notifications.map((n) => ({ ...n, is_read: true })),
      unreadCount: 0,
    }));
  },

  deleteNotification: async (id) => {
    await notificationService.delete(id);
    const wasUnread = get().notifications.find((n) => n.id === id && !n.is_read);
    set((state) => ({
      notifications: state.notifications.filter((n) => n.id !== id),
      unreadCount: wasUnread ? Math.max(0, state.unreadCount - 1) : state.unreadCount,
    }));
  },

  startPolling: (interval = 15000) => {
    const { polling } = get();
    if (polling) return;
    set({ polling: true });
    get().fetchUnreadCount();
    const id = setInterval(() => {
      get().fetchUnreadCount();
    }, interval);
    set({ _pollInterval: id });
  },

  stopPolling: () => {
    const { _pollInterval } = get();
    if (_pollInterval) {
      clearInterval(_pollInterval);
    }
    set({ polling: false, _pollInterval: null });
  },
}));
