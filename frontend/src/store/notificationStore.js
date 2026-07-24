import { create } from 'zustand';
import { notificationService } from '../services/notificationService';
import { getEcho } from '../bootstrap';

let echoChannel = null;

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

  addNotification: (notification) => {
    set((state) => ({
      notifications: [notification, ...state.notifications],
      unreadCount: state.unreadCount + (notification.is_read ? 0 : 1),
    }));
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

  listenForNotifications: (userId) => {
    if (!userId) return;

    try {
      const echo = getEcho();
      echoChannel = echo.private(`user.${userId}`);

      echoChannel.listen('.notification.created', (event) => {
        get().addNotification({
          id: event.id || Date.now(),
          title: event.title,
          message: event.message,
          type: event.type,
          link: event.link,
          is_read: false,
          created_at: event.created_at || new Date().toISOString(),
        });
      });
    } catch (err) {
      console.warn('WebSocket notification listener failed, falling back to polling:', err);
    }
  },

  stopListeningForNotifications: (userId) => {
    if (echoChannel) {
      try {
        const echo = getEcho();
        echo.leave(`user.${userId}`);
      } catch {
        // ignore cleanup errors
      }
      echoChannel = null;
    }
  },

  startPolling: (interval = 30000) => {
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
