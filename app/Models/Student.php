<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Authenticatable
{
    use HasApiTokens, HasFactory;


    protected $primaryKey = 'student_id';

    protected $fillable = [
        'usercode',
        'username',
        'password',
        'email',
        'is_active',
        'first_login',
        'full_name',
        'gender',
        'dob',
        'phone_number',
        'class_id',
        'gpa',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'first_login' => 'boolean',
        'dob' => 'date',
        'gpa' => 'decimal:2',
    ];

    //  RELATIONSHIPS 
    public function studentClass()
    {
        return $this->belongsTo(\App\Models\Classes::class, 'class_id', 'class_id');
    }

    public function capstones()
    {
        return $this->hasMany(Capstone::class, 'student_id', 'student_id');
    }

    public function internships()
    {
        return $this->hasMany(Internship::class, 'student_id', 'student_id');
    }

    // ===================== SCOPES =====================

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
