<?php

namespace App\Exceptions;

use Exception;

/**
 * Raised when a document asks for more goods than are physically available, or
 * for more than is still outstanding on a purchase.
 *
 * The message is written for the person at the counter ("Only 4.000 of pump
 * assembly are still pending"), so it is surfaced verbatim with a 422 rather
 * than a generic 500.
 */
class StockQuantityException extends Exception
{
    public function render()
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], 422);
    }
}
