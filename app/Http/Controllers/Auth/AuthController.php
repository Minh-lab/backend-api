<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\Login;
use App\Models\Role;
use App\Models\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private const MAX_FAILED_ATTEMPTS = 6;
    private const LOCK_DURATION_MINUTES = 30;

    private const ROLE_MODELS = [
        'student' => \App\Models\Student::class,
        'lecturer' => \App\Models\Lecturer::class,
        'faculty_staff' => \App\Models\FacultyStaff::class,
        'admin' => \App\Models\Admin::class,
        'company' => \App\Models\Company::class,
    ];


    // Helper: tìm user theo email

    private function findUserByEmail(string $email): array
    {
        foreach (self::ROLE_MODELS as $roleName => $modelClass) {
            $user = $modelClass::where('email', $email)->first();
            if ($user) {
                return [$user, $roleName];
            }
        }
        return [null, null];
    }


    // UC1: Login

    public function login(LoginRequest $request): JsonResponse
    {
        $email = $request->input('email');
        $password = $request->input('password');

        [$found, $foundRole] = $this->findUserByEmail($email);

        // 1. Email không tồn tại
        if (!$found) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản không tồn tại.',
            ], 404);
        }

        // 2. Lấy role + login record
        $role = Role::where('role_name', $foundRole)->first();
        $primaryKey = $found->getKeyName();
        $login = Login::where('user_id', $found->$primaryKey)
            ->where('role_id', $role->role_id)
            ->first();

        // 3. Chưa có login record
        if (!$login) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản chưa được kích hoạt.',
            ], 403);
        }

        // 4. Bị vô hiệu hóa bởi admin
        if (isset($found->is_active) && $found->is_active == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản đã bị khoá.',
                'type' => 'toast',
            ], 403);
        }

        // 5. Đang bị khóa tạm thời
        if ($login->lockout_until && now()->lt($login->lockout_until)) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản bị khoá tạm thời, thử lại sau.',
                'type' => 'toast',
                'locked_until' => $login->lockout_until,
            ], 423);
        }

        // 6. Sai mật khẩu
        if (!Hash::check($password, $found->password)) {
            $attempts = ($login->login_attempts ?? 0) + 1;

            if ($attempts >= self::MAX_FAILED_ATTEMPTS) {
                $login->update([
                    'login_attempts' => 0,
                    'lockout_until' => now()->addMinutes(self::LOCK_DURATION_MINUTES),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Nhập sai quá 6 lần, tài khoản bị khoá 30 phút.',
                ], 423);
            }

            $login->update(['login_attempts' => $attempts]);

            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu không chính xác.',
                'attempts_left' => self::MAX_FAILED_ATTEMPTS - $attempts,
            ], 401);
        }

        // 7. Thành công → reset attempts + tạo token
        $login->update([
            'login_attempts' => 0,
            'lockout_until' => null,
        ]);

        $token = $found->createToken('auth_token', [$foundRole])->plainTextToken;

        // Eager load relations cho UserResource
        if ($foundRole === 'student') {
            $found->load('studentClass');
        } elseif ($foundRole === 'lecturer') {
            $found->load('expertises');
        }

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công.',
            'data' => [
                'token' => $token,
                'role' => $foundRole,
                'user' => new UserResource($found, $foundRole),
            ],
        ], 200);
    }


    // UC2: Logout

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công.',
        ], 200);
    }


    // UC3 - Bước 1: Gửi OTP

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $email = $request->input('email');

        [$found, $foundRole] = $this->findUserByEmail($email);

        // Email không tồn tại → vẫn trả success (bảo mật, không lộ email)
        if (!$found) {
            return response()->json([
                'success' => true,
                'message' => 'Nếu email tồn tại, mã OTP đã được gửi.',
            ], 200);
        }

        $role = Role::where('role_name', $foundRole)->first();
        $primaryKey = $found->getKeyName();

        // Kiểm tra cooldown (sai OTP quá 3 lần, chưa hết thời gian chờ)
        $recentReset = PasswordReset::where('user_id', $found->$primaryKey)
            ->where('role_id', $role->role_id)
            ->where('is_used', 0)
            ->where('attempts', '>=', 3)
            ->where('expired_at', '>', now())
            ->latest('created_at')
            ->first();

        if ($recentReset) {
            $secondsLeft = now()->diffInSeconds($recentReset->expired_at, false);
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng chờ trước khi yêu cầu mã OTP mới.',
                'seconds_left' => $secondsLeft,
            ], 429);
        }

        // Xoá OTP cũ → tạo OTP mới 6 số (hiệu lực 60 giây)
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PasswordReset::where('user_id', $found->$primaryKey)
            ->where('role_id', $role->role_id)
            ->delete();

        PasswordReset::create([
            'user_id' => $found->$primaryKey,
            'role_id' => $role->role_id,
            'otp' => $otp,
            'expired_at' => now()->addSeconds(60),
            'is_used' => 0,
            'attempts' => 0,
        ]);

        // Gửi email
        Mail::raw("Mã OTP của bạn là: {$otp}\nMã có hiệu lực trong 60 giây.", function ($message) use ($email) {
            $message->to($email)->subject('Mã OTP đặt lại mật khẩu');
        });

        return response()->json([
            'success' => true,
            'message' => 'Mã OTP đã được gửi đến email của bạn.',
        ], 200);
    }


    // UC3 - Bước 2: Xác thực OTP

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $email = $request->input('email');
        $otp = $request->input('otp');

        [$found, $foundRole] = $this->findUserByEmail($email);

        if (!$found) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản không tồn tại.',
            ], 404);
        }

        $role = Role::where('role_name', $foundRole)->first();
        $primaryKey = $found->getKeyName();

        // Chỉ lấy OTP thật (6 ký tự), không lấy reset_token (64 ký tự)
        $resetRecord = PasswordReset::where('user_id', $found->$primaryKey)
            ->where('role_id', $role->role_id)
            ->where('is_used', 0)
            ->whereRaw('LENGTH(otp) = 6')
            ->latest('created_at')
            ->first();

        // Không có OTP
        if (!$resetRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Mã OTP không hợp lệ.',
            ], 400);
        }

        // Sai quá 3 lần → bị vô hiệu hoá
        if ($resetRecord->attempts >= 3) {
            $secondsLeft = max(0, now()->diffInSeconds($resetRecord->expired_at, false));
            return response()->json([
                'success' => false,
                'message' => 'Mã OTP đã bị vô hiệu hoá do nhập sai quá 3 lần.',
                'seconds_left' => $secondsLeft,
            ], 429);
        }

        // OTP hết hạn
        if (now()->gt($resetRecord->expired_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Mã OTP đã hết hạn, vui lòng yêu cầu mã mới.',
            ], 400);
        }

        // OTP sai → tăng attempts
        if ($resetRecord->otp !== $otp) {
            $newAttempts = $resetRecord->attempts + 1;

            if ($newAttempts >= 3) {
                $resetRecord->update([
                    'attempts' => $newAttempts,
                    'expired_at' => now()->addSeconds(60),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Mã OTP đã bị vô hiệu hoá do nhập sai quá 3 lần.',
                    'seconds_left' => 60,
                ], 429);
            }

            $resetRecord->update(['attempts' => $newAttempts]);

            return response()->json([
                'success' => false,
                'message' => 'Mã OTP không chính xác.',
                'attempts_left' => 3 - $newAttempts,
            ], 400);
        }

        // OTP đúng → tạo reset_token 64 ký tự (hiệu lực 10 phút)
        $resetToken = Str::random(64);

        $resetRecord->update([
            'reset_token' => $resetToken,
            'is_used' => 0,
            'attempts' => 0,
            'expired_at' => now()->addMinutes(10),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Xác thực OTP thành công.',
            'reset_token' => $resetToken,
        ], 200);
    }


    // UC3 - Bước 3: Đặt mật khẩu mới

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $email = $request->input('email');
        $resetToken = $request->input('reset_token');
        $newPassword = $request->input('password');

        [$found, $foundRole] = $this->findUserByEmail($email);

        if (!$found) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản không tồn tại.',
            ], 404);
        }

        $role = Role::where('role_name', $foundRole)->first();
        $primaryKey = $found->getKeyName();

        $resetRecord = PasswordReset::where('user_id', $found->$primaryKey)
            ->where('role_id', $role->role_id)
            ->where('reset_token', $resetToken)
            ->where('is_used', 0)
            ->first();

        if (!$resetRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Phiên đặt lại mật khẩu không hợp lệ.',
            ], 400);
        }

        // Token hết hạn
        if (now()->gt($resetRecord->expired_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Phiên đã hết hạn, vui lòng thực hiện lại.',
            ], 400);
        }

        // Mật khẩu mới trùng cũ
        if (Hash::check($newPassword, $found->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu mới không được trùng mật khẩu hiện tại.',
            ], 422);
        }

        // Cập nhật mật khẩu + đánh dấu token đã dùng + xoá Sanctum tokens
        $found->update(['password' => Hash::make($newPassword)]);
        $resetRecord->update(['is_used' => 1]);
        $found->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đặt lại mật khẩu thành công.',
        ], 200);
    }

    // UC4: Đổi mật khẩu (đang đăng nhập)

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $currentPassword = $request->input('current_password');
        $newPassword = $request->input('password');

        // Mật khẩu hiện tại không đúng
        if (!Hash::check($currentPassword, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu hiện tại không chính xác.',
            ], 422);
        }

        // Mật khẩu mới trùng cũ
        if (Hash::check($newPassword, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu mới không được trùng mật khẩu hiện tại.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($newPassword),
            'first_login' => 0,
        ]);

        // Xoá tất cả token → bắt đăng nhập lại
        // $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đổi mật khẩu thành công.',
        ], 200);
    }

    // UC5: Xem thông tin cá nhân

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $role = $user->currentAccessToken()->abilities[0] ?? null;

        // Eager load relations theo role
        if ($role === 'student') {
            $user->load('studentClass');
        } elseif ($role === 'lecturer') {
            $user->load('expertises');
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserResource($user, $role),
                'role' => $role,
            ],
        ], 200);
    }
}