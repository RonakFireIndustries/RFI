import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

/**
 * Order tracking (operational): orders, their line items, the status
 * timeline and the invoice link.
 *
 * There is deliberately no accounting call here — orders never touch the
 * ledger. `advance` walks an order one stage along
 * `open → in_progress → fulfilled → invoiced → done` (passing
 * `document_type` / `document_id` when moving into `invoiced`), and `cancel`
 * is the only other exit. Both are gated by the `orders` access module.
 */
export const ordersService = {
  list: async (params) => unwrapList((await api.get(`/orders${normalizeQuery(params)}`)).data),
  get: async (id) => unwrapItem((await api.get(`/orders/${id}`)).data),
  create: async (payload) => unwrapItem((await api.post('/orders', payload)).data),
  update: async (id, payload) => unwrapItem((await api.put(`/orders/${id}`, payload)).data),
  advance: async (id, payload = {}) => unwrapItem((await api.post(`/orders/${id}/advance`, payload)).data),
  cancel: async (id, payload = {}) => unwrapItem((await api.post(`/orders/${id}/cancel`, payload)).data),
};

export default ordersService;
