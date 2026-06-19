const API_HOST = 'https://rfibackend.ronakfire.com';
const BASE_URL = import.meta.env.DEV ? `/api/v1` : `${API_HOST}/api/v1`;
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

const getActiveBranchId = () => {
  const branchStorage = localStorage.getItem('branch-storage');
  if (branchStorage) {
    const parsed = JSON.parse(branchStorage);
    if (parsed?.state?.activeBranchId) {
      return parsed.state.activeBranchId;
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

  const branchId = getActiveBranchId();
  if (branchId) {
    headers['X-Branch-Id'] = String(branchId);
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

  const data = await response.json();
  // If backend uses the standardized wrapper { success, message, data }, unwrap it for callers
  if (data && typeof data === 'object' && Object.prototype.hasOwnProperty.call(data, 'success') && Object.prototype.hasOwnProperty.call(data, 'data')) {
    return { data: data.data, meta: { success: data.success, message: data.message } };
  }

  return { data };
};

const api = {
  get: async (endpoint) => {
    const response = await fetch(`${BASE_URL}${endpoint}`, {
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
    const response = await fetch(`${BASE_URL}${endpoint}`, {
      method: 'POST',
      headers,
      body: isFormData ? body : JSON.stringify(body),
      credentials: 'include',
    });
    return handleResponse(response);
  },

  put: async (endpoint, body) => {
    const isFormData = body instanceof FormData;
    const headers = buildHeaders();
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
