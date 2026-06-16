const BASE_URL = 'http://localhost:8000/api/v1';
const SANCTUM_URL = 'http://localhost:8000';

class ApiError extends Error {
  constructor(message, status, data) {
    super(message);
    this.status = status;
    this.data = data;
  }
}

const getHeaders = () => {
  const token = localStorage.getItem('token');
  const headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  };
  
  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }
  
  return headers;
};

export const apiFetch = async (endpoint, options = {}) => {
  try {
    const url = endpoint.startsWith('http') ? endpoint : `${BASE_URL}${endpoint}`;
    
    const response = await fetch(url, {
      ...options,
      headers: {
        ...getHeaders(),
        ...options.headers,
      },
    });

    // Check for 401 Unauthorized globally
    if (response.status === 401) {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      window.dispatchEvent(new Event('auth:unauthorized'));
    }

    const data = await response.json().catch(() => null);

    if (!response.ok) {
      throw new ApiError(
        data?.message || 'An error occurred',
        response.status,
        data
      );
    }

    return data;
  } catch (error) {
    if (error instanceof ApiError) throw error;
    throw new Error(error.message || 'Network request failed');
  }
};

export const api = {
  // Sanctum CSRF Cookie
  getCsrfCookie: () => fetch(`${SANCTUM_URL}/sanctum/csrf-cookie`, { method: 'GET' }),
  
  get: (endpoint, options) => apiFetch(endpoint, { method: 'GET', ...options }),
  post: (endpoint, data, options) => apiFetch(endpoint, { method: 'POST', body: JSON.stringify(data), ...options }),
  put: (endpoint, data, options) => apiFetch(endpoint, { method: 'PUT', body: JSON.stringify(data), ...options }),
  delete: (endpoint, options) => apiFetch(endpoint, { method: 'DELETE', ...options }),
};
