import { createResourceStore } from './createResourceStore';
import { holidayService } from '../services/holidayService';

export const useHolidayStore = createResourceStore(holidayService);
