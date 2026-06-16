import React, { useEffect } from 'react';
import { useAuthStore } from '../../store/authStore';
import { useBranchStore } from '../../store/branchStore';
import api from '../../services/api';

export default function BranchSelector() {
  const { roles } = useAuthStore();
  const { activeBranchId, availableBranches, setActiveBranch, setAvailableBranches } = useBranchStore();

  const isAdmin = roles?.includes('Super Admin') || roles?.includes('Admin');

  useEffect(() => {
    if (isAdmin) {
      api.get('/branches')
        .then((response) => {
          setAvailableBranches(Array.isArray(response.data) ? response.data : response.data.data || []);
        })
        .catch((error) => console.error("Failed to load branches", error));
    }
  }, [isAdmin, setAvailableBranches]);

  if (!isAdmin) return null;

  return (
    <div className="flex items-center ml-4 border-l border-gray-200 pl-4">
      <label htmlFor="branch-select" className="sr-only">Select Warehouse</label>
      <select
        id="branch-select"
        className="block w-48 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-[#1a56db] focus:border-[#1a56db] sm:text-sm rounded-md"
        value={activeBranchId || ''}
        onChange={(e) => setActiveBranch(e.target.value ? parseInt(e.target.value, 10) : null)}
      >
        <option value="">Global (All Branches)</option>
        {availableBranches.map((branch) => (
          <option key={branch.id} value={branch.id}>
            {branch.name}
          </option>
        ))}
      </select>
    </div>
  );
}
