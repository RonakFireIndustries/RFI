<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records when a building was promoted into a Site.
 *
 * `buildings.site_id` already exists and already points at `sites`, so it is
 * reused as the link rather than adding a second, overlapping column. It was
 * previously editable by hand through a "Linked Site" dropdown; that dropdown
 * is removed in favour of this explicit conversion, so `site_id` is now only
 * ever written by the conversion itself. `converted_at` distinguishes a Site
 * created by conversion from a Site that was linked by hand before this change.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('buildings')) {
            return;
        }

        Schema::table('buildings', function (Blueprint $table) {
            if (! Schema::hasColumn('buildings', 'converted_at')) {
                $table->timestamp('converted_at')->nullable()->after('site_id');
            }

            if (! Schema::hasColumn('buildings', 'converted_site_name')) {
                $table->string('converted_site_name')->nullable()->after('converted_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('buildings')) {
            return;
        }

        $columns = array_values(array_filter(
            ['converted_at', 'converted_site_name'],
            fn ($c) => Schema::hasColumn('buildings', $c)
        ));

        if ($columns === []) {
            return;
        }

        Schema::table('buildings', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }
};