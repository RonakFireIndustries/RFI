const API_HOST = import.meta.env.VITE_API_URL || (
  window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1'
    ? 'http://localhost:8000'
    : 'https://rfibackend.ronakfire.com'
);

export const BASE_URL = `${API_HOST}/api/v1`;
export const STORAGE_URL = `${API_HOST}/storage`;

const getAuthToken = () => {
  const authStorage = localStorage.getItem('auth-storage');
  if (authStorage) {
    const parsed = JSON.parse(authStorage);
    if (parsed?.state?.token) {
      return parsed.state.token;
    }
  }
  return null;
};

const buildHeaders = () => {
  const headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  };

  const token = getAuthToken();
  if (token) {
    headers.Authorization = `Bearer ${token}`;
  }

  return headers;
};

const handleResponse = async (response) => {
  if (!response.ok) {
    const error = new Error('API Error');
    error.response = {
      status: response.status,
      statusText: response.statusText,
      data: await response.json().catch(() => ({})),
    };
    throw error;
  }

  // 204 No Content — no body to parse
  if (response.status === 204) {
    return { data: null };
  }

  const data = await response.json();
  // If backend uses the standardized wrapper { success, message, data }, unwrap it for callers
  if (data && typeof data === 'object' && Object.prototype.hasOwnProperty.call(data, 'success') && Object.prototype.hasOwnProperty.call(data, 'data')) {
    return { data: data.data, meta: { success: data.success, message: data.message } };
  }

  return { data };
};

const buildBlobHeaders = () => {
  const headers = {};
  const token = getAuthToken();
  if (token) {
    headers.Authorization = `Bearer ${token}`;
  }
  return headers;
};

const buildQueryString = (params) => {
  if (!params) return '';
  const entries = Object.entries(params).filter(([, v]) => v !== undefined && v !== null && v !== '');
  if (entries.length === 0) return '';
  return '?' + new URLSearchParams(entries).toString();
};

const api = {
  get: async (endpoint, config) => {
    const query = buildQueryString(config?.params);
    const response = await fetch(`${BASE_URL}${endpoint}${query}`, {
      method: 'GET',
      headers: buildHeaders(),
      credentials: 'include',
    });
    return handleResponse(response);
  },

  post: async (endpoint, body) => {
    const isFormData = body instanceof FormData;
    const headers = buildHeaders();
    if (isFormData) {
      delete headers['Content-Type'];
    }
    try {
      const response = await fetch(`${BASE_URL}${endpoint}`, {
        method: 'POST',
        headers,
        body: isFormData ? body : JSON.stringify(body),
        credentials: 'include',
      });
      return handleResponse(response);
    } catch (error) {
      if (!window.navigator.onLine || error.message === 'Failed to fetch') {
        if (!isFormData && endpoint !== '/login' && endpoint !== '/logout') {
          const { enqueueRequest } = await import('./offlineQueue');
          await enqueueRequest({ url: `${BASE_URL}${endpoint}`, method: 'POST', headers, body: JSON.stringify(body) });
          return { data: { offline: true, message: 'Request queued for offline sync' }, meta: { success: true } };
        } else if (error.message === 'Failed to fetch') {
          throw new Error('Network error. Unable to connect to the server (or CORS issue).');
        }
      }
      throw error;
    }
  },

  put: async (endpoint, body) => {
    const isFormData = body instanceof FormData;
    const headers = buildHeaders();

    try {
      if (isFormData) {
        delete headers['Content-Type'];
        body.append('_method', 'PUT');
        const response = await fetch(`${BASE_URL}${endpoint}`, {
          method: 'POST',
          headers,
          body,
          credentials: 'include',
        });
        return handleResponse(response);
      }

      const response = await fetch(`${BASE_URL}${endpoint}`, {
        method: 'PUT',
        headers,
        body: JSON.stringify(body),
        credentials: 'include',
      });
      return handleResponse(response);
    } catch (error) {
      if (!window.navigator.onLine || error.message === 'Failed to fetch') {
        if (!isFormData) {
          const { enqueueRequest } = await import('./offlineQueue');
          await enqueueRequest({ url: `${BASE_URL}${endpoint}`, method: 'PUT', headers, body: JSON.stringify(body) });
          return { data: { offline: true, message: 'Request queued for offline sync' }, meta: { success: true } };
        }
      }
      throw error;
    }
  },

  getBlob: async (endpoint) => {
    const response = await fetch(`${BASE_URL}${endpoint}`, {
      method: 'GET',
      headers: buildBlobHeaders(),
      credentials: 'include',
    });
    if (!response.ok) {
      const error = new Error('API Error');
      error.response = { status: response.status, statusText: response.statusText };
      throw error;
    }
    return response.blob();
  },

  delete: async (endpoint) => {
    const response = await fetch(`${BASE_URL}${endpoint}`, {
      method: 'DELETE',
      headers: buildHeaders(),
      credentials: 'include',
    });
    return handleResponse(response);
  },

  setAuthHeader: (token) => {
    // This is called by authStore when token is set, but our fetch wrapper
    // reads from localStorage directly, so no action needed here
  },

  removeAuthHeader: () => {
    // This is called by authStore when logging out
    // Our fetch wrapper will automatically pick up the cleared localStorage
  },
};

export default api;
