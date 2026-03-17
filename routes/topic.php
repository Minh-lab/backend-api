<?php

use App\Http\Controllers\TopicController;
use Illuminate\Support\Facades\Route;


//UC 13-16: Topics Management Routes
Route::middleware(['auth:sanctum', 'role:faculty_staff'])->prefix('faculty_staff')->group(function () {
    Route::get('/topics',        [TopicController::class, 'index']);          // UC 13 - Tìm kiếm
    Route::post('/topics',        [TopicController::class, 'store']);          // UC 14 - Thêm
    Route::put('/topics/{id}',   [TopicController::class, 'update']);        // UC 15 - Sửa
    Route::delete('/topics/{id}',   [TopicController::class, 'destroy']);       // UC 16 - Xoá
});
