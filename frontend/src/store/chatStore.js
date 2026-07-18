import { create } from 'zustand';
import { chatService } from '../services/chatService';

export const useChatStore = create((set, get) => ({
  channels: [],
  activeChannel: null,
  messages: [],
  hasMoreMessages: true,
  loadingChannels: false,
  loadingMessages: false,
  typingUsers: {},
  onlineUsers: {},
  allUsers: [],

  fetchChannels: async () => {
    set({ loadingChannels: true });
    try {
      const channels = await chatService.getChannels();
      set({ channels, loadingChannels: false });
    } catch (error) {
      set({ loadingChannels: false });
      throw error;
    }
  },

  setActiveChannel: async (channel) => {
    set({ activeChannel: channel, messages: [], hasMoreMessages: true });
    if (channel) {
      await get().loadMessages(channel.id);
      await chatService.markRead(channel.id);
      set((state) => ({
        channels: state.channels.map(c =>
          c.id === channel.id ? { ...c, unread_count: 0 } : c
        ),
      }));
    }
  },

  loadMessages: async (channelId, beforeId) => {
    set({ loadingMessages: true });
    try {
      const { messages, has_more } = await chatService.getMessages(channelId, beforeId ? { before_id: beforeId, per_page: 50 } : { per_page: 50 });
      set((state) => ({
        messages: beforeId ? [...messages, ...state.messages] : messages,
        hasMoreMessages: has_more,
        loadingMessages: false,
      }));
    } catch (error) {
      set({ loadingMessages: false });
    }
  },

  addMessage: (message) => {
    set((state) => {
      const exists = state.messages.some(m => m.id === message.id);
      if (exists) return state;
      return { messages: [...state.messages, message] };
    });

    set((state) => ({
      channels: state.channels.map(c => {
        if (c.id === message.chat_channel_id) {
          return {
            ...c,
            last_message: {
              id: message.id,
              message: message.message,
              user_name: message.user?.name,
              created_at: message.created_at,
            },
            unread_count: state.activeChannel?.id === message.chat_channel_id
              ? c.unread_count
              : (message.is_mine ? c.unread_count : c.unread_count + 1),
          };
        }
        return c;
      }),
    }));
  },

  updateReadReceipts: (messageId, userId, userName, readAt) => {
    set((state) => ({
      messages: state.messages.map(m => {
        if (m.id === messageId) {
          const alreadyRead = m.reads?.some(r => r.user_id === userId);
          if (alreadyRead) return m;
          return {
            ...m,
            reads: [...(m.reads || []), { user_id: userId, user_name: userName, read_at: readAt }],
          };
        }
        return m;
      }),
    }));
  },

  setTypingUser: (channelId, userId, userName) => {
    set((state) => ({
      typingUsers: {
        ...state.typingUsers,
        [channelId]: {
          ...(state.typingUsers[channelId] || {}),
          [userId]: { name: userName, timestamp: Date.now() },
        },
      },
    }));

    setTimeout(() => {
      set((state) => {
        const channelTyping = { ...(state.typingUsers[channelId] || {}) };
        if (channelTyping[userId] && Date.now() - channelTyping[userId].timestamp > 3000) {
          delete channelTyping[userId];
        }
        return { typingUsers: { ...state.typingUsers, [channelId]: channelTyping } };
      });
    }, 3500);
  },

  clearTypingUser: (channelId, userId) => {
    set((state) => {
      const channelTyping = { ...(state.typingUsers[channelId] || {}) };
      delete channelTyping[userId];
      return { typingUsers: { ...state.typingUsers, [channelId]: channelTyping } };
    });
  },

  createChannel: async (payload) => {
    const channel = await chatService.createChannel(payload);
    set((state) => ({ channels: [channel, ...state.channels] }));
    return channel;
  },

  sendMessage: async (channelId, payload) => {
    return await chatService.sendMessage(channelId, payload);
  },

  sendTyping: async (channelId) => {
    await chatService.sendTyping(channelId);
  },
}));
