<?php

use App\Http\Controllers\Capstone\CapstoneRequestController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum', 'role:student'])->prefix('capstonerequest')->group(function () {
    // UC17: Đăng ký đợt đồ án
    Route::post('/register-capstone', [CapstoneRequestController::class, 'registerCapstone']);
    // UC18: Đăng ký GVHD DA
    Route::post('/register-lecturer', [CapstoneRequestController::class, 'registerLecturer']);
    // UC19: Dăng ký đề tài mới
    Route::post('/register-topic-new', [CapstoneRequestController::class, 'registerTopic']);
    // UC20: Đăng ký đề tài từ ngân hàng đề tài 
    Route::post('/register-topic-bank', [CapstoneRequestController::class, 'registerTopicBank']);
});
