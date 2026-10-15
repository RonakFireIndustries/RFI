/**
 * Helpers shared by every document upload UI (Buildings PMC files, the Sales
 * building Documents tab, ...).
 */

/** Largest single file the API is configured to accept (backend `max:20480`). */
export const MAX_UPLOAD_BYTES = 20 * 1024 * 1024;

export const formatBytes = (bytes) => {
  const n = Number(bytes);
  if (!Number.isFinite(n) || n <= 0) return '0 KB';
  if (n < 1024 * 1024) return `${Math.max(1, Math.round(n / 1024))} KB`;
  return `${(n / (1024 * 1024)).toFixed(1)} MB`;
};

/**
 * Human-readable reason an upload failed.
 *
 * Laravel answers validation failures with `{ message, errors: {...} }`. A bare
 * 413 (or a network-level failure) usually means the body never reached Laravel
 * because it exceeded the server's PHP `upload_max_filesize` / `post_max_size`,
 * so we explain that instead of surfacing "API Error".
 */
export const uploadErrorMessage = (err) => {
  const data = err?.response?.data;
  const firstFieldError = data?.errors && Object.values(data.errors).flat().find(Boolean);
  if (firstFieldError) return firstFieldError;
  if (data?.message) return data.message;

  switch (err?.response?.status) {
    case 401:
    case 403:
      return 'You do not have permission to upload documents here.';
    case 413:
      return 'The file is too large for the server to accept.';
    case 0:
      return 'The upload could not reach the server — the file may exceed the server upload limit.';
    default:
      return err?.message || 'Upload failed.';
  }
};

/** Reject files larger than the documented limit before starting the request. */
export const findOversizeFile = (files) =>
  Array.from(files || []).find((file) => file.size > MAX_UPLOAD_BYTES) || null;
