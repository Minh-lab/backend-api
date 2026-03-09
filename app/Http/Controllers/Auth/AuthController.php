<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\Login;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\PasswordReset;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\Auth\ChangePasswordRequest;
use Illuminate\Support\Str;
use App\Http\Requests\Auth\UpdateExpertiseRequest;
use App\Models\Expertise;
use App\Models\LecturerExpertise;
use App\Http\Requests\Auth\LeaveRequest;
use App\Models\LecturerRequest;
use App\Models\Notification;
use App\Models\UserNotification;
use App\Models\FacultyStaff;
use Illuminate\Support\Facades\Storage;
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

    // UC1: Login
    public function login(LoginRequest $request): JsonResponse
    {
        $email = $request->input('email');
        $password = $request->input('password');

        $found = null;
        $foundRole = null;

        foreach (self::ROLE_MODELS as $roleName => $modelClass) {
            $user = $modelClass::where('email', $email)->first();
            if ($user) {
                $found = $user;
                $foundRole = $roleName;
                break;
            }
        }

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

        // 7. Thành công
        $login->update([
            'login_attempts' => 0,
            'lockout_until' => null,
        ]);

        $token = $found->createToken('auth_token', [$foundRole])->plainTextToken;

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

    // UC Xem thông tin cá nhân
    // public function me(Request $request): JsonResponse
    // {
    //     $user = $request->user();// trả về model của user đã đc xác thực từ token trong request
    //     $role = $user->currentAccessToken()->abilities[0] ?? null;

    //     return response()->json([
    //         'success' => true,
    //         'data' => [
    //             'user' => new UserResource($user, $role),
    //             'role' => $role,
    //         ],
    //     ], 200);
    // }





    // UC3 - BƯỚC 1: Nhập email → gửi OTP

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $email = $request->input('email');

        // Tìm user theo email trong tất cả bảng
        $found = null;
        $foundRole = null;

        foreach (self::ROLE_MODELS as $roleName => $modelClass) {
            $user = $modelClass::where('email', $email)->first();
            if ($user) {
                $found = $user;
                $foundRole = $roleName;
                break;
            }
        }

        // Email không tồn tại → vẫn trả về success (bảo mật, không lộ email)
        if (!$found) {
            return response()->json([
                'success' => true,
                'message' => 'Nếu email tồn tại, mã OTP đã được gửi.',
            ], 200);
        }

        $role = Role::where('role_name', $foundRole)->first();
        $primaryKey = $found->getKeyName();

        // Kiểm tra có đang bị disable nút gửi không (OTP cũ chưa hết cooldown)
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

        // Tạo OTP 6 số
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Xoá các OTP cũ chưa dùng của user này
        PasswordReset::where('user_id', $found->$primaryKey)
            ->where('role_id', $role->role_id)
            ->delete();

        // Lưu OTP mới vào CSDL (hiệu lực 60 giây)
        PasswordReset::create([
            'user_id' => $found->$primaryKey,
            'role_id' => $role->role_id,
            'otp' => $otp,
            'expired_at' => now()->addSeconds(60),
            'is_used' => 0,
        ]);

        // Gửi email chứa OTP
        Mail::raw("Mã OTP của bạn là: {$otp}\nMã có hiệu lực trong 60 giây.", function ($message) use ($email) {
            $message->to($email)
                ->subject('Mã OTP đặt lại mật khẩu');
        });

        return response()->json([
            'success' => true,
            'message' => 'Mã OTP đã được gửi đến email của bạn.',
        ], 200);
    }


    // UC3 - BƯỚC 2: Xác thực OTP (tự động khi nhập đủ 6 ký tự)
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $email = $request->input('email');
        $otp = $request->input('otp');

        // Tìm user
        $found = null;
        $foundRole = null;

        foreach (self::ROLE_MODELS as $roleName => $modelClass) {
            $user = $modelClass::where('email', $email)->first();
            if ($user) {
                $found = $user;
                $foundRole = $roleName;
                break;
            }
        }

        if (!$found) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản không tồn tại.',
            ], 404);
        }

        $role = Role::where('role_name', $foundRole)->first();
        $primaryKey = $found->getKeyName();

        // Lấy OTP record mới nhất chưa dùng
        $resetRecord = PasswordReset::where('user_id', $found->$primaryKey)
            ->where('role_id', $role->role_id)
            ->where('is_used', 0)
            ->latest('created_at')
            ->first();

        // Không có OTP
        if (!$resetRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Mã OTP không hợp lệ.',
            ], 400);
        }

        // OTP đã bị vô hiệu hoá (sai quá 3 lần)
        if ($resetRecord->attempts >= 3) {
            // Tính cooldown còn lại
            $secondsLeft = max(0, now()->diffInSeconds($resetRecord->expired_at, false));
            return response()->json([
                'success' => false,
                'message' => 'Mã OTP đã bị vô hiệu hoá do nhập sai quá 3 lần.',
                'seconds_left' => $secondsLeft,
            ], 429);
        }

        // OTP hết hạn (60s)
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
                // Vô hiệu hoá OTP, set expired_at = now + 60s (cooldown)
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

        // OTP đúng → tạo reset_token, hiệu lực 10 phút
        $resetToken = Str::random(64);

        $resetRecord->update([
            'otp' => $resetToken,
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

    // UC3 - BƯỚC 3: Đặt mật khẩu mới

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $email = $request->input('email');
        $resetToken = $request->input('otp');       // FE gửi reset_token vào field otp
        $newPassword = $request->input('password');

        // Tìm user
        $found = null;
        $foundRole = null;

        foreach (self::ROLE_MODELS as $roleName => $modelClass) {
            $user = $modelClass::where('email', $email)->first();
            if ($user) {
                $found = $user;
                $foundRole = $roleName;
                break;
            }
        }

        if (!$found) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản không tồn tại.',
            ], 404);
        }

        $role = Role::where('role_name', $foundRole)->first();
        $primaryKey = $found->getKeyName();

        // Tìm reset record theo token
        $resetRecord = PasswordReset::where('user_id', $found->$primaryKey)
            ->where('role_id', $role->role_id)
            ->where('otp', $resetToken)
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

        // Mật khẩu mới trùng mật khẩu cũ
        if (Hash::check($newPassword, $found->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu mới không được trùng mật khẩu hiện tại.',
            ], 422);
        }

        // Cập nhật mật khẩu mới
        $found->update([
            'password' => Hash::make($newPassword),
        ]);

        // Đánh dấu token đã dùng
        $resetRecord->update(['is_used' => 1]);

        // Xoá tất cả token Sanctum cũ (bắt đăng nhập lại)
        $found->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đặt lại mật khẩu thành công.',
        ], 200);
    }

    //UC4: Đặt lại password
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

        // Mật khẩu mới trùng mật khẩu cũ
        if (Hash::check($newPassword, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu mới không được trùng mật khẩu hiện tại.',
            ], 422);
        }

        // Cập nhật mật khẩu mới
        $user->update([
            'password' => Hash::make($newPassword),
            'first_login' => 0, // Đã đổi mật khẩu lần đầu
        ]);

        // Xoá tất cả token cũ → bắt đăng nhập lại
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đổi mật khẩu thành công.',
        ], 200);
    }


    //UC5 
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $role = $user->currentAccessToken()->abilities[0] ?? null;

        // Eager load relationships theo role
        if ($role === 'student') {
            $user->load('class');
        } elseif ($role === 'lecturer') {
            $user->load('lecturerExpertises.expertise');
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserResource($user, $role),
                'role' => $role,
            ],
        ], 200);
    }
    // UC6 - Lấy tất cả chuyên môn (để hiển thị checkbox)
    public function getExpertises(Request $request): JsonResponse
    {
        // Chỉ giảng viên mới được dùng
        $role = $request->user()->currentAccessToken()->abilities[0] ?? null;
        if ($role !== 'lecturer') {
            return response()->json([
                'success' => false,
                'message' => 'Không có quyền thực hiện thao tác này.',
            ], 403);
        }

        $lecturer = $request->user();
        $lecturerId = $lecturer->lecturer_id;

        // Lấy tất cả chuyên môn
        $allExpertises = Expertise::orderBy('name')->get();

        // Lấy danh sách chuyên môn hiện tại của giảng viên
        $currentExpertiseIds = LecturerExpertise::where('lecturer_id', $lecturerId)
            ->pluck('expertise_id')
            ->toArray();

        // Map: đánh dấu checkbox nào đang được tick
        $expertises = $allExpertises->map(fn($e) => [
            'expertise_id' => $e->expertise_id,
            'name' => $e->name,
            'description' => $e->description,
            'is_selected' => in_array($e->expertise_id, $currentExpertiseIds),
        ]);

        return response()->json([
            'success' => true,
            'data' => $expertises,
        ], 200);
    }

    // UC6 - Cập nhật chuyên môn giảng viên
    public function updateExpertise(UpdateExpertiseRequest $request): JsonResponse
    {
        // Chỉ giảng viên mới được dùng
        $role = $request->user()->currentAccessToken()->abilities[0] ?? null;
        if ($role !== 'lecturer') {
            return response()->json([
                'success' => false,
                'message' => 'Không có quyền thực hiện thao tác này.',
            ], 403);
        }

        $lecturer = $request->user();
        $lecturerId = $lecturer->lecturer_id;
        $expertiseIds = $request->input('expertise_ids');

        // Xoá tất cả chuyên môn cũ → thêm lại mới (sync)
        LecturerExpertise::where('lecturer_id', $lecturerId)->delete();

        $now = now();
        $data = array_map(fn($id) => [
            'lecturer_id' => $lecturerId,
            'expertise_id' => $id,
            'created_at' => $now,
        ], $expertiseIds);

        LecturerExpertise::insert($data);

        // Trả về thông tin giảng viên đã cập nhật
        $lecturer->load('lecturerExpertises.expertise');

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật chuyên môn thành công.',
            'data' => new UserResource($lecturer, 'lecturer'),
        ], 200);
    }
    // UC7 - Yêu cầu nghỉ phép dài hạn
    public function createLeaveRequest(LeaveRequest $request): JsonResponse
    {
        // Chỉ giảng viên mới được dùng
        $role = $request->user()->currentAccessToken()->abilities[0] ?? null;
        if ($role !== 'lecturer') {
            return response()->json([
                'success' => false,
                'message' => 'Không có quyền thực hiện thao tác này.',
            ], 403);
        }

        $lecturer = $request->user();
        $lecturerId = $lecturer->lecturer_id;

        // Upload file đơn nghỉ phép
        $file = $request->file('file');
        $filePath = $file->store("leave_requests/{$lecturerId}", 'public');

        // Lưu vào bảng lecturer_requests
        $leaveRequest = LecturerRequest::create([
            'lecturer_id' => $lecturerId,
            'type' => 'LEAVE_REQ',
            'status' => 'PENDING',
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'file_path' => $filePath,
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ]);

        // Thông báo cho tất cả facultyStaff
        $this->notifyFacultyStaff($lecturer->full_name, $leaveRequest->request_id);

        return response()->json([
            'success' => true,
            'message' => 'Gửi yêu cầu thành công.',
            'data' => [
                'request_id' => $leaveRequest->request_id,
                'title' => $leaveRequest->title,
                'status' => $leaveRequest->status,
                'start_date' => $leaveRequest->start_date,
                'end_date' => $leaveRequest->end_date,
                'file_url' => Storage::url($filePath),
            ],
        ], 201);
    }

    // Helper: Gửi thông báo cho facultyStaff
    private function notifyFacultyStaff(string $lecturerName, int $requestId): void
    {
        // Tạo notification
        $notification = Notification::create([
            'title' => 'Yêu cầu nghỉ phép mới',
            'content' => "Giảng viên {$lecturerName} đã gửi yêu cầu nghỉ phép dài hạn. Mã yêu cầu: #{$requestId}",
        ]);

        // Gửi cho tất cả FacultyStaff
        $facultyStaffList = FacultyStaff::all();

        $role = Role::where('role_name', 'facultyStaff')->first();
        $now = now();

        $userNotifications = $facultyStaffList->map(fn($facultyStaff) => [
            'notification_id' => $notification->notification_id,
            'user_id' => $facultyStaff->faculty_staff_id,
            'is_read' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ])->toArray();

        UserNotification::insert($userNotifications);
    }
    // UC8 - Lấy danh sách thông báo
    public function getNotifications(Request $request): JsonResponse
    {
        $role = $request->user()->currentAccessToken()->abilities[0] ?? null;

        // Chỉ sinh viên và giảng viên
        if (!in_array($role, ['student', 'lecturer'])) {
            return response()->json([
                'success' => false,
                'message' => 'Không có quyền thực hiện thao tác này.',
            ], 403);
        }

        $user = $request->user();
        $primaryKey = $user->getKeyName(); // student_id hoặc lecturer_id
        $userId = $user->$primaryKey;

        // Lấy danh sách thông báo của user
        // Sắp xếp theo thời gian giảm (mới nhất trên cùng)
        $notifications = UserNotification::with('notification')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($un) => [
                'id' => $un->notification->notification_id,
                'title' => $un->notification->title,
                'content' => $un->notification->content,
                'is_read' => (bool) $un->is_read,
                'created_at' => $un->notification->created_at,
            ]);

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'total' => $notifications->count(),
            'unread_count' => $notifications->where('is_read', false)->count(),
        ], 200);
    }

    // UC8 - Đánh dấu đã xem (gọi khi user bấm vào thông báo)
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $role = $request->user()->currentAccessToken()->abilities[0] ?? null;

        if (!in_array($role, ['student', 'lecturer'])) {
            return response()->json([
                'success' => false,
                'message' => 'Không có quyền thực hiện thao tác này.',
            ], 403);
        }

        $user = $request->user();
        $primaryKey = $user->getKeyName();
        $userId = $user->$primaryKey;

        // Tìm user_notification
        $userNotification = UserNotification::where('notification_id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$userNotification) {
            return response()->json([
                'success' => false,
                'message' => 'Thông báo không tồn tại.',
            ], 404);
        }

        // Cập nhật trạng thái đã xem (dù chưa xem hay rồi)
        $userNotification->update(['is_read' => 1]);

        // Trả về nội dung thông báo để hiển thị dialog
        $notification = $userNotification->notification;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $notification->notification_id,
                'title' => $notification->title,
                'content' => $notification->content,
                'is_read' => true,
                'created_at' => $notification->created_at,
            ],
        ], 200);
    }
}


