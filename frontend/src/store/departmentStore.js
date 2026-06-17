import { createResourceStore } from './createResourceStore';
import { departmentService } from '../services/departmentService';

export const useDepartmentStore = createResourceStore(departmentService);
