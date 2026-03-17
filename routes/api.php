<?php

use Illuminate\Support\Facades\Route;
use App\Http\Expertise\ExpertiseController;

Route::prefix('v1')->group(function () {
    Route::get('/expertises', [ExpertiseController::class, 'index']);
    require base_path('routes/auth.php');
    require base_path('routes/admin.php');
    require base_path('routes/lecturer.php');
    require base_path('routes/notification.php');
    require base_path('routes/faculty.php');
});
