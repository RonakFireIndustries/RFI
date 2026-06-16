import { createResourceStore } from './createResourceStore';
import { employeesService } from '../services/employeesService';

export const useEmployeesStore = createResourceStore(employeesService);
