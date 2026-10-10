import { create } from 'zustand';

export const createResourceStore = (service) => create((set, get) => ({
  items: [],
  selected: null,
  loading: false,
  saving: false,
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
    set({ saving: true, error: null });
    try {
      const result = await service.create(payload);
      const item = result?.employee ?? result;
      set({ items: [item, ...get().items], saving: false });
      return result;
    } catch (error) {
      set({ error, saving: false });
      throw error;
    }
  },

  updateItem: async (id, payload) => {
    set({ saving: true, error: null });
    try {
      const item = await service.update(id, payload);
      set({ items: get().items.map((current) => current.id === id ? item : current), saving: false });
      return item;
    } catch (error) {
      set({ error, saving: false });
      throw error;
    }
  },

  deleteItem: async (id) => {
    set({ saving: true, error: null });
    try {
      await service.remove(id);
      set({ items: get().items.filter((item) => item.id !== id), saving: false });
    } catch (error) {
      set({ error, saving: false });
      throw error;
    }
  },

  clearSelected: () => set({ selected: null }),
}));
