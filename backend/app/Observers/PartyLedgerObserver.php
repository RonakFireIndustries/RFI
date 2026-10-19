<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\Supplier;
use App\Services\Accounting\PartyLedgerService;
use Illuminate\Support\Facades\Log;

/**
 * Creates the party ledger as soon as a customer or supplier is added, so a
 * master created through any path (the UI, a seeder, an import) is immediately
 * available for a sales or purchase voucher and for the legacy bill bridge in
 * the Outstandings report.
 *
 * Deliberately fail-soft. Creating a ledger needs the chart of accounts to be
 * seeded, and a sales desk adding a customer must not get a 500 because the
 * accountant has not run the seeder yet. The link is repaired afterwards by
 * `php artisan accounting:link-parties`, which is idempotent.
 */
class PartyLedgerObserver
{
    public function __construct(protected PartyLedgerService $partyLedgers) {}

    public function created(Customer|Supplier $party): void
    {
        try {
            $this->partyLedgers->ensure($party);
        } catch (\Throwable $e) {
            Log::warning('Could not create the party ledger.', [
                'party_type' => $party instanceof Customer ? 'customer' : 'supplier',
                'party_id' => $party->getKey(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
