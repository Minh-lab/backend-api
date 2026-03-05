<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Council extends Model
{
    protected $primaryKey = 'council_id';

    protected $fillable = [
        'semester_id',
        'name',
        'buildings',
        'rooms',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
    ];

    // ===================== RELATIONSHIPS =====================

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }

    public function members()
    {
        return $this->belongsToMany(
            Lecturer::class,
            'council_members',
            'council_id',
            'lecturer_id'
        )->withPivot('position')->withTimestamps();
    }

    public function councilMembers()
    {
        return $this->hasMany(CouncilMember::class, 'council_id', 'council_id');
    }

    public function capstones()
    {
        return $this->hasMany(Capstone::class, 'council_id', 'council_id');
    }

    // ===================== SCOPES =====================

    public function scopeBySemester($query, int $semesterId)
    {
        return $query->where('semester_id', $semesterId);
    }
}
