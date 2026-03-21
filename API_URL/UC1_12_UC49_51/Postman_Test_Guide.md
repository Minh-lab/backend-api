Hướng dẫn test API trên Postman (UC1-12 & UC49-51)

Dưới đây là danh sách các API từ Use Case 1 đến 12, và từ 49 đến 51.

LƯU Ý CHUNG VỀ TOKEN (Authorization):
Hầu hết các API đều yêu cầu bạn phải đăng nhập trước. 
1. Bạn gọi API Đăng nhập (UC1) đầu tiên.
2. Copy chuỗi token hiển thị trong kết quả trả về.
3. Trong Postman của các API tiếp theo, chuyển sang tab Headers, thêm key Authorization và value là Bearer <dán_token_của_bạn_vào_đây>.

---

Nhóm Authentication (UC 1 - 5)

UC1: Đăng nhập vào hệ thống
URL: POST http://localhost:8000/api/v1/auth/login
Body (JSON): (Ví dụ tài khoản Sinh viên)
{
  "email": "6351012001@sv.tlu.edu.vn",
  "password": "Student@123"
}

UC2: Đăng xuất khỏi hệ thống
(Yêu cầu: Phải đăng nhập trước để lấy token)
URL: DELETE http://localhost:8000/api/v1/auth/logout
Headers: Authorization: Bearer <token>
Body: Không có (hoặc {})

UC3: Quên mật khẩu
Quá trình này gồm 3 bước thực hiện 3 API liên tiếp.
1. Gửi OTP:
URL: POST http://localhost:8000/api/v1/password/otp-requests
Body (JSON):
{
  "email": "6351012001@sv.tlu.edu.vn"
}
2. Xác thực OTP:
URL: POST http://localhost:8000/api/v1/password/otp/verifications
Body (JSON): (Lấy OTP từ email hoặc DB)
{
  "email": "6351012001@sv.tlu.edu.vn",
  "otp": "123456"
}
3. Đặt lại mật khẩu mới:
URL: PUT http://localhost:8000/api/v1/password/reset
Body (JSON):
{
  "email": "6351012001@sv.tlu.edu.vn",
  "otp": "123456",
  "password": "NewPassword@456",
  "password_confirmation": "NewPassword@456"
}

UC4: Đổi mật khẩu
(Yêu cầu: Phải đăng nhập trước, điền token vào tab Headers)
URL: PUT http://localhost:8000/api/v1/profile/password
Headers: Authorization: Bearer <token>
Body (JSON):
{
  "current_password": "Student@123",
  "password": "NewPassword@789",
  "password_confirmation": "NewPassword@789"
}

UC5: Xem thông tin cá nhân
(Yêu cầu: Đã đăng nhập)
URL: GET http://localhost:8000/api/v1/profile
Headers: Authorization: Bearer <token>

---

Nhóm User Management (UC 6 - 8)

UC6: Lấy & Cập nhật chuyên môn (Giảng viên)
(Yêu cầu: Đăng nhập tài khoản với vai trò Lecturer mới test được - Email: nguyenvana@tlu.edu.vn / Password: Lecturer@123)

1. Xem chuyên môn:
URL: GET http://localhost:8000/api/v1/lecturer/expertises
Headers: Authorization: Bearer <token_cua_giang_vien>

2. Cập nhật chuyên môn:
URL: PUT http://localhost:8000/api/v1/lecturer/expertises
Headers: Authorization: Bearer <token_cua_giang_vien>
Body (JSON):
{
  "expertise_ids": [1, 2, 3]
}

UC7: Tạo yêu cầu nghỉ phép dài hạn (Giảng viên)
(Yêu cầu: Dùng token đăng nhập của Lecturer)
URL: POST http://localhost:8000/api/v1/lecturer/leave-requests
Headers: Authorization: Bearer <token_cua_giang_vien>
Body (form-data):
- title: Nghỉ dự hội thảo
- description: Xin nghỉ hội thảo AI
- start_date: 2026-06-01
- end_date: 2026-06-15
- file: (Chọn 1 file bất kỳ hợp lệ)

