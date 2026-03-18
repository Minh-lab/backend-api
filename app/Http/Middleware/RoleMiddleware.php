<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string...$roles): mixed
    {
        $user = $request->user(); // Sanctum tự resolve model

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Chưa đăng nhập.'], 401);
        }

        // Lấy ability (role) từ token hiện tại
        $currentRole = $request->user()->currentAccessToken()->abilities[0] ?? null;

        // Nếu roles truyền vào dạng "vpk,admin,student" (chuỗi đơn có dấu phẩy)
        // thì ta cần hạp nhất lại thành mảng phẳng.
        $flatRoles = [];
        foreach ($roles as $r) {
            foreach (explode(',', $r) as $sub) {
                $flatRoles[] = trim($sub);
            }
        }

        if (!in_array($currentRole, $flatRoles)) {
            return response()->json(['success' => false, 'message' => 'Không có quyền.'], 403);
        }

        $request->merge(['current_role' => $currentRole]);

        return $next($request);
    }
}