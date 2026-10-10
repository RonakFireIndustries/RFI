<?php

namespace App\Services;

use App\Models\Building;
use App\Models\Site;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Promotes a survey building into a fully working Site.
 *
 * The two records overlap but are not equivalent: a `sites` row drives
 * attendance, geo-fencing and stock movements, while a `buildings` row holds
 * the fire-safety and structural survey. Converting copies the survey across
 * so the resulting Site is self-contained, then mirrors the link onto the
 * building's detail children so they are addressable by site as well.
 *
 * The building is left untouched and keeps `building_id` on every pipeline
 * record (site visits, opportunities, follow-ups, quotations), so conversion
 * is additive and never rewrites sales history.
 */
class BuildingConversionService
{
    /**
     * Detail children that follow the building over to the site.
     *
     * `building_status_mapping` is the assignment pivot, included so a site
     * can report its fire-safety statuses without walking the building.
     */
    private const CHILD_TABLES = [
        'building_wings',
        'building_floors',
        'building_flats',
        'building_flat_products',
        'building_amcs',
        'building_fire_systems',
        'fire_systems',
        'building_contacts',
        'building_status_mapping',
    ];

    /**
     * Building -> Site column map.
     *
     * Columns shared by both tables are copied verbatim. Building-only survey
     * columns are copied straight across. A few building fields land on a
     * differently named site field, listed in ALIASES.
     */
    private const ALIASES = [
        'contact_name' => 'contact_person',
        'contact_number' => 'phone',
    ];

    public function convert(Building $building): array
    {
        $alreadyConverted = $building->site_id && $building->converted_at;

        return DB::transaction(function () use ($building, $alreadyConverted) {
            $site = $this->resolveTargetSite($building);
            // `wasRecentlyCreated` is only true after save, so track intent here.
            $created = ! $site->exists;

            $site->fill($this->siteAttributesFrom($building));
            $site->name = $this->uniqueSiteName(
                $building->name ?: 'Site ' . $building->id,
                $site->exists ? $site->id : null
            );
            $site->save();

            $moved = $this->mirrorChildrenToSite($building, $site);

            $building->site_id = $site->id;
            $building->converted_at = now();
            $building->converted_site_name = $site->name;
            $building->save();

            return [
                'site' => $site->fresh(),
                'building' => $building->fresh(),
                'created' => $created,
                'already_converted' => (bool) $alreadyConverted,
                'children_moved' => $moved,
            ];
        });
    }

    /**
     * Decide whether this conversion creates a new Site or refreshes the one
     * the building is already linked to.
     *
     * A building linked by hand before this feature existed keeps that Site and
     * is adopted into it, so no duplicate is created and no link is silently
     * reassigned. Anything else becomes a new Site.
     */
    private function resolveTargetSite(Building $building): Site
    {
        if ($building->site_id) {
            $existing = Site::find($building->site_id);
            if ($existing) {
                return $existing;
            }
        }

        return new Site();
    }

    /**
     * Project the building's columns onto a site row.
     */
    private function siteAttributesFrom(Building $building): array
    {
        $shared = [
            'address', 'city', 'state', 'country', 'pincode',
            'latitude', 'longitude', 'status',
        ];

        $survey = [
            'society_name', 'building_type', 'area', 'google_place_id',
            'construction_year', 'occupancy_certificate_year', 'fire_noc_status',
            'no_of_floors', 'basement_levels', 'no_of_wings', 'no_of_flats',
            'terrace_available', 'refuge_floors', 'commercial_shops_available',
            'no_of_staircase', 'no_of_lifts', 'no_of_exits_entry',
            'fire_safety_available', 'fire_safety_type', 'equipment_condition_notes',
            'last_serviced_date', 'last_amc_company', 'overall_equipment_condition',
            'property_owner', 'plot_no', 'contact_name', 'contact_number',
            'developer_name', 'developer_contact', 'architect_name', 'architect_contact',
            'pmc_consultant_name', 'pmc_consultant_contact',
            'provisional_noc', 'provisional_noc_details', 'under_construction',
        ];

        $attributes = [];

        foreach ($shared as $column) {
            $attributes[$column] = $building->{$column};
        }

        foreach ($survey as $column) {
            $attributes[$column] = $building->{$column};
        }

        foreach (self::ALIASES as $from => $to) {
            $attributes[$to] = $building->{$from};
        }

        // Fall back to the society so the site has something meaningful in
        // the client field rather than an empty string.
        $attributes['client_details'] = $building->society_name
            ?: $building->developer_name
            ?: $building->property_owner;

        // Sites carry a code for reporting; reuse the plot number when present.
        $attributes['code'] = $attributes['code'] ?? $building->plot_no;

        return $attributes;
    }

    /**
     * `sites.name` is unique but `buildings.name` is not, so two buildings can
     * legitimately share a name. Suffix rather than fail, and never collide
     * with the Site being updated.
     */
    private function uniqueSiteName(string $desired, ?int $ignoreSiteId): string
    {
        $candidate = $desired;
        $suffix = 2;

        while (true) {
            $clash = Site::where('name', $candidate)
                ->when($ignoreSiteId, fn ($q) => $q->where('id', '!=', $ignoreSiteId))
                ->exists();

            if (! $clash) {
                return $candidate;
            }

            $candidate = $desired . ' (' . $suffix . ')';
            $suffix++;
        }
    }

    /**
     * Point the building's detail children at the new site.
     *
     * Returns the number of rows updated per table so the API can report what
     * moved. Skipped when the child tables predate the site_id column, which
     * keeps this working against a database that has not been migrated yet.
     *
     * @return array<string, int>
     */
    private function mirrorChildrenToSite(Building $building, Site $site): array
    {
        $moved = [];

        foreach (self::CHILD_TABLES as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'site_id')) {
                continue;
            }

            if (! Schema::hasColumn($table, 'building_id')) {
                continue;
            }

            $moved[$table] = DB::table($table)
                ->where('building_id', $building->id)
                ->update(['site_id' => $site->id]);
        }

        return $moved;
    }
}