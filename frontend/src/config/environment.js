export const ENVIRONMENT = 'development'; // 'production' or 'development'

const environments = {
  production: {
    API_URL: 'https://rfibackend.ronakfire.com',
    PUSHER_APP_KEY: 'c81c2da2537855f4f133',
    PUSHER_APP_CLUSTER: 'ap2',
    GOOGLE_MAPS_API_KEY: 'AIzaSyBTvx-6baPhjsaJQb2VMnwqVG_J0DSv15o',
  },
  development: {
    API_URL: 'http://localhost:8000',
    PUSHER_APP_KEY: 'c81c2da2537855f4f133',
    PUSHER_APP_CLUSTER: 'ap2',
    GOOGLE_MAPS_API_KEY: 'AIzaSyBTvx-6baPhjsaJQb2VMnwqVG_J0DSv15o',
  },
};

export const config = environments[ENVIRONMENT] ?? environments.development;
