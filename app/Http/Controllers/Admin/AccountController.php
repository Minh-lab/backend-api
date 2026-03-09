<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AccountRequest;
use App\Models\Admin;
use App\Models\Company;
use App\Models\FacultyStaff;
use App\Models\Lecturer;
use App\Models\Login;
use App\Models\Role;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    private const ROLE_MODELS = [
        'student' => Student::class,
        'lecturer' => Lecturer::class,
        'faculty_staff' => FacultyStaff::class,
        'admin' => Admin::class,
        'company' => Company::class,
    ];

    private const ROLE_PRIMARY_KEYS = [
        'student' => 'student_id',
        'lecturer' => 'lecturer_id',
        'faculty_staff' => 'faculty_staffstaff_id',
        'admin' => 'admin_id',
        'company' => 'company_id',
    ];

    // Bảng có cột is_active
    private const HAS_ACTIVE_FIELD = ['student', 'lecturer', 'company'];

    // ─────────────────────────────────────────────────────────
    // UC9 - Tìm kiếm tài khoản
    // GET /admin/accounts?keyword=&status=&role=&page=&per_page=
    // ─────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $keyword = $request->query('keyword', '');
        $status = $request->query('status', '');  // active | inactive
        $role = $request->query('role', '');     // student | lecturer | faculty_staff | admin | company
        $perPage = (int) $request->query('per_page', 10);
        $page = (int) $request->query('page', 1);

        $results = [];

        // Xác định bảng cần tìm
        $rolesToSearch = ($role && isset(self::ROLE_MODELS[$role]))
            ? [$role => self::ROLE_MODELS[$role]]
            : self::ROLE_MODELS;

        foreach ($rolesToSearch as $roleName => $modelClass) {
            $query = $modelClass::query();

            // Tìm theo keyword (email hoặc username)
            if ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('email', 'like', "%{$keyword}%")
                        ->orWhere('username', 'like', "%{$keyword}%");
                });
            }

            // Lọc trạng thái (chỉ áp dụng bảng có is_active)
            $hasActiveField = in_array($roleName, self::HAS_ACTIVE_FIELD);
            if ($status && $hasActiveField) {
                $query->where('is_active', $status === 'active' ? 1 : 0);
            }

            $primaryKey = self::ROLE_PRIMARY_KEYS[$roleName];

            $users = $query->get()->map(fn($user) => [
                'id' => $user->$primaryKey,   // ← internal ID (dùng cho update/delete)
                'usercode' => $user->usercode,      // ← mã hiển thị ra FE
                'username' => $user->username,
                'email' => $user->email,
                'role' => $roleName,
                'status' => $hasActiveField
                    ? ($user->is_active ? 'active' : 'inactive')
                    : 'active',
                'updated_at' => $user->updated_at,
            ]);

            $results = array_merge($results, $users->toArray());
        }

        // Sắp xếp theo updated_at giảm dần (mới nhất lên đầu)
        usort(
            $results,
            fn($a, $b) =>
            strtotime($b['updated_at'] ?? 0) - strtotime($a['updated_at'] ?? 0)
        );

        // Phân trang thủ công
        $total = count($results);
        $offset = ($page - 1) * $perPage;
        $paginated = array_slice($results, $offset, $perPage);

        return response()->json([
            'success' => true,
            'data' => $paginated,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ], 200);
    }

    // ─────────────────────────────────────────────────────────
    // UC10 - Thêm tài khoản
    // POST /admin/accounts
    // ─────────────────────────────────────────────────────────
    public function store(AccountRequest $request): JsonResponse
    {
        $username = $request->input('username');
        $email = $request->input('email');
        $role = $request->input('role');

        $modelClass = self::ROLE_MODELS[$role];

        // Kiểm tra trùng email trong tất cả bảng
        foreach (self::ROLE_MODELS as $modelC) {
            if ($modelC::where('email', $email)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email đã tồn tại.',
                ], 422);
            }
        }

        // Kiểm tra trùng username trong bảng tương ứng
        if ($modelClass::where('username', $username)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Tên tài khoản đã tồn tại.',
            ], 422);
        }

        // Tạo tài khoản — mật khẩu mặc định = username
        $userData = [
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($username),
            'usercode' => $this->generateUsercode($role),
            'first_login' => 1,
        ];

        // Thêm full_name nếu bảng có
        if ($role !== 'company') {
            $userData['full_name'] = $username;
        } else {
            $userData['name'] = $username;
        }

        // Thêm is_active nếu bảng có
        if (in_array($role, self::HAS_ACTIVE_FIELD)) {
            $userData['is_active'] = 1;
        }

        $user = $modelClass::create($userData);

        // Tạo login record
        $roleModel = Role::where('role_name', $role)->first();
        $primaryKey = self::ROLE_PRIMARY_KEYS[$role];

        Login::create([
            'user_id' => $user->$primaryKey,
            'role_id' => $roleModel->role_id,
            'login_attempts' => 0,
            'lockout_until' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thêm tài khoản thành công.',
            'data' => [
                'id' => $user->$primaryKey,
                'usercode' => $user->usercode,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $role,
                'status' => 'active',
            ],
        ], 201);
    }

    // ─────────────────────────────────────────────────────────
    // UC11 - Sửa tài khoản
    // PUT /admin/accounts/{id}?role=student
    // ─────────────────────────────────────────────────────────
    public function update(AccountRequest $request, $id): JsonResponse
    {
        $role = $request->query('role');
        $username = $request->input('username');
        $email = $request->input('email');
        $status = $request->input('status');
        $resetPass = $request->input('reset_password', false);

        if (!isset(self::ROLE_MODELS[$role])) {
            return response()->json([
                'success' => false,
                'message' => 'Role không hợp lệ.',
            ], 422);
        }

        $modelClass = self::ROLE_MODELS[$role];
        $primaryKey = self::ROLE_PRIMARY_KEYS[$role];
        $user = $modelClass::where($primaryKey, $id)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản không tồn tại.',
            ], 404);
        }

        // Kiểm tra trùng email (trừ chính nó)
        foreach (self::ROLE_MODELS as $rName => $modelC) {
            $pk = self::ROLE_PRIMARY_KEYS[$rName];
            $exists = $modelC::where('email', $email)
                ->when($modelC === $modelClass, fn($q) => $q->where($pk, '!=', $id))
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email đã tồn tại.',
                ], 422);
            }
        }

        // Kiểm tra trùng username (trừ chính nó)
        if (
            $modelClass::where('username', $username)
                ->where($primaryKey, '!=', $id)
                ->exists()
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Tên tài khoản đã tồn tại.',
            ], 422);
        }

        // Cập nhật thông tin
        $updateData = [
            'username' => $username,
            'email' => $email,
        ];

        // Cập nhật trạng thái (chỉ bảng có is_active)
        if (in_array($role, self::HAS_ACTIVE_FIELD) && $status) {
            $updateData['is_active'] = $status === 'active' ? 1 : 0;
        }

        // Reset mật khẩu về mặc định = username
        if ($resetPass) {
            $updateData['password'] = Hash::make($username);
            $updateData['first_login'] = 1;
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật tài khoản thành công.',
            'data' => [
                'id' => $user->$primaryKey,
                'usercode' => $user->usercode,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $role,
                'status' => in_array($role, self::HAS_ACTIVE_FIELD)
                    ? ($user->is_active ? 'active' : 'inactive')
                    : 'active',
            ],
        ], 200);
    }

    // ─────────────────────────────────────────────────────────
    // UC12 - Xoá tài khoản (vô hiệu hoá)
    // DELETE /admin/accounts/{id}?role=student
    // ─────────────────────────────────────────────────────────
    public function destroy(Request $request, $id): JsonResponse
    {
        $role = $request->query('role');

        if (!isset(self::ROLE_MODELS[$role])) {
            return response()->json([
                'success' => false,
                'message' => 'Role không hợp lệ.',
            ], 422);
        }

        $modelClass = self::ROLE_MODELS[$role];
        $primaryKey = self::ROLE_PRIMARY_KEYS[$role];
        $user = $modelClass::where($primaryKey, $id)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản không tồn tại.',
            ], 404);
        }

        // Admin và faculty_staff không có is_active
        if (!in_array($role, self::HAS_ACTIVE_FIELD)) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể vô hiệu hoá tài khoản này.',
            ], 422);
        }

        $user->update(['is_active' => 0]);

        return response()->json([
            'success' => true,
            'message' => 'Vô hiệu hoá tài khoản thành công.',
        ], 200);
    }

    // ─────────────────────────────────────────────────────────
    // Helper: Tạo usercode tự động
    // ─────────────────────────────────────────────────────────
    private function generateUsercode(string $role): string
    {
        $prefix = match ($role) {
            'student' => 'SV',
            'lecturer' => 'GV',
            'faculty_staff' => 'faculty_staff',
            'admin' => 'AD',
            'company' => 'DN',
            default => 'USR',
        };

        $count = self::ROLE_MODELS[$role]::count() + 1;
        return $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
