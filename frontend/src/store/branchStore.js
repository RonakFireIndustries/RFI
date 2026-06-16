import { create } from 'zustand';
import { persist } from 'zustand/middleware';

export const useBranchStore = create(
  persist(
    (set) => ({
      activeBranchId: null,
      availableBranches: [],
      
      setActiveBranch: (branchId) => {
        set({ activeBranchId: branchId });
      },
      
      setAvailableBranches: (branches) => {
        set({ availableBranches: branches });
      },

      clearBranchData: () => {
        set({ activeBranchId: null, availableBranches: [] });
      }
    }),
    {
      name: 'branch-storage',
    }
  )
);
