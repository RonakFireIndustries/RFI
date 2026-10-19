/*
 * Retirement of the legacy Purchases / Sales / Invoices documents.
 *
 * `php artisan accounting:migrate-legacy` re-keyed every legacy sales invoice,
 * purchase order and sales order as a Tally-style voucher, so those documents
 * are no longer the system of record. Their pages are kept reachable by URL —
 * historic records stay viewable and printable — but they are read-only:
 * new sales, purchases and notes are entered as vouchers under Finance.
 *
 * The pages read this flag to decide whether to offer their create/mutate
 * controls. Hiding them is a UX guard only; the matching API routes are still
 * writable, so keep this `true` outside of a deliberate, temporary re-open
 * (e.g. while verifying the migration) and flip it straight back.
 */
export const LEGACY_DOCUMENTS_READONLY = true;
