<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\Access;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InspectUserPermissionsCommand extends Command
{
    protected $signature = 'access:inspect {email : Email of the user to inspect}';

    protected $description = 'Inspect a user\'s roles, super-admin flag, and direct/effective permissions (debug helper).';

    public function handle(): int
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("No user found with email {$email}");
            return Command::FAILURE;
        }

        $this->info('User #' . $user->id . ' | ' . $user->name . ' | ' . $user->email);
        $this->line('  is_super_admin (column): ' . var_export($user->is_super_admin ?? null, true));
        $this->line('  isSuperAdmin (effective) : ' . var_export(Access::isSuperAdmin($user), true));

        $roles = $user->roles->pluck('name')->all();
        $this->line('  Roles                     : ' . (count($roles) ? implode(', ', $roles) : '(none)'));

        $direct = $user->getDirectPermissions()->pluck('name')->sort()->values()->all();
        $this->line('  Direct permissions (' . count($direct) . '):');
        foreach ($direct as $d) {
            $this->line('    - ' . $d);
        }

        // Also show raw model_has_permissions rows (by id) to catch name mismatches
        $morphKey = config('permission.column_names.model_morph_key', 'model_morph_key');
        $rows = DB::table('model_has_permissions')
            ->join('permissions', 'permissions.id', '=', 'model_has_permissions.permission_id')
            ->where('model_type', User::class)
            ->where($morphKey, $user->getKey())
            ->orderBy('permissions.name')
            ->get(['permission_id', 'permissions.name']);

        $this->line('  model_has_permissions rows (' . $rows->count() . '):');
        foreach ($rows as $r) {
            $this->line('    #' . $r->permission_id . ' ' . $r->name);
        }

        return Command::SUCCESS;
    }
}
