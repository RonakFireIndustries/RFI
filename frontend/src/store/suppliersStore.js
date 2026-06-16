import { createResourceStore } from './createResourceStore';
import { suppliersService } from '../services/suppliersService';

export const useSuppliersStore = createResourceStore(suppliersService);
