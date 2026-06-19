import { createResourceStore } from './createResourceStore';
import { unitConversionService } from '../services/unitConversionService';

export const useUnitConversionStore = createResourceStore(unitConversionService);
