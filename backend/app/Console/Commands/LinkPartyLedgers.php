<?php

namespace App\Console\Commands;

use App\Services\Accounting\PartyLedgerService;
use Illuminate\Console\Command;

/**
 * Backfill for masters created before party ledgers existed, and the repair
 * tool when the observer had to fail soft (the chart of accounts was not
 * seeded when the master was added).
 */
class LinkPartyLedgers extends Command
{
    protected $signature = 'accounting:link-parties {--dry-run : Report what would change without writing}';

    protected $description = 'Create a party ledger (Sundry Debtors / Sundry Creditors) for every customer and supplier that has none.';

    public function handle(PartyLedgerService $partyLedgers): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $summary = $partyLedgers->linkAll($dryRun);

        $prefix = $dryRun ? '[DRY RUN] ' : '';
        $verb = $dryRun ? 'would be created' : 'created';

        $this->info("{$prefix}Customers: {$summary['customer']['created']} ledger(s) {$verb}, {$summary['customer']['existing']} already linked.");
        $this->info("{$prefix}Suppliers: {$summary['supplier']['created']} ledger(s) {$verb}, {$summary['supplier']['existing']} already linked.");

        if ($summary['errors'] !== []) {
            $this->newLine();
            $this->warn('Skipped:');

            foreach ($summary['errors'] as $error) {
                $this->line("  - {$error}");
            }

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
