/**
 * Shared helpers for rendering leave requests, so the dashboard widgets and the
 * Leave Management pages agree on statuses, dates and badges.
 *
 * `Submitted` is what the app writes when a request is filed; `Pending` is the
 * column default on older rows. Both are still awaiting a decision.
 */
export const PENDING_STATUSES = ['Submitted', 'Pending'];

const MONTH_NAMES = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

/**
 * Dates come back as 'YYYY-MM-DD'; splitting the string avoids the UTC shift
 * that `new Date('YYYY-MM-DD')` applies in non-UTC timezones.
 */
export const formatLeaveDate = (value) => {
  if (!value) return '-';
  const [year, month, day] = String(value).slice(0, 10).split('-');
  if (!year || !month || !day) return String(value);
  return `${day} ${MONTH_NAMES[Number(month) - 1]} ${year}`;
};

export const employeeName = (employee) =>
  employee?.full_name
  || [employee?.first_name, employee?.last_name].filter(Boolean).join(' ')
  || employee?.name
  || 'Unknown employee';

export const statusBadgeClass = (status) => {
  if (status === 'Approved') return 'bg-green-100 text-green-800';
  if (status === 'Rejected') return 'bg-red-100 text-red-800';
  if (PENDING_STATUSES.includes(status)) return 'bg-yellow-100 text-yellow-800';
  return 'bg-gray-100 text-gray-800';
};
