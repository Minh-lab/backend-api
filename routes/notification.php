<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\NotificationController;

Route::prefix('notifications')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        // UC8 - Thông báo-đang ở routes không phù hợp
        Route::get('/', [NotificationController::class, 'index']); // Lấy danh sách
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);      // Đánh dấu đã xem
    });