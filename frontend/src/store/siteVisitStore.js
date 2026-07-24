import { createResourceStore } from './createResourceStore';
import { siteVisitService } from '../services/siteVisitService';

export const useSiteVisitStore = createResourceStore(siteVisitService);
