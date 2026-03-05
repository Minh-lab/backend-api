<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    protected $primaryKey = 'topic_id';

    protected $fillable = [
        'expertise_id',
        'lecturer_id',
        'faculty_staff_id',
        'title',
        'description',
        'technologies',
        'is_available',
        'is_bank_topic',
    ];

    protected $casts = [
        'is_available'  => 'boolean',
        'is_bank_topic' => 'boolean',
    ];

    // ===================== RELATIONSHIPS =====================

    public function expertise()
    {
        return $this->belongsTo(Expertise::class, 'expertise_id', 'expertise_id');
    }

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id', 'lecturer_id');
    }

    public function facultyStaff()
    {
        return $this->belongsTo(FacultyStaff::class, 'faculty_staff_id', 'faculty_staff_id');
    }

    public function capstones()
    {
        return $this->hasMany(Capstone::class, 'topic_id', 'topic_id');
    }

    public function capstoneRequests()
    {
        return $this->hasMany(CapstoneRequest::class, 'topic_id', 'topic_id');
    }

    // ===================== SCOPES =====================

    public function scopeAvailable($query)
    {
        return $query->where('is_available', 1);
    }

    public function scopeBankTopics($query)
    {
        return $query->where('is_bank_topic', 1);
    }

    public function scopeByExpertise($query, int $expertiseId)
    {
        return $query->where('expertise_id', $expertiseId);
    }
}
