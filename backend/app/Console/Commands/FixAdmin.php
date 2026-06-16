<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class FixAdmin extends Command
{
    protected $signature = 'fix:admin';
    protected $description = 'Fix admin@example.com';

    public function handle()
    {
        $u = User::where('email', 'admin@example.com')->first();
        if ($u) {
            $u->assignRole('Admin');
            $u->branch_id = 1;
            $u->save();
            $this->info('Fixed admin@example.com');
        } else {
            $this->info('User not found');
        }
    }
}
