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
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

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
        'faculty_staff' => 'faculty_staff_id',
        'admin' => 'admin_id',
        'company' => 'company_id',
    ];

    // Bảng có cột is_active
    private const HAS_ACTIVE_FIELD = ['student', 'lecturer', 'company'];


    // UC9 - Tìm kiếm tài khoản
    // GET /admin/accounts?keyword=&status=&role=&page=&per_page=

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

        // Lấy admin hiện tại (nếu có)
        $currentAdmin = $request->user();

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

            // Nếu tìm kiếm admin, loại trừ admin hiện tại
            if ($roleName === 'admin' && $currentAdmin && $currentAdmin->admin_id) {
                $query->where('admin_id', '!=', $currentAdmin->admin_id);
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

    // Lấy chi tiết tài khoản theo ID
    // GET /admin/accounts/{id}?role=student
    public function getAccountById(Request $request, $id): JsonResponse
    {
        $role = $request->query('role');

        if (!$role || !isset(self::ROLE_MODELS[$role])) {
            return response()->json([
                'success' => false,
                'message' => 'Role không hợp lệ hoặc không được cung cấp.',
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

        // Chuẩn bị dữ liệu trả về
        $data = [
            'id' => $user->$primaryKey,
            'usercode' => $user->usercode,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $role,
            'status' => in_array($role, self::HAS_ACTIVE_FIELD)
                ? ($user->is_active ? 'active' : 'inactive')
                : 'active',
            'updated_at' => $user->updated_at,
        ];

        // Thêm trường chung cho tất cả vai trò (ngoại trừ company)
        if ($role !== 'company') {
            $data['full_name'] = $user->full_name;
            $data['gender'] = $user->gender;
            $data['dob'] = $user->dob;
        } else {
            $data['name'] = $user->name;
        }

        // Thêm trường riêng theo vai trò
        if ($role === 'student') {
            $data['student_id'] = $user->student_id;
            $data['phone_number'] = $user->phone_number ?? null;
            $data['class_id'] = $user->class_id;
            $data['gpa'] = $user->gpa ?? null;
        } elseif ($role === 'lecturer') {
            $data['lecturer_id'] = $user->lecturer_id;
            $data['phone_number'] = $user->phone_number ?? null;
            $data['degree'] = $user->degree;
            $data['department'] = $user->department;
        } elseif ($role === 'faculty_staff') {
            $data['faculty_staff_id'] = $user->faculty_staff_id;
            $data['phone_number'] = $user->phone_number ?? null;
        } elseif ($role === 'admin') {
            $data['admin_id'] = $user->admin_id;
        } elseif ($role === 'company') {
            $data['user_code'] = $user->user_code ?? null;
            $data['address'] = $user->address;
            $data['website'] = $user->website;
            $data['is_partnered'] = (bool) $user->is_partnered;
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
    }

    // UC10 - Thêm tài khoản
    // POST /admin/accounts
    public function store(AccountRequest $request): JsonResponse
    {
        $username = $request->input('username');
        $email = $request->input('email');
        $role = $request->input('role');
        $usercode = $request->input('usercode');

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

        // Kiểm tra trùng usercode (mã) trong bảng tương ứng
        if ($usercode && $modelClass::where('usercode', $usercode)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Mã đã tồn tại.',
            ], 422);
        }

        // Tạo tài khoản — mật khẩu mặc định = username
        $userData = [
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($username),
            'usercode' => $usercode ?? $this->generateUsercode($role),
            'first_login' => 1,
        ];

        // Thêm trường dùng chung cho tất cả vai trò (ngoại trừ company)
        if ($role !== 'company') {
            $userData['full_name'] = $request->input('full_name', $username);
            $userData['gender'] = $request->input('gender');
            $userData['dob'] = $request->input('dob');
        } else {
            $userData['name'] = $request->input('name', $username);
        }

        // Thêm trường riêng theo vai trò
        if ($role === 'student') {
            $userData['phone_number'] = $request->input('phone_number');
            $userData['class_id'] = $request->input('class_id');
            if ($request->has('gpa')) {
                $userData['gpa'] = $request->input('gpa');
            }
        } elseif ($role === 'lecturer') {
            $userData['phone_number'] = $request->input('phone_number');
            $userData['degree'] = $request->input('degree');
            $userData['department'] = $request->input('department');
        } elseif ($role === 'faculty_staff') {
            // Faculty staff không có phone_number trong requirements, nhưng model có
            // Nếu gửi kèm sẽ thêm, nếu không thì bỏ qua
            if ($request->has('phone_number')) {
                $userData['phone_number'] = $request->input('phone_number');
            }
        } elseif ($role === 'company') {
            $userData['user_code'] = $request->input('user_code');
            $userData['address'] = $request->input('address');
            $userData['website'] = $request->input('website');
            $userData['is_partnered'] = (bool) $request->input('is_partnered');
        }

        // Thêm is_active nếu bảng có
        if (in_array($role, self::HAS_ACTIVE_FIELD)) {
            $userData['is_active'] = 1;
        }

        $user = $modelClass::create($userData);

        // Tạo login record
        $roleModel = Role::where('role_name', $role)->first();

        // Kiểm tra role có tồn tại hay không
        if (!$roleModel) {
            return response()->json([
                'success' => false,
                'message' => 'Vai trò không tồn tại trong hệ thống. Vui lòng liên hệ quản trị viên.',
            ], 400);
        }

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
                'status' => in_array($role, self::HAS_ACTIVE_FIELD) ? 'active' : 'active',
            ],
        ], 201);
    }

    // UC11 - Sửa tài khoản
    // PUT /admin/accounts/{id}?role=student
    public function update(AccountRequest $request, $id): JsonResponse
    {
        $role = $request->query('role');
        $username = $request->input('username');
        $email = $request->input('email');
        $usercode = $request->input('usercode');
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

        // Kiểm tra: Admin không được sửa chính tài khoản của mình
        if ($role === 'admin') {
            $currentAdmin = $request->user();
            if ($currentAdmin && $currentAdmin->admin_id == $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể sửa tài khoản của chính bạn.',
                ], 403);
            }
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

        // Kiểm tra trùng usercode (trừ chính nó)
        if (
            $usercode && $modelClass::where('usercode', $usercode)
                ->where($primaryKey, '!=', $id)
                ->exists()
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Mã đã tồn tại.',
            ], 422);
        }

        // Cập nhật thông tin cơ bản
        $updateData = [
            'username' => $username,
            'email' => $email,
        ];

        // Cập nhật usercode nếu được cung cấp
        if ($usercode) {
            $updateData['usercode'] = $usercode;
        }

        // Cập nhật trạng thái (chỉ bảng có is_active)
        if (in_array($role, self::HAS_ACTIVE_FIELD) && $status) {
            $updateData['is_active'] = $status === 'active' ? 1 : 0;
        }

        // Reset mật khẩu về mặc định = username
        if ($resetPass) {
            $updateData['password'] = Hash::make($username);
            $updateData['first_login'] = 1;
        }

        // Cập nhật các trường tùy chọn theo role
        if ($role !== 'company') {
            if ($request->has('full_name')) {
                $updateData['full_name'] = $request->input('full_name');
            }
            if ($request->has('gender')) {
                $updateData['gender'] = $request->input('gender');
            }
            if ($request->has('dob')) {
                $updateData['dob'] = $request->input('dob');
            }
        } else {
            if ($request->has('name')) {
                $updateData['name'] = $request->input('name');
            }
        }

        // Cập nhật trường riêng theo role
        if ($role === 'student') {
            if ($request->has('phone_number')) {
                $updateData['phone_number'] = $request->input('phone_number');
            }
            if ($request->has('class_id')) {
                $updateData['class_id'] = $request->input('class_id');
            }
            if ($request->has('gpa')) {
                $updateData['gpa'] = $request->input('gpa');
            }
        } elseif ($role === 'lecturer') {
            if ($request->has('phone_number')) {
                $updateData['phone_number'] = $request->input('phone_number');
            }
            if ($request->has('degree')) {
                $updateData['degree'] = $request->input('degree');
            }
            if ($request->has('department')) {
                $updateData['department'] = $request->input('department');
            }
        } elseif ($role === 'faculty_staff') {
            if ($request->has('phone_number')) {
                $updateData['phone_number'] = $request->input('phone_number');
            }
        } elseif ($role === 'company') {
            if ($request->has('address')) {
                $updateData['address'] = $request->input('address');
            }
            if ($request->has('website')) {
                $updateData['website'] = $request->input('website');
            }
            if ($request->has('is_partnered')) {
                $updateData['is_partnered'] = (bool) $request->input('is_partnered');
            }
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

    // UC12 - Xoá tài khoản (vô hiệu hoá)
    // DELETE /admin/accounts/{id}?role=student
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

        // Kiểm tra: Admin không được xoá chính tài khoản của mình
        if ($role === 'admin') {
            $currentAdmin = $request->user();
            if ($currentAdmin && $currentAdmin->admin_id == $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xoá tài khoản của chính bạn.',
                ], 403);
            }
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

    // Helper: Tạo usercode tự động
    private function generateUsercode(string $role): string
    {
        $prefix = match ($role) {
            'student' => 'SV',
            'lecturer' => 'GV',
            'faculty_staff' => 'NV',
            'admin' => 'AD',
            'company' => 'DN',
            default => 'USR',
        };

        $count = self::ROLE_MODELS[$role]::count() + 1;
        return $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
