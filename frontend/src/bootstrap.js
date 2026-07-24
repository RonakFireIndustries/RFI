import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

let echoInstance = null;

export function getEcho() {
  if (echoInstance) return echoInstance;

  echoInstance = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: `${import.meta.env.VITE_API_URL || 'http://localhost:8000'}/broadcasting/auth`,
    auth: {
      headers: {
        Authorization: `Bearer ${getToken()}`,
        Accept: 'application/json',
      },
    },
  });

  return echoInstance;
}

function getToken() {
  try {
    const authStorage = localStorage.getItem('auth-storage');
    if (authStorage) {
      const parsed = JSON.parse(authStorage);
      return parsed?.state?.token || '';
    }
  } catch {
    return '';
  }
  return '';
}
