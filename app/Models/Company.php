<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Company extends Authenticatable
{
    use HasApiTokens;

    protected $primaryKey = 'company_id';

    protected $fillable = [
        'usercode',
        'username',
        'password',
        'email',
        'is_active',
        'first_login',
        'name',
        'address',
        'website',
        'is_partnered',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'first_login'  => 'boolean',
        'is_partnered' => 'boolean',
    ];

    // ===================== RELATIONSHIPS =====================

    public function internships()
    {
        return $this->hasMany(Internship::class, 'company_id', 'company_id');
    }

    public function internshipRequests()
    {
        return $this->hasMany(InternshipRequest::class, 'company_id', 'company_id');
    }
}
