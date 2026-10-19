/**
 * Book-date formatting.
 *
 * Dates like `2026-10-18` reach the UI as bare strings (or ISO timestamps).
 * Parsing them with `new Date()` treats a date-only string as UTC midnight, so
 * anywhere west of Greenwich the screen shows the day *before* the voucher
 * date. Splitting the parts off the string keeps the books' own dates intact.
 */

/**
 * Format a `YYYY-MM-DD` string (an ISO timestamp also works) as `DD/MM/YYYY`.
 *
 * @param {string|number|null|undefined} value
 * @param {string} fallback returned for an empty value
 * @returns {string}
 */
export const formatDate = (value, fallback = '—') => {
  if (!value) return fallback;

  const [year, month, day] = String(value).slice(0, 10).split('-');
  if (!year || !month || !day) return String(value);

  return `${day}/${month}/${year}`;
};
