<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class TestController extends Controller {
    public function test() {
        try {
            DB::connection()->getPdo();
            return response()->json([
                'status' => 'success',
                'message' => 'API is working and connected to the database.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to connect to the database.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
