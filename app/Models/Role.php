<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $primaryKey = 'role_id';

    protected $fillable = [
        'role_name',
    ];

    // ===================== RELATIONSHIPS =====================

    public function logins()
    {
        return $this->hasMany(Login::class, 'role_id', 'role_id');
    }

    public function passwordResets()
    {
        return $this->hasMany(PasswordReset::class, 'role_id', 'role_id');
    }
}
