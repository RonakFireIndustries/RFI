import api from './api';
import { normalizeQuery } from './resourceHelpers';

const extractList = (response) => {
  const body = response?.data;
  if (!body) return [];
  if (Array.isArray(body)) return body;
  for (const key of Object.keys(body)) {
    const val = body[key];
    if (Array.isArray(val)) return val;
    if (Array.isArray(val?.data)) return val.data;
  }
  return [];
};

export const attendancesService = {
  list: async (params = {}) => extractList(await api.get(`/attendances${normalizeQuery(params)}`)),
  get: async (id) => (await api.get(`/attendances/${id}`))?.data?.data || (await api.get(`/attendances/${id}`))?.data,
  create: async (payload) => (await api.post('/attendances', payload))?.data?.data,
  update: async (id, payload) => (await api.put(`/attendances/${id}`, payload))?.data?.data,
  remove: async (id) => api.delete(`/attendances/${id}`),
  checkIn: async (payload) => (await api.post('/attendance/check-in', payload)).data,
  checkOut: async (payload) => (await api.post('/attendance/check-out', payload)).data,
  myAttendance: async (params = {}) => extractList(await api.get(`/my-attendance${normalizeQuery(params)}`)),
  locationAudit: async (params = {}) => extractList(await api.get(`/attendance/location-audit${normalizeQuery(params)}`)),
};
