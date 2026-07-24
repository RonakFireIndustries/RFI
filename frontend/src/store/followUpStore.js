import { createResourceStore } from './createResourceStore';
import { followUpService } from '../services/followUpService';

export const useFollowUpStore = createResourceStore(followUpService);
