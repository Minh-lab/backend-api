<?php

use App\Http\Controllers\Admin\TopicController;
use Illuminate\Support\Facades\Route;


// UC13: Tìm kiếm đề tài
Route::get('/topics', [TopicController::class, 'index']);

// UC14: Thêm đề tài
Route::post('/topics', [TopicController::class, 'store']);

// UC15: Sửa đề tài
Route::put('/topics/{id}', [TopicController::class, 'update']);

// UC16: Xoá đề tài
Route::delete('/topics/{id}', [TopicController::class, 'destroy']);
