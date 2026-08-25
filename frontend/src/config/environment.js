const isLocalhost = typeof window !== 'undefined' && (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1');

export const ENVIRONMENT = isLocalhost ? 'development' : 'production';

const environments = {
  production: {
    API_URL: 'https://rfibackend.ronakfire.com',
    PUSHER_APP_KEY: 'c81c2da2537855f4f133',
    PUSHER_APP_CLUSTER: 'ap2',
    GOOGLE_MAPS_API_KEY: 'AIzaSyBTvx-6baPhjsaJQb2VMnwqVG_J0DSv15o',
  },
  development: {
    API_URL: '',
    PUSHER_APP_KEY: 'c81c2da2537855f4f133',
    PUSHER_APP_CLUSTER: 'ap2',
    GOOGLE_MAPS_API_KEY: 'AIzaSyBTvx-6baPhjsaJQb2VMnwqVG_J0DSv15o',
  },
};

export const config = environments[ENVIRONMENT] ?? environments.development;
