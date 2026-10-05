<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PointsController;
use App\Http\Controllers\Api\RewardController;
use App\Http\Controllers\Api\RedemptionController;
use App\Http\Controllers\Api\AuditController;
use App\Http\Controllers\Api\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ── Dashboard ──────────────────────────────────────────────────────
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // ── Points ─────────────────────────────────────────────────────────
    Route::prefix('points')->group(function () {
        Route::post('/earn', [PointsController::class, 'earn']);
        Route::post('/redeem', [PointsController::class, 'redeem']);
        Route::post('/reverse', [PointsController::class, 'reverse']);
        Route::get('/balance/{studentId}', [PointsController::class, 'balance']);
        Route::get('/history/{studentId}', [PointsController::class, 'history']);
        Route::post('/reconcile/{studentId}', [PointsController::class, 'reconcile']);
    });

    // ── Rewards ────────────────────────────────────────────────────────
    Route::apiResource('rewards', RewardController::class);

    // ── Redemptions ────────────────────────────────────────────────────
    Route::apiResource('redemptions', RedemptionController::class)->only(['index', 'show']);

    // ── Audit ──────────────────────────────────────────────────────────
    Route::prefix('audit')->group(function () {
        Route::get('/logs', [AuditController::class, 'index']);
        Route::get('/logs/{id}', [AuditController::class, 'show']);
    });
});
