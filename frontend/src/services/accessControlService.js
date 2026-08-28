import api from './api';

export const accessControlService = {
  definitions: () => api.get('/access-control/definitions'),
  users: () => api.get('/access-control/users'),
  userPermissions: (userId) => api.get(`/access-control/users/${userId}/permissions`),
  updatePermissions: (userId, permissions) =>
    api.put(`/access-control/users/${userId}/permissions`, { permissions }),
  grantPermission: (userId, permission) =>
    api.post(`/access-control/users/${userId}/permissions/grant`, { permission }),
  revokePermission: (userId, permission) =>
    api.post(`/access-control/users/${userId}/permissions/revoke`, { permission }),
};
