import { createResourceStore } from './createResourceStore';
import { designationService } from '../services/designationService';

export const useDesignationStore = createResourceStore(designationService);
