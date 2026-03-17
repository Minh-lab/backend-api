<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckActiveLecturer
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Kiểm tra nếu là giảng viên và đang nghỉ phép (is_active = 0)
        if ($user && isset($user->is_active) && (int)$user->is_active === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản đang trong trạng thái nghỉ phép. Các chức năng nghiệp vụ đã bị khóa.'
            ], 403);
        }

        return $next($request);
    }
}
