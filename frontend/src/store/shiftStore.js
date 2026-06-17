import { createResourceStore } from './createResourceStore';
import { shiftsService } from '../services/shiftsService';

export const useShiftsStore = createResourceStore(shiftsService);
