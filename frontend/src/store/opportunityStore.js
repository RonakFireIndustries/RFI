import { createResourceStore } from './createResourceStore';
import { opportunityService } from '../services/opportunityService';

export const useOpportunityStore = createResourceStore(opportunityService);
