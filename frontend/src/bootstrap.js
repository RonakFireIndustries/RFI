import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { config } from './config/environment';

window.Pusher = Pusher;

let echoInstance = null;
let echoToken = null;

/**
 * The bearer token the API client is using right now.
 *
 * Read from the same persisted store that services/api.js reads, so Echo and
 * the REST calls can never disagree about who is signed in.
 */
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

export function hasAuthToken() {
  return Boolean(getToken());
}

/**
 * Authorize a private/presence channel against Laravel.
 *
 * pusher-js normally does this itself from `authEndpoint` + `auth.headers`,
 * but that captures the Authorization header once, when the Echo instance is
 * built. A token that is rotated (logout followed by login without a full
 * page reload) then keeps being sent, and every channel authorization comes
 * back 401 while ordinary API calls - which rebuild their headers per request
 * - keep working.
 *
 * Reading the token inside the authorizer instead means it is always the
 * current one. It also lets us fail loudly with a useful message rather than
 * sending "Bearer " and getting an opaque 401 back.
 */
function authorizer(channel) {
  return {
    authorize: (socketId, callback) => {
      const token = getToken();

      if (!token) {
        callback(new Error('Not signed in - cannot authorize a private channel.'), null);
        return;
      }

      fetch(`${config.API_URL}/broadcasting/auth`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
          socket_id: socketId,
          channel_name: channel.name,
        }),
      })
        .then(async (response) => {
          const body = await response.json().catch(() => ({}));

          if (!response.ok) {
            // 401 here means the backend rejected the token, not that the
            // channel was refused (that is a 403 from the channel callback).
            callback(
              new Error(body?.message || `Channel authorization failed (${response.status}).`),
              null
            );
            return;
          }

          callback(null, body);
        })
        .catch((error) => callback(error, null));
    },
  };
}

/**
 * The shared Echo client.
 *
 * Rebuilt whenever the bearer token changes, so a new session never keeps
 * talking to the websocket server with the previous session's credentials.
 */
export function getEcho() {
  const token = getToken();

  if (echoInstance && token === echoToken) {
    return echoInstance;
  }

  if (echoInstance) {
    resetEcho();
  }

  echoToken = token;

  echoInstance = new Echo({
    broadcaster: 'pusher',
    key: config.PUSHER_APP_KEY,
    cluster: config.PUSHER_APP_CLUSTER,
    forceTLS: true,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: `${config.API_URL}/broadcasting/auth`,
    authorizer,
  });

  return echoInstance;
}

/**
 * Drop the current Echo client. Call this on logout so the next session
 * starts from a clean websocket connection and a fresh token.
 */
export function resetEcho() {
  if (echoInstance) {
    try {
      echoInstance.disconnect();
    } catch {
      // Nothing useful to do if the socket was already gone.
    }
  }

  echoInstance = null;
  echoToken = null;
}
