<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CapstoneReviewer extends Model
{
    protected $table = 'capstone_reviewers';

    // Bảng pivot, không có id riêng
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        'capstone_id',
        'lecturer_id',
        'opponent_grade',
    ];

    protected $casts = [
        'opponent_grade' => 'decimal:2',
    ];

    // ===================== RELATIONSHIPS =====================

    public function capstone()
    {
        return $this->belongsTo(Capstone::class, 'capstone_id', 'capstone_id');
    }

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id', 'lecturer_id');
    }
}
