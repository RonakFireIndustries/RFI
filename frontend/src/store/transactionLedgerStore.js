import { createResourceStore } from './createResourceStore';
import { transactionLedgerService } from '../services/transactionLedgerService';

export const useTransactionLedgerStore = createResourceStore(transactionLedgerService);
