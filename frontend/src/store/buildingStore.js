import { createResourceStore } from './createResourceStore';
import { buildingService } from '../services/buildingService';

export const useBuildingStore = createResourceStore(buildingService);
