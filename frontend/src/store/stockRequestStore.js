import { create } from 'zustand';
import { stockRequestService } from '../services/stockRequestService';

export const useStockRequestStore = create((set, get) => ({
  items: [],
  selected: null,
  loading: false,
  error: null,

  fetchItems: async (params) => {
    set({ loading: true, error: null });
    try {
      const items = await stockRequestService.list(params);
      set({ items, loading: false });
      return items;
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  fetchItem: async (id) => {
    set({ loading: true, error: null });
    try {
      const selected = await stockRequestService.get(id);
      set({ selected, loading: false });
      return selected;
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  createItem: async (payload) => {
    const item = await stockRequestService.create(payload);
    set({ items: [item, ...get().items] });
    return item;
  },

  approve: async (id) => {
    const item = await stockRequestService.approve(id);
    set({ items: get().items.map((i) => i.id === id ? { ...i, ...item } : i), selected: item });
    return item;
  },

  issue: async (id) => {
    const item = await stockRequestService.issue(id);
    set({ items: get().items.map((i) => i.id === id ? { ...i, ...item } : i), selected: item });
    return item;
  },

  receive: async (id) => {
    const item = await stockRequestService.receive(id);
    set({ items: get().items.map((i) => i.id === id ? { ...i, ...item } : i), selected: item });
    return item;
  },

  clearSelected: () => set({ selected: null }),
}));
