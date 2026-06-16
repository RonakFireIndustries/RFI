<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TestApi extends Command
{
    protected $signature = 'test:api';
    protected $description = 'Command description';

    public function handle()
    {
        $user = User::where('email', 'mumbaimanager@example.com')->first();
        Auth::login($user);

        DB::enableQueryLog();
        $productsGlobal = Product::withSum('inventories', 'quantity')->get();
        $this->info("Mumbai manager count for product 1: " . $productsGlobal->first()->inventories_sum_quantity);
        $this->info("SQL: " . json_encode(DB::getQueryLog()));
    }
}
