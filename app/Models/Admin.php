<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Admin extends Authenticatable
{
     use HasApiTokens, HasFactory;

    protected $primaryKey = 'admin_id';

   protected $fillable = [
    'usercode', 'username', 'password',
    'email', 'full_name',
    'gender', 'dob',   
];

    protected $hidden = [
        'password',
    ];
}
