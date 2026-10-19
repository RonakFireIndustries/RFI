<?php

namespace App\Console\Commands;

use App\Services\Accounting\LegacyDocumentMigrator;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * Moves the legacy sales and purchase documents into the books.
 *
 * Run it once when the accounting module goes live: invoices become sales
 * vouchers, purchase orders become purchase vouchers, and each party's
 * outstanding balance is carried across unchanged. Re-running it is safe — a
 * document that already has a voucher is skipped.
 */
class MigrateLegacyDocuments extends Command
{
    protected $signature = 'accounting:migrate-legacy
        {--dry-run : Report what would be created without writing anything}
        {--rollback : Delete the vouchers a previous run created and unlink the documents}';

    protected $description = 'Re-key legacy invoices and purchase orders as sales / purchase vouchers, keeping every party\'s outstanding balance unchanged.';

    public function handle(LegacyDocumentMigrator $migrator): int
    {
        if ($this->option('dry-run') && $this->option('rollback')) {
            $this->error('Use either --dry-run or --rollback, not both.');

            return Command::FAILURE;
        }

        try {
            return $this->option('rollback')
                ? $this->rollback($migrator)
                : $this->migrate($migrator, (bool) $this->option('dry-run'));
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }

    protected function migrate(LegacyDocumentMigrator $migrator, bool $dryRun): int
    {
        $summary = $migrator->migrate($dryRun);
        $prefix = $dryRun ? '[DRY RUN] ' : '';

        $this->info("{$prefix}Invoices:        {$summary['invoice']['migrated']} migrated, {$summary['invoice']['skipped']} already migrated.");
        $this->info("{$prefix}Purchase orders: {$summary['purchase-order']['migrated']} migrated, {$summary['purchase-order']['skipped']} already migrated.");

        $this->line("{$prefix}Settlements:     {$summary['settlement']['journals']} journal(s) totalling "
            . number_format($summary['settlement']['amount'], 2) . " posted against \"" . $summary['suspense_ledger'] . '".');

        foreach (['invoice', 'purchase-order'] as $type) {
            $statuses = $summary['statuses'][$type] ?? [];

            if ($statuses === []) {
                continue;
            }

            $parts = [];
            foreach ($statuses as $status => $count) {
                $parts[] = "{$status} {$count}";
            }

            $this->line("{$prefix}  {$type}: " . implode(', ', $parts));
        }

        if ($summary['residual']['count'] > 0) {
            $this->newLine();
            $this->warn($summary['residual']['count'] . ' document(s) had a header total that disagreed with their line items by more than 1.00 ('
                . number_format($summary['residual']['amount'], 2) . ' was absorbed as round off). Those are worth a look.');
        }

        if ($summary['shipping']['count'] > 0) {
            $this->warn($summary['shipping']['count'] . ' purchase order(s) carry freight / other cost totalling '
                . number_format($summary['shipping']['amount'], 2) . ', which is not part of the payable — add it to the voucher if the supplier billed it.');
        }

        if ($summary['settlement']['clamped'] > 0) {
            $this->warn($summary['settlement']['clamped'] . ' document(s) were marked paid for more than they were worth; the excess was ignored.');
        }

        $this->reportErrors($summary);

        return $this->hasErrors($summary) ? Command::FAILURE : Command::SUCCESS;
    }

    protected function rollback(LegacyDocumentMigrator $migrator): int
    {
        $summary = $migrator->rollback();

        $this->info("Removed {$summary['invoice']} sales voucher(s) and {$summary['purchase-order']} purchase voucher(s), and unlinked their documents.");
        $this->info("Removed {$summary['settlement']} settlement journal(s).");

        if ($summary['errors'] !== []) {
            $this->newLine();
            $this->warn('Failed:');

            foreach ($summary['errors'] as $error) {
                $this->line("  - {$error}");
            }

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $summary
     */
    protected function reportErrors(array $summary): void
    {
        $lines = array_merge(
            array_map(static fn ($error): string => "invoice: {$error}", $summary['invoice']['errors']),
            array_map(static fn ($error): string => "purchase order: {$error}", $summary['purchase-order']['errors']),
            array_map(static fn ($error): string => "settlement: {$error}", $summary['settlement']['errors']),
        );

        if ($lines === []) {
            return;
        }

        $this->newLine();
        $this->warn('Skipped:');

        foreach ($lines as $line) {
            $this->line("  - {$line}");
        }
    }

    /**
     * @param  array<string, mixed>  $summary
     */
    protected function hasErrors(array $summary): bool
    {
        return $summary['invoice']['errors'] !== []
            || $summary['purchase-order']['errors'] !== []
            || $summary['settlement']['errors'] !== [];
    }
}
