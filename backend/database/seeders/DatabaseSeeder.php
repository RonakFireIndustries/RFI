<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (\App\Models\User::where('email', 'admin@example.com')->doesntExist()) {
            \App\Models\User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            ]);
        } else {
            $u = \App\Models\User::where('email', 'admin@example.com')->first();
            $u->password = \Illuminate\Support\Facades\Hash::make('password123');
            $u->save();
        }

        $this->call([
            RolesAndPermissionsSeeder::class,
            DashboardWidgetSeeder::class,
        ]);
    }
}
