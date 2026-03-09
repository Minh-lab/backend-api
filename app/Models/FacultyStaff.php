<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class FacultyStaff extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $table = 'faculty_staffs';

    protected $primaryKey = 'faculty_staff_id';

    protected $fillable = [
        'usercode',
        'username',
        'password',
        'email',
        'full_name',
        'gender',
        'dob',
        'phone_number',
    ];

    protected $hidden = [
        'password',
    ];

    // ===================== RELATIONSHIPS =====================

    public function topics()
    {
        return $this->hasMany(Topic::class, 'faculty_staff_id', 'faculty_staff_id');
    }
}
