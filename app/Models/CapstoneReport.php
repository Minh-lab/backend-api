<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CapstoneReport extends Model
{
    protected $table = 'capstone_reports';

    protected $primaryKey = 'report_id';

    protected $fillable = [
        'capstone_id',
        'milestone_id',
        'status',
        'file_path',
        'lecturer_feedback',
        'submission_date',
    ];

    protected $casts = [
        'submission_date' => 'datetime',
    ];

    // Các trạng thái báo cáo
    const STATUS_PENDING  = 'PENDING';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_REJECTED = 'REJECTED';

    // ===================== RELATIONSHIPS =====================

    public function capstone()
    {
        return $this->belongsTo(Capstone::class, 'capstone_id', 'capstone_id');
    }

    public function milestone()
    {
        return $this->belongsTo(Milestone::class, 'milestone_id', 'milestone_id');
    }

    // ===================== SCOPES =====================

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }
}
