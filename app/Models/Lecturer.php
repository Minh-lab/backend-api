<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Lecturer extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $primaryKey = 'lecturer_id';

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
        'degree',
        'department',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'first_login' => 'boolean',
        'dob' => 'date',
    ];

    //  RELATIONSHIPS

    public function expertises()
    {
        return $this->belongsToMany(
            Expertise::class,
            'lecturer_expertises',
            'lecturer_id',
            'expertise_id'
        )->withPivot('created_at');
    }

    public function classes()
    {
        return $this->hasMany(Classes::class, 'lecturer_id', 'lecturer_id');
    }

    public function topics()
    {
        return $this->hasMany(Topic::class, 'lecturer_id', 'lecturer_id');
    }

    public function requests()
    {
        return $this->hasMany(LecturerRequest::class, 'lecturer_id', 'lecturer_id');
    }

    public function leaves()
    {
        return $this->hasManyThrough(
            LecturerLeave::class,
            LecturerRequest::class,
            'lecturer_id',
            'request_id',
            'lecturer_id',
            'request_id'
        );
    }

    public function capstones()
    {
        return $this->hasMany(Capstone::class, 'lecturer_id', 'lecturer_id');
    }

    public function capstoneReviewers()
    {
        return $this->hasMany(CapstoneReviewer::class, 'lecturer_id', 'lecturer_id');
    }

    public function internships()
    {
        return $this->hasMany(Internship::class, 'lecturer_id', 'lecturer_id');
    }

    public function councils()
    {
        return $this->belongsToMany(
            Council::class,
            'council_members',
            'lecturer_id',
            'council_id'
        )->withPivot('position')->withTimestamps();
    }

    // ===================== SCOPES =====================

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
