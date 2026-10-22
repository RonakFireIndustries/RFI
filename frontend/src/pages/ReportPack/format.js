import { formatDate } from '../../utils/date';
import { formatAmount, formatMoney } from '../../utils/finance';

/**
 * Value formatting for the module report pack.
 *
 * Every report in the application is described by the same handful of column
 * formats - text, number, money, percent, date, datetime, badge, boolean -
 * and this file is the one place that knows what each of them looks like on
 * screen. The backend uses the same names (see App\Services\Reports\
 * ModuleReportService), so a report never has to say how to render itself.
 */

/** Shown wherever a value is missing. */
export const EMPTY = '—';

/** Colours for the status chips. */
const BADGE_TONES = {
  active: 'bg-green-50 text-green-700 border-green-200',
  approved: 'bg-green-50 text-green-700 border-green-200',
  present: 'bg-green-50 text-green-700 border-green-200',
  posted: 'bg-green-50 text-green-700 border-green-200',
  completed: 'bg-green-50 text-green-700 border-green-200',
  received: 'bg-green-50 text-green-700 border-green-200',
  resolved: 'bg-green-50 text-green-700 border-green-200',
  closed: 'bg-gray-100 text-gray-700 border-gray-200',
  inactive: 'bg-gray-100 text-gray-700 border-gray-200',
  draft: 'bg-yellow-50 text-yellow-700 border-yellow-200',
  pending: 'bg-yellow-50 text-yellow-700 border-yellow-200',
  requested: 'bg-yellow-50 text-yellow-700 border-yellow-200',
  submitted: 'bg-yellow-50 text-yellow-700 border-yellow-200',
  open: 'bg-yellow-50 text-yellow-700 border-yellow-200',
  in_progress: 'bg-blue-50 text-blue-700 border-blue-200',
  processing: 'bg-blue-50 text-blue-700 border-blue-200',
  issued: 'bg-blue-50 text-blue-700 border-blue-200',
  sent: 'bg-blue-50 text-blue-700 border-blue-200',
  rejected: 'bg-red-50 text-red-700 border-red-200',
  cancelled: 'bg-red-50 text-red-700 border-red-200',
  failed: 'bg-red-50 text-red-700 border-red-200',
  overdue: 'bg-red-50 text-red-700 border-red-200',
  critical: 'bg-red-50 text-red-700 border-red-200',
  high: 'bg-orange-50 text-orange-700 border-orange-200',
  medium: 'bg-yellow-50 text-yellow-700 border-yellow-200',
  low: 'bg-gray-100 text-gray-700 border-gray-200',
};

/** The chip colour for a status word, or a neutral one when it is unknown. */
export const badgeTone = (value) => {
  const key = String(value ?? '').trim().toLowerCase().replace(/\s+/g, '_');
  return BADGE_TONES[key] || 'bg-gray-100 text-gray-700 border-gray-200';
};

/**
 * A number, with the application's digit grouping.
 *
 * Whole numbers lose their decimals - a count of 5 is "5", not "5.00" - and
 * everything else keeps two.
 */
export const formatNumber = (value) => {
  if (value === null || value === undefined || value === '') return EMPTY;

  const numeric = Number(value);
  if (!Number.isFinite(numeric)) return String(value);

  return Number.isInteger(numeric) ? formatAmount(numeric, 0) : formatAmount(numeric, 2);
};

/**
 * Render one cell of a report table.
 */
export const formatCell = (value, format = 'text') => {
  if (value === null || value === undefined || value === '') return EMPTY;

  switch (format) {
    case 'money':
      return formatMoney(value, { decimals: 2 });

    case 'percent':
      return `${formatAmount(value, 2)}%`;

    case 'number':
      return formatNumber(value);

    case 'boolean':
      return value ? 'Yes' : 'No';

    case 'date':
      return formatDate(value);

    case 'datetime':
      return formatDateTime(value);

    default:
      return String(value);
  }
};

/**
 * Render a timestamp, keeping the book's own date (see utils/date).
 */
export const formatDateTime = (value) => {
  if (!value) return EMPTY;

  const text = String(value);
  const [date, time = ''] = text.split(' ');

  return `${formatDate(date)}${time ? ` ${time.slice(0, 5)}` : ''}`;
};

/**
 * The value of a headline figure. Money and percentages keep their units;
 * counts stay plain numbers.
 */
export const formatSummaryValue = (value, format = 'number') => {
  if (value === null || value === undefined || value === '') return EMPTY;

  if (format === 'money') return formatMoney(value, { decimals: 2 });
  if (format === 'percent') return `${formatAmount(value, 2)}%`;

  return formatNumber(value);
};
