<?php

namespace App\Services\Accounting;

use App\Models\AccountGroup;
use App\Models\Customer;
use App\Models\LedgerAccount;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * Keeps every customer and supplier linked to a party ledger.
 *
 * The accounting side is entirely ledger-driven: a voucher posts against a
 * LedgerAccount, and a bill is a line on one. The legacy sales and purchase
 * documents are keyed on customer_id / supplier_id instead, so `party_type` +
 * `party_id` on the ledger is the only bridge between the two worlds. Without
 * it the Outstandings report cannot see a single legacy invoice or purchase
 * order, and there is nothing for a new sales voucher to post against.
 *
 * Ledgers are created in the standard party groups — Sundry Debtors for
 * customers, Sundry Creditors for suppliers — resolved from the `party_type`
 * flag on account_groups rather than the group name, so ChartOfAccountsSeeder
 * stays the single source of truth for what those groups look like.
 */
class PartyLedgerService
{
    /**
     * Find the ledger already linked to this master, if any.
     */
    public function for(Model $party): ?LedgerAccount
    {
        ['type' => $type] = $this->typeFor($party);

        return LedgerAccount::where('party_type', $type)
            ->where('party_id', $party->getKey())
            ->first();
    }

    /**
     * Return the party's ledger, creating it when the master has none.
     *
     * Idempotent — running it twice never creates a second ledger.
     */
    public function ensure(Model $party): LedgerAccount
    {
        ['type' => $type, 'label' => $label] = $this->typeFor($party);

        return $this->for($party) ?? LedgerAccount::create([
            'name' => $this->uniqueName((string) $party->name, $label, $type, $party->getKey()),
            'group_id' => $this->partyGroup($type)->id,
            'party_type' => $type,
            'party_id' => $party->getKey(),
            'status' => 'active',
        ]);
    }

    /**
     * Backfill every master that has no ledger yet.
     *
     * Safe to re-run: masters already linked are counted and skipped, so the
     * command can be part of a routine upgrade without a second thought.
     *
     * @return array{customer: array{created: int, existing: int}, supplier: array{created: int, existing: int}, errors: list<string>}
     */
    public function linkAll(bool $dryRun = false): array
    {
        // One error that would hit every row — check it once, up front.
        $this->partyGroup('customer');
        $this->partyGroup('supplier');

        $summary = [
            'customer' => ['created' => 0, 'existing' => 0],
            'supplier' => ['created' => 0, 'existing' => 0],
            'errors' => [],
        ];

        foreach ([Customer::class, Supplier::class] as $model) {
            $type = $model === Customer::class ? 'customer' : 'supplier';

            foreach ($model::query()->orderBy('id')->get() as $party) {
                if ($this->for($party)) {
                    $summary[$type]['existing']++;

                    continue;
                }

                if ($dryRun) {
                    $summary[$type]['created']++;

                    continue;
                }

                try {
                    $this->ensure($party);
                    $summary[$type]['created']++;
                } catch (\Throwable $e) {
                    // One odd master must not abort the whole backfill.
                    $summary['errors'][] = "{$type} #{$party->getKey()} ({$party->name}): {$e->getMessage()}";
                }
            }
        }

        return $summary;
    }

    /**
     * @return array{type: string, label: string}
     */
    protected function typeFor(Model $party): array
    {
        return match (true) {
            $party instanceof Customer => ['type' => 'customer', 'label' => 'Customer'],
            $party instanceof Supplier => ['type' => 'supplier', 'label' => 'Supplier'],
            default => throw new RuntimeException('Party ledgers are only linked for customers and suppliers.'),
        };
    }

    /**
     * The party group a ledger of this type belongs to.
     *
     * @throws RuntimeException when the chart of accounts has not been seeded.
     */
    protected function partyGroup(string $type): AccountGroup
    {
        $group = AccountGroup::where('is_party_group', true)
            ->where('party_type', $type)
            ->orderBy('id')
            ->first();

        if (! $group) {
            throw new RuntimeException(
                "No {$type} party group is set up. Run `php artisan db:seed --class=ChartOfAccountsSeeder` first."
            );
        }

        return $group;
    }

    /**
     * Party names are free text and ledger names are globally unique, so a
     * customer and a supplier can share a name, and either can collide with a
     * system ledger such as "Cash". Fall back to a qualified name, then a
     * numeric suffix, rather than failing the caller.
     */
    protected function uniqueName(string $name, string $label, string $type, int $partyId): string
    {
        $name = trim($name);

        if ($name === '') {
            $name = "{$label} #{$partyId}";
        }

        if (! $this->nameTaken($name)) {
            return $name;
        }

        $qualified = "{$name} ({$label})";

        if (! $this->nameTaken($qualified)) {
            return $qualified;
        }

        for ($n = 2; $n < 1000; $n++) {
            if (! $this->nameTaken("{$qualified} {$n}")) {
                return "{$qualified} {$n}";
            }
        }

        throw new RuntimeException("Could not find a free ledger name for party {$type} #{$partyId}.");
    }

    protected function nameTaken(string $name): bool
    {
        return LedgerAccount::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->exists();
    }
}
