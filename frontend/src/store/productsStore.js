import { createResourceStore } from './createResourceStore';
import { productsService } from '../services/productsService';

export const useProductsStore = createResourceStore(productsService);
