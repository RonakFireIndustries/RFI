import { createResourceStore } from './createResourceStore';
import { unitService } from '../services/unitService';

export const useUnitStore = createResourceStore(unitService);
