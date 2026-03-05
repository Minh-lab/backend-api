<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $table = 'password_resets';

    protected $primaryKey = 'password_reset_id';

    // Chỉ có created_at, không có updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'role_id',
        'otp',
        'expired_at',
        'is_used',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'is_used'    => 'boolean',
    ];

    // ===================== RELATIONSHIPS =====================

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    // ===================== HELPERS =====================

    /**
     * Kiểm tra OTP còn hiệu lực không
     */
    public function isValid(): bool
    {
        return !$this->is_used && $this->expired_at->isFuture();
    }
}
