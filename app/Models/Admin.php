<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens;

    protected $primaryKey = 'admin_id';

    protected $fillable = [
        'usercode',
        'username',
        'password',
        'email',
        'full_name',
    ];

    protected $hidden = [
        'password',
    ];
}
