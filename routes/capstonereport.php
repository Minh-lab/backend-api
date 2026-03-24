<?php

use App\Http\Controllers\Capstone\CapstoneReportsController;
use Illuminate\Support\Facades\Route;

// Báo cáo đồ án
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/capstones/milestones', [CapstoneReportsController::class, 'getMilestones']);
    Route::get('/capstones/reports/history', [CapstoneReportsController::class, 'getReportHistory']);
    Route::post('/capstones/reports/submit', [CapstoneReportsController::class, 'submitReport']);
});
