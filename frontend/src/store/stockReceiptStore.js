import { create } from 'zustand';
import { stockReceiptService } from '../services/stockReceiptService';

/**
 * Receipts for one voucher at a time.
 *
 * Receipts are always read and written in the context of a document (there is
 * no standalone "new receipt" screen), so the store keys everything on the
 * voucher it was last loaded for and exposes `summary` as the shape the UI
 * actually renders: per-line received / pending plus the receipt history.
 */
export const useStockReceiptStore = create((set) => ({
  voucherId: null,
  summary: null,
  items: [],
  loading: false,
  saving: false,
  error: null,

  /** Ordered / received / pending lines plus the receipt history. */
  fetchForVoucher: async (voucherId) => {
    set({ loading: true, error: null, voucherId });
    try {
      const summary = await stockReceiptService.forVoucher(voucherId);
      set({ summary, items: summary?.receipts || [], loading: false });
      return summary;
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  /**
   * Record a delivery and fold the returned summary back in, so the caller
   * never has to refetch to redraw the pending quantities.
   */
  createReceipt: async (voucherId, payload) => {
    set({ saving: true, error: null });
    try {
      const result = await stockReceiptService.create(voucherId, payload);
      const summary = result?.summary || result;
      set({ summary, items: summary?.receipts || [], saving: false });
      return result;
    } catch (error) {
      set({ error, saving: false });
      throw error;
    }
  },

  reverseReceipt: async (receiptId, reason) => {
    set({ saving: true, error: null });
    try {
      const result = await stockReceiptService.remove(receiptId, reason);
      const summary = result?.summary || result;
      set({ summary, items: summary?.receipts || summary || [], saving: false });
      return summary;
    } catch (error) {
      set({ error, saving: false });
      throw error;
    }
  },

  clear: () => set({ voucherId: null, summary: null, items: [], error: null }),
}));
