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

        if (!in_array($currentRole, $roles)) {
            return response()->json(['success' => false, 'message' => 'Không có quyền.'], 403);
        }

        $request->merge(['current_role' => $currentRole]);

        return $next($request);
    }
}