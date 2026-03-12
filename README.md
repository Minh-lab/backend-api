PTDAPM API v1 (UC1-UC12)

├── Authentication
│   ├── Login (POST) http://localhost:8000/api/v1/auth/login
│   ├── Logout (DELETE) http://localhost:8000/api/v1/auth/logout
│   ├── Forgot Password - OTP Request (POST) http://localhost:8000/api/v1/password/otp-requests
│   ├── Forgot Password - Verify OTP (POST) http://localhost:8000/api/v1/password/otp/verifications
│   ├── Forgot Password - Reset (PUT) http://localhost:8000/api/v1/password/reset
│   ├── Change Password (PUT) http://localhost:8000/api/v1/profile/password
│   └── Get Profile (GET) http://localhost:8000/api/v1/profile
│
├── Admin Management
│   ├── Search Accounts (GET) http://localhost:8000/api/v1/admin/accounts
│   ├── Create Account (POST) http://localhost:8000/api/v1/admin/accounts
│   ├── Update Account (PUT) http://localhost:8000/api/v1/admin/accounts/{id}
│   └── Delete Account (DELETE) http://localhost:8000/api/v1/admin/accounts/{id}
│
├── Lecturer
│   ├── Get Expertises (GET) http://localhost:8000/api/v1/lecturer/expertises
│   ├── Update Expertise (PUT) http://localhost:8000/api/v1/lecturer/expertises
│   └── Create Leave Request (POST) http://localhost:8000/api/v1/lecturer/leave-requests
│
└── Notifications
    ├── Get Notifications (GET) http://localhost:8000/api/v1/notifications
    └── Mark as Read (PUT) http://localhost:8000/api/v1/notifications/{id}/read

    