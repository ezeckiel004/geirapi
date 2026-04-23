<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\AgencyController;
use App\Http\Controllers\Api\Admin\EquipmentController;
use App\Http\Controllers\Api\Admin\InterventionController as AdminInterventionController;
use App\Http\Controllers\Api\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Client\InterventionController as ClientInterventionController;
use App\Http\Controllers\Api\Client\ReportController as ClientReportController;
use App\Http\Controllers\Api\Technician\MissionController;
use App\Http\Controllers\Api\Technician\ReportController as TechReportController;

// ═══════════════════════════════════════════════════════════════════
//  AUTH — Public
// ═══════════════════════════════════════════════════════════════════
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

// ═══════════════════════════════════════════════════════════════════
//  Routes protégées par Sanctum
// ═══════════════════════════════════════════════════════════════════
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::put('/auth/password',[AuthController::class, 'updatePassword']);

    // ─────────────────────────────────────────────────────────────
    //  ADMIN
    // ─────────────────────────────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Agences
        Route::apiResource('agencies', AgencyController::class);

        // Équipements
        Route::apiResource('equipment', EquipmentController::class);

        // Interventions
        Route::post('/interventions/{intervention}/assign', [AdminInterventionController::class, 'assign']);
        Route::apiResource('interventions', AdminInterventionController::class);

        // Rapports
        Route::post('/reports/{report}/send-to-client', [AdminReportController::class, 'sendToClient']);
        Route::get('/reports/{report}', [AdminReportController::class, 'show']);
        Route::get('/reports', [AdminReportController::class, 'index']);

        // Gestion utilisateurs
        Route::get('/technicians',    [UserController::class, 'technicians']);
        Route::post('/technicians',   [UserController::class, 'createTechnician']);
        Route::get('/clients',        [UserController::class, 'clients']);
        Route::post('/clients',       [UserController::class, 'createClient']);
        Route::put('/users/{user}/toggle-active', [UserController::class, 'toggleActive']);

        Route::post('/reports/{report}/validate', [AdminReportController::class, 'validate']);

        Route::get('/equipment-categories', [\App\Http\Controllers\Api\Admin\EquipmentCategoryController::class, 'index']);
    Route::post('/equipment-categories', [\App\Http\Controllers\Api\Admin\EquipmentCategoryController::class, 'store']);
    });

    // ─────────────────────────────────────────────────────────────
    //  CLIENT
    // ─────────────────────────────────────────────────────────────
    Route::middleware('role:client')->prefix('client')->group(function () {

        // Vue annuelle des 4 interventions
        Route::get('/year', [ClientInterventionController::class, 'year']);

        // Interventions
        Route::get('/interventions',                                [ClientInterventionController::class, 'index']);
        Route::put('/interventions/{intervention}/accept',          [ClientInterventionController::class, 'accept']);
        Route::put('/interventions/{intervention}/decline',         [ClientInterventionController::class, 'decline']);

        // Rapports
        Route::get('/reports',                    [ClientReportController::class, 'index']);
        Route::get('/reports/{report}',           [ClientReportController::class, 'show']);
        Route::put('/reports/{report}/validate',  [ClientReportController::class, 'validate']);
        Route::put('/reports/{report}/reject',    [ClientReportController::class, 'reject']);
    });

    // ─────────────────────────────────────────────────────────────
    //  TECHNICIEN
    // ─────────────────────────────────────────────────────────────
    Route::middleware('role:technician')->prefix('tech')->group(function () {

        // Missions
        Route::get('/missions',              [MissionController::class, 'index']);
        Route::get('/missions/{intervention}', [MissionController::class, 'show']);
        Route::put('/missions/{intervention}/start', [MissionController::class, 'start']);

        // Rapports
        Route::get('/reports',                [TechReportController::class, 'index']);
        Route::post('/reports',               [TechReportController::class, 'store']);
        Route::get('/reports/{report}',       [TechReportController::class, 'show']);
    });
});
