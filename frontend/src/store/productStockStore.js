import { createResourceStore } from './createResourceStore';
import { productStockService } from '../services/productStockService';

export const useProductStockStore = createResourceStore(productStockService);
