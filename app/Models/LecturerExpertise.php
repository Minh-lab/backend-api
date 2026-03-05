<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LecturerExpertise extends Model
{
    protected $table = 'lecturer_expertises';

    // Bảng pivot, không có id riêng
    public $incrementing = false;
    protected $primaryKey = null;

    // Chỉ có created_at
    const UPDATED_AT = null;

    protected $fillable = [
        'lecturer_id',
        'expertise_id',
    ];

    // ===================== RELATIONSHIPS =====================

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id', 'lecturer_id');
    }

    public function expertise()
    {
        return $this->belongsTo(Expertise::class, 'expertise_id', 'expertise_id');
    }
}