UC8: Quản lý thông báo
(Yêu cầu: Dùng token đăng nhập của bất kỳ user nào)
1. Lấy danh sách thông báo:
URL: GET http://localhost:8000/api/v1/notifications?status=unread
Headers: Authorization: Bearer <token>

2. Đánh dấu đã xem:
URL: PUT http://localhost:8000/api/v1/notifications/{id}/read
(Thay {id} bằng ID thực tế, ví dụ 1, 2 hoặc 3)
Headers: Authorization: Bearer <token>

---

Nhóm Admin Management (UC 9 - 12)

(Yêu cầu: Đăng nhập bằng Admin admin@tlu.edu.vn / Admin@123 để lấy Token Admin)

UC9: Tìm kiếm & Xem chi tiết tài khoản
URL: GET http://localhost:8000/api/v1/admin/accounts?role=student
Headers: Authorization: Bearer <admin_token>

UC10: Thêm tài khoản
URL: POST http://localhost:8000/api/v1/admin/accounts
Headers: Authorization: Bearer <admin_token>
Body (JSON):
{
  "username": "sinhvien_test",
  "email": "sinhvien_test@sv.tlu.edu.vn",
  "role": "student",
  "student_id": "6351012999",
  "full_name": "Nguyễn Văn Sinh Viên",
  "gender": "male",
  "dob": "2003-01-01",
  "phone_number": "0912345678",
  "class_id": 1,
  "gpa": 3.5,
  "usercode": "SV999"
}

UC11: Sửa tài khoản
URL: PUT http://localhost:8000/api/v1/admin/accounts/{id}?role=student
(Thay {id} bằng ID hợp lệ)
Headers: Authorization: Bearer <admin_token>
Body (JSON):
{
  "username": "sinhvien_test_upd",
  "email": "sinhvien_test_upd@sv.tlu.edu.vn",
  "status": "active",
  "full_name": "Nguyên Cập Nhật",
  "gender": "male",
  "dob": "2003-01-01",
  "phone_number": "0912345679",
  "class_id": 2,
  "gpa": 3.8,
  "reset_password": false
}

UC12: Xóa (vô hiệu hóa) tài khoản
URL: DELETE http://localhost:8000/api/v1/admin/accounts/{id}?role=student
Headers: Authorization: Bearer <admin_token>

---

Nhóm Faculty (UC 49 - 51)

(Yêu cầu: Đăng nhập tài khoản Faculty Staff. Ví dụ: vanphong01@tlu.edu.vn / Staff@123)

UC49: Thêm năm học và học kỳ mới
URL: POST http://localhost:8000/api/v1/faculty/semesters
Headers: Authorization: Bearer <faculty_token>
Body (JSON):
{
    "year_name": "2026-2027",
    "semester_name": "Kỳ 1",
    "start_date": "2026-09-01",
    "end_date": "2027-01-15"
}

UC50: Thêm mốc thời gian (Đã sửa ngày để không bị lỗi trùng lặp)
(Hệ thống bắt buộc mốc thời gian mới phải diễn ra sau ngày kết thúc của mốc trước đó)
URL: POST http://localhost:8000/api/v1/faculty/milestones
Headers: Authorization: Bearer <faculty_token>
Body (JSON):
{
    "semester_id": 8,
    "phase_name": "Đăng ký đợt thực tập bổ sung",
    "description": "Dành cho sinh viên chưa kịp đăng ký đợt 1",
    "type": "INTERNSHIP",
    "start_date": "2025-06-01 00:00:00",
    "end_date": "2025-06-15 23:59:00"
}

UC51: Cập nhật mốc thời gian
URL: PUT http://localhost:8000/api/v1/faculty/milestones/{id}
(Thay {id} bằng ID thực tế của milestone mới tạo ra ở trên)
Headers: Authorization: Bearer <faculty_token>
Body (JSON):
{
    "phase_name": "Đăng ký đợt thực tập bổ sung (Đã gia hạn)",
    "description": "Gia hạn thêm",
    "type": "INTERNSHIP",
    "start_date": "2025-06-01 00:00:00",
    "end_date": "2025-06-20 23:59:00"
}
