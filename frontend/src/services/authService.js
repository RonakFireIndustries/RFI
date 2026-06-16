import api from './api';

export const authService = {
  login: async (credentials) => {
    return await api.post('/login', credentials);
  },
  
  logout: async () => {
    return await api.post('/logout', {});
  },
  
  getUser: async () => {
    return await api.get('/user');
  },
  
  updateProfile: async (data) => {
    return await api.put('/profile', data);
  },
  
  forgotPassword: async (email) => {
    return await api.post('/forgot-password', { email });
  },
  
  resetPassword: async (data) => {
    return await api.post('/reset-password', data);
  }
};
