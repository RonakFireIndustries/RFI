import api from './api';

export const chatService = {
  getChannels: async () => {
    const res = await api.get('/chat/channels');
    return res.data?.channels ?? [];
  },

  createChannel: async (payload) => {
    const res = await api.post('/chat/channels', payload);
    return res.data?.channel;
  },

  addMembers: async (channelId, memberIds) => {
    const res = await api.post(`/chat/channels/${channelId}/members`, { member_ids: memberIds });
    return res.data?.channel;
  },

  removeMember: async (channelId, userId) => {
    await api.delete(`/chat/channels/${channelId}/members/${userId}`);
  },

  getMessages: async (channelId, params = {}) => {
    const query = new URLSearchParams(params).toString();
    const res = await api.get(`/chat/channels/${channelId}/messages${query ? '?' + query : ''}`);
    return { messages: res.data?.messages ?? [], hasMore: res.data?.has_more ?? false };
  },

  sendMessage: async (channelId, payload) => {
    const isFormData = payload instanceof FormData;
    const res = await api.post(`/chat/channels/${channelId}/messages`, payload);
    return res.data?.message;
  },

  markRead: async (channelId) => {
    await api.post(`/chat/channels/${channelId}/read`);
  },

  sendTyping: async (channelId) => {
    await api.post(`/chat/channels/${channelId}/typing`);
  },

  getMembers: async (channelId) => {
    const res = await api.get(`/chat/channels/${channelId}/members`);
    return res.data?.members ?? [];
  },
};
