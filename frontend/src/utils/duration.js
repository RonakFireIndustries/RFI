// Helpers for rendering and parsing work durations as "3h 22m".
//
// The database stores hours as a decimal (e.g. 3.37), but the DPR screens
// present the exact duration to the user, so these two functions are the only
// place that converts between the two representations.

const pad2 = (n) => String(n).padStart(2, '0');

/**
 * A "YYYY-MM-DD" string for the user's *local* day.
 *
 * `new Date().toISOString()` is UTC, which rolls over to the previous day for
 * users ahead of UTC (e.g. IST) during the early hours. Dates entered on a form
 * should follow the user's wall clock, so build it from local parts.
 */
export const todayLocalISO = () => {
  const d = new Date();
  return `${d.getFullYear()}-${pad2(d.getMonth() + 1)}-${pad2(d.getDate())}`;
};

/** Format decimal hours as "3h 22m". Returns '' for empty/invalid input. */
export const formatHours = (value) => {
  if (value === null || value === undefined || value === '') return '';
  const n = Number(value);
  if (!Number.isFinite(n)) return '';
  const totalMinutes = Math.round(n * 60);
  const hours = Math.floor(totalMinutes / 60);
  const minutes = totalMinutes % 60;
  return `${hours}h ${pad2(minutes)}m`;
};

/** Format a duration in decimal hours, or '--' when there is nothing to show. */
export const formatHoursOrDash = (value) => formatHours(value) || '--';

/**
 * Parse user input into decimal hours. Accepts "3h 22m", "3h22", "3h", "3:22",
 * "22m" and plain numbers (treated as hours). Returns NaN when unparseable.
 */
export const parseDurationToHours = (input) => {
  if (input === null || input === undefined) return NaN;
  const raw = String(input).trim().toLowerCase();
  if (!raw) return NaN;

  // "3h 22m" / "3h22" / "3h"
  const hm = raw.match(/^(\d+(?:\.\d+)?)\s*h(?:\s*(\d+(?:\.\d+)?)\s*m?)?$/);
  if (hm) return Number(hm[1]) + (hm[2] ? Number(hm[2]) / 60 : 0);

  // "22m" / "22 min"
  const mins = raw.match(/^(\d+(?:\.\d+)?)\s*m(?:in(?:utes?)?)?$/);
  if (mins) return Number(mins[1]) / 60;

  // "3:22"
  const colon = raw.match(/^(\d+):([0-5]?\d)$/);
  if (colon) return Number(colon[1]) + Number(colon[2]) / 60;

  // Bare number -> hours.
  const n = Number(raw);
  return Number.isFinite(n) ? n : NaN;
};

/** Local wall-clock time of a Date/ISO string, e.g. "09:08". */
export const formatClockTime = (value) => {
  if (!value) return '';
  const d = value instanceof Date ? value : new Date(value);
  if (Number.isNaN(d.getTime())) return '';
  return `${pad2(d.getHours())}:${pad2(d.getMinutes())}`;
};
