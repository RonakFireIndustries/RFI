import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

export const productStockService = {
  list: async (params) => unwrapList((await api.get(`/stock${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/stock/${id}`)).data),
  byProduct: async (productId) => unwrapList((await api.get(`/stock/by-product/${productId}`)).data),
  byLocation: async (locationType, locationId) => {
    return unwrapList((await api.get(`/stock/by-location/site/${locationId}`)).data);
  },
};
