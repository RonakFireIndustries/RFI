import api from './api';
import { normalizeQuery, unwrapItem, unwrapList } from './resourceHelpers';

/**
 * Goods receipts and deliveries against item vouchers.
 *
 * A purchase voucher is the bill; a receipt records the goods that actually
 * arrived for it (once, or in instalments). Sales vouchers use the same shape
 * with the movement pointing outwards — `mode` in each payload says which.
 */
export const stockReceiptService = {
  /** The receipt register, optionally narrowed to one voucher or party. */
  list: async (params) => unwrapList((await api.get(`/stock-receipts${normalizeQuery(params)}`)).data),

  get: async (id) => unwrapItem((await api.get(`/stock-receipts/${id}`)).data),

  /** Ordered / received / pending per line, plus the dated receipt history. */
  forVoucher: async (voucherId) =>
    unwrapItem((await api.get(`/vouchers/${voucherId}/receipts`)).data),

  /**
   * Record a delivery.
   *
   * @param {number} voucherId
   * @param {{receipt_date: string, location_type?: string, location_id?: number,
   *          supplier_bill_no?: string, supplier_bill_date?: string, notes?: string,
   *          items: Array<{voucher_item_id: number, quantity: number, batch_no?: string}>}} payload
   */
  create: async (voucherId, payload) =>
    unwrapItem((await api.post(`/vouchers/${voucherId}/receipts`, payload)).data),

  /** Reverse a receipt (kept for audit, counter-movement written). */
  remove: async (id, reason) => api.delete(`/stock-receipts/${id}${normalizeQuery({ reason })}`),
};

export default stockReceiptService;
