import { createResourceStore } from './createResourceStore';
import { inventoryService } from '../services/inventoryService';

export const useInventoryStore = createResourceStore(inventoryService);
