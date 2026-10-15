import { config } from '../config/environment';

// Live backend host. To use a local backend instead, set VITE_API_HOST in
// frontend/.env.local (e.g. VITE_API_HOST=http://localhost:8000) and restart Vite.
const API_HOST = import.meta.env.VITE_API_HOST || config.API_URL;
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

/*
 * In-flight mutation dedupe.
 *
 * Double-clicking a submit button (or a slow network) could otherwise fire the
 * same POST/PUT/DELETE twice and create duplicate records. While an identical
 * mutation is pending we return its existing promise instead of starting a new
 * request, so the same form can never submit the same data twice concurrently.
 */
const inFlightMutations = new Map();

const buildMutationKey = (method, endpoint, body) => {
  if (body instanceof FormData) {
    return `${method} ${endpoint} [formdata]`;
  }
  try {
    return `${method} ${endpoint} ${JSON.stringify(body ?? null)}`;
  } catch {
    return `${method} ${endpoint}`;
  }
};

const dedupeMutation = (key, factory) => {
  if (inFlightMutations.has(key)) {
    return inFlightMutations.get(key);
  }
  const promise = Promise.resolve()
    .then(factory)
    .finally(() => {
      inFlightMutations.delete(key);
    });
  inFlightMutations.set(key, promise);
  return promise;
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
    return dedupeMutation(buildMutationKey('POST', endpoint, body), async () => {
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
    });
  },

  put: async (endpoint, body) => {
    return dedupeMutation(buildMutationKey('PUT', endpoint, body), async () => {
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
    });
  },

  /*
   * FormData upload with progress reporting.
   *
   * `fetch` cannot report upload progress, so this uses XMLHttpRequest. It
   * resolves with the same { data, meta } shape as the other methods, and
   * reports 0-100 via options.onProgress. It deliberately bypasses the
   * in-flight dedupe used by post(): distinct files in a multi-file upload are
   * separate requests, and a shared key would collapse them into one.
   */
  upload: (endpoint, formData, { onProgress } = {}) =>
    new Promise((resolve, reject) => {
      const headers = buildHeaders();
      delete headers['Content-Type']; // let the browser add the multipart boundary

      const xhr = new XMLHttpRequest();
      xhr.open('POST', `${BASE_URL}${endpoint}`);
      xhr.withCredentials = true;
      Object.entries(headers).forEach(([key, value]) => xhr.setRequestHeader(key, value));

      if (xhr.upload && typeof onProgress === 'function') {
        xhr.upload.onprogress = (event) => {
          if (event.lengthComputable) {
            onProgress(Math.round((event.loaded / event.total) * 100), event.loaded, event.total);
          }
        };
      }

      xhr.onload = () => {
        let parsed = {};
        try {
          parsed = xhr.responseText ? JSON.parse(xhr.responseText) : {};
        } catch {
          parsed = {};
        }

        if (xhr.status < 200 || xhr.status >= 300) {
          const error = new Error(parsed?.message || 'Upload failed');
          error.response = { status: xhr.status, statusText: xhr.statusText, data: parsed };
          reject(error);
          return;
        }

        if (parsed && typeof parsed === 'object' && Object.prototype.hasOwnProperty.call(parsed, 'success') && Object.prototype.hasOwnProperty.call(parsed, 'data')) {
          resolve({ data: parsed.data, meta: { success: parsed.success, message: parsed.message } });
          return;
        }
        resolve({ data: parsed });
      };

      xhr.onerror = () => {
        const error = new Error('Network error. Unable to connect to the server (or CORS issue).');
        error.response = { status: 0, statusText: 'Network Error', data: {} };
        reject(error);
      };

      xhr.onabort = () => reject(new Error('Upload cancelled'));

      xhr.send(formData);
    }),

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
    return dedupeMutation(buildMutationKey('DELETE', endpoint), async () => {
      const response = await fetch(`${BASE_URL}${endpoint}`, {
        method: 'DELETE',
        headers: buildHeaders(),
        credentials: 'include',
      });
      return handleResponse(response);
    });
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
