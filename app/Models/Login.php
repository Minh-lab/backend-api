<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Login extends Model
{
    protected $primaryKey = 'login_id';

    protected $fillable = [
        'user_id',
        'role_id',
        'login_attempts',
        'lockout_until',
    ];

    protected $casts = [
        'lockout_until' => 'datetime',
    ];

    // ===================== RELATIONSHIPS =====================

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    // ===================== HELPERS =====================

    /**
     * Kiểm tra tài khoản có đang bị khóa không
     */
    public function isLocked(): bool
    {
        return $this->lockout_until !== null && $this->lockout_until->isFuture();
    }
}
