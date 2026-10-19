<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Closes the write endpoints of the retired legacy documents.
 *
 * The sales invoices, purchase orders and sales orders were re-keyed as
 * Tally-style vouchers (`accounting:migrate-legacy`), which are now the system
 * of record. Their pages are read-only in the UI, but the API behind them was
 * still writable, which made the retirement cosmetic. This makes it real: any
 * unsafe request to a guarded route is refused.
 *
 * Safe requests are untouched, so historic documents stay viewable, printable
 * and emailable. The guarding is applied per-route in routes/api.php rather
 * than globally, so unrelated resources are never affected.
 *
 * To re-open the old flow, remove this middleware from those route groups and
 * flip LEGACY_DOCUMENTS_READONLY in frontend/src/config/legacyDocuments.js.
 */
class BlockLegacyDocumentWrites
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethodSafe()) {
            return response()->json([
                'success' => false,
                'message' => 'Legacy documents are read-only. Record sales and purchases as vouchers instead.',
                'data' => [],
            ], 403);
        }

        return $next($request);
    }
}
