<?php

use App\Http\Controllers\Expertise\ExpertiseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])
    ->group(function () {
        Route::get('expertises', [ExpertiseController::class, 'index']);
    });
