import { createResourceStore } from './createResourceStore';
import {
  accountGroupService,
  ledgerService,
  financialYearService,
  voucherService,
} from '../services/accountingService';

/*
 * Zustand resource stores for the accounting foundation (slice 1).
 * Each exposes the standard { items, loading, saving, error, fetchItems,
 * createItem, updateItem, deleteItem } shape from createResourceStore.
 */

export const useAccountGroupStore = createResourceStore(accountGroupService);
export const useLedgerStore = createResourceStore(ledgerService);
export const useFinancialYearStore = createResourceStore(financialYearService);
export const useVoucherStore = createResourceStore(voucherService);
