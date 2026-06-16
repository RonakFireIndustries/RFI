<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\EmployeeController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\SiteController;
use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\LeaveController;
use App\Http\Controllers\Api\V1\PayrollController;
use App\Http\Controllers\Api\V1\DailyReportController;

Route::post('/v1/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::get('dashboard', [DashboardController::class, 'dashboard']);
    Route::apiResource('employees', EmployeeController::class);
    Route::apiResource('sites', SiteController::class);
    Route::apiResource('attendance', AttendanceController::class);
    Route::apiResource('leaves', LeaveController::class);
    Route::apiResource('payrolls', PayrollController::class);
    Route::apiResource('daily-reports', DailyReportController::class);
});
