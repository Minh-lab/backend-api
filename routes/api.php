<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    require base_path('routes/auth.php');
    require base_path('routes/admin.php');
    require base_path('routes/lecturer.php');
    require base_path('routes/notification.php');
    require base_path('routes/faculty.php');
    require base_path('routes/topic.php');
});
