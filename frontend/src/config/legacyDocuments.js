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
 * controls. The matching API routes are closed server-side too (see
 * App\Http\Middleware\BlockLegacyDocumentWrites), so re-opening the old flow
 * means flipping this constant *and* dropping that middleware from the route
 * groups in routes/api.php.
 */
export const LEGACY_DOCUMENTS_READONLY = true;
