import { createResourceStore } from './createResourceStore';
import { customersService } from '../services/customersService';

export const useCustomersStore = createResourceStore(customersService);
