<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    protected $primaryKey = 'milestone_id';

    protected $fillable = [
        'semester_id',
        'phase_name',
        'description',
        'type',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    // Các loại milestone
    const TYPE_CAPSTONE    = 'CAPSTONE';
    const TYPE_INTERNSHIP  = 'INTERNSHIP';

  //relationships

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }

    public function capstoneReports()
    {
        return $this->hasMany(CapstoneReport::class, 'milestone_id', 'milestone_id');
    }

    public function internshipReports()
    {
        return $this->hasMany(InternshipReport::class, 'milestone_id', 'milestone_id');
    }


    public function scopeCapstone($query)
    {
        return $query->where('type', self::TYPE_CAPSTONE);
    }

    public function scopeInternship($query)
    {
        return $query->where('type', self::TYPE_INTERNSHIP);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('deadline', '>', now());
    }
}
