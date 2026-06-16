const BASE_URL = 'http://127.0.0.1:8000/api';

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
    const response = await fetch(`${BASE_URL}${endpoint}`, {
      method: 'POST',
      headers: buildHeaders(),
      body: JSON.stringify(body),
      credentials: 'include',
    });
    return handleResponse(response);
  },

  put: async (endpoint, body) => {
    const response = await fetch(`${BASE_URL}${endpoint}`, {
      method: 'PUT',
      headers: buildHeaders(),
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
