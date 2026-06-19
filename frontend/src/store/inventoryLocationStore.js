import { createResourceStore } from './createResourceStore';
import { inventoryLocationService } from '../services/inventoryLocationService';

export const useInventoryLocationStore = createResourceStore(inventoryLocationService);
