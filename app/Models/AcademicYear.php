<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $table = 'academic_years';

    protected $primaryKey = 'year_id';

    protected $fillable = [
        'year_name',
        'start_year',
        'end_year',
    ];

    // ===================== RELATIONSHIPS =====================

    public function semesters()
    {
        return $this->hasMany(Semester::class, 'year_id', 'year_id');
    }
}
