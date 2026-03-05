<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    protected $table = 'classes';

    protected $primaryKey = 'class_id';

    protected $fillable = [
        'lecturer_id',
        'class_name',
        'major_id',
    ];

    // ===================== RELATIONSHIPS =====================

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id', 'lecturer_id');
    }

    public function major()
    {
        return $this->belongsTo(Major::class, 'major_id', 'major_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id', 'class_id');
    }
}
