import { createResourceStore } from './createResourceStore';
import { siteService } from '../services/siteService';

export const useSiteStore = createResourceStore(siteService);
