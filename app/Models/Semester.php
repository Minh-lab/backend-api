<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $primaryKey = 'semester_id';

    protected $fillable = [
        'year_id',
        'semester_name',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    // ===================== RELATIONSHIPS =====================

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'year_id', 'year_id');
    }

    public function milestones()
    {
        return $this->hasMany(Milestone::class, 'semester_id', 'semester_id');
    }

    public function capstones()
    {
        return $this->hasMany(Capstone::class, 'semester_id', 'semester_id');
    }

    public function internships()
    {
        return $this->hasMany(Internship::class, 'semester_id', 'semester_id');
    }

    public function councils()
    {
        return $this->hasMany(Council::class, 'semester_id', 'semester_id');
    }

    // ===================== SCOPES =====================

    public function scopeCapstoneMilestones($query)
    {
        return $this->milestones()->where('type', 'CAPSTONE');
    }

    public function scopeInternshipMilestones($query)
    {
        return $this->milestones()->where('type', 'INTERNSHIP');
    }
}
