import { create } from 'zustand';

export const createResourceStore = (service) => create((set, get) => ({
  items: [],
  selected: null,
  loading: false,
  error: null,

  fetchItems: async (params) => {
    set({ loading: true, error: null });
    try {
      const items = await service.list(params);
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
      const selected = await service.get(id);
      set({ selected, loading: false });
      return selected;
    } catch (error) {
      set({ error, loading: false });
      throw error;
    }
  },

  createItem: async (payload) => {
    const item = await service.create(payload);
    set({ items: [item, ...get().items] });
    return item;
  },

  updateItem: async (id, payload) => {
    const item = await service.update(id, payload);
    set({ items: get().items.map((current) => current.id === id ? item : current) });
    return item;
  },

  deleteItem: async (id) => {
    await service.remove(id);
    set({ items: get().items.filter((item) => item.id !== id) });
  },

  clearSelected: () => set({ selected: null }),
}));
