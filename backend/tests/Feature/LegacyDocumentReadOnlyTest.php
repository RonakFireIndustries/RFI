<?php

namespace Tests\Feature;

use App\Http\Middleware\BlockLegacyDocumentWrites;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * The server-side half of the legacy document retirement (slice 8).
 *
 * The retired pages (invoices, purchase orders, sales orders, payments) are
 * read-only in the UI, but the UI guard is cosmetic: what actually closes the
 * door is BlockLegacyDocumentWrites on their write routes. These tests pin
 * both directions — the writes are refused, and the reads (plus the invoice
 * email route) are left alone.
 *
 * No migrations or models are needed: the wiring is asserted against the route
 * table and the behaviour against the middleware directly.
 */
class LegacyDocumentReadOnlyTest extends TestCase
{
    /**
     * Route groups whose writes must be refused. Requests on these URIs stay
     * readable.
     */
    private const READ_ONLY_PREFIXES = [
        'api/v1/invoices',
        'api/v1/sales/orders',
        'api/v1/purchases/orders',
        'api/v1/payments',
    ];

    /** Methods that change state, i.e. everything the guard must refuse. */
    private const UNSAFE_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function test_every_legacy_write_route_is_guarded(): void
    {
        $checked = 0;

        foreach (Route::getRoutes() as $route) {
            if (! $this->isLegacyDocumentRoute($route) || $this->isInvoiceEmailRoute($route)) {
                continue;
            }

            foreach ($route->methods() as $method) {
                if (! in_array($method, self::UNSAFE_METHODS, true)) {
                    continue;
                }

                $checked++;
                $this->assertContains(
                    BlockLegacyDocumentWrites::class,
                    $route->middleware(),
                    sprintf('Expected %s %s to be read-only.', $method, $route->uri()),
                );
            }
        }

        // Guards against the prefixes silently going stale.
        $this->assertGreaterThanOrEqual(12, $checked, 'Expected to inspect the legacy write routes.');
    }

    public function test_legacy_read_routes_exist_and_are_not_blocked_by_method(): void
    {
        // index / show are the pages' read paths; they share the middleware but
        // pass because the request method is safe.
        $this->assertNotEmpty(
            collect(Route::getRoutes())->filter(
                fn (RoutingRoute $route) => $route->uri() === 'api/v1/invoices'
                    && in_array('GET', $route->methods(), true)
            )->all(),
        );
    }

    public function test_invoice_email_route_is_deliberately_not_guarded(): void
    {
        $email = collect(Route::getRoutes())->first(
            fn (RoutingRoute $route) => $this->isInvoiceEmailRoute($route)
                && in_array('POST', $route->methods(), true)
        );

        $this->assertNotNull($email, 'The invoice email route should still be registered.');
        $this->assertNotContains(BlockLegacyDocumentWrites::class, $email->middleware());
    }

    public function test_middleware_refuses_writes_and_passes_reads(): void
    {
        $middleware = new BlockLegacyDocumentWrites();

        foreach (self::UNSAFE_METHODS as $method) {
            $reached = false;

            $response = $middleware->handle(
                Request::create('/api/v1/invoices', $method),
                function () use (&$reached) {
                    $reached = true;

                    return response()->json(['ok' => true]);
                },
            );

            $this->assertFalse($reached, "{$method} should never reach the controller.");
            $this->assertSame(403, $response->getStatusCode(), "{$method} should be refused.");
            $this->assertFalse(json_decode($response->getContent(), true)['success']);
        }

        foreach (['GET', 'HEAD'] as $method) {
            $reached = false;

            $response = $middleware->handle(
                Request::create('/api/v1/invoices', $method),
                function () use (&$reached) {
                    $reached = true;

                    return response()->json(['ok' => true], 200);
                },
            );

            $this->assertTrue($reached, "{$method} should pass through.");
            $this->assertSame(200, $response->getStatusCode());
        }
    }

    private function isLegacyDocumentRoute(RoutingRoute $route): bool
    {
        foreach (self::READ_ONLY_PREFIXES as $prefix) {
            if ($route->uri() === $prefix || str_starts_with($route->uri(), $prefix . '/')) {
                return true;
            }
        }

        return false;
    }

    private function isInvoiceEmailRoute(RoutingRoute $route): bool
    {
        return str_ends_with($route->uri(), '/email');
    }
}
