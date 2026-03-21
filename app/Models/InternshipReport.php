<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternshipReport extends Model
{
    protected $table = 'internship_reports';

    protected $primaryKey = 'report_id';

    protected $fillable = [
        'internship_id',
        'milestone_id',
        'status',
        'description',
        'lecturer_feedback',
        'file_path',
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

    public function internship()
    {
        return $this->belongsTo(Internship::class, 'internship_id', 'internship_id');
    }

    public function milestone()
    {
        return $this->belongsTo(Milestone::class, 'milestone_id', 'milestone_id');
    }

    // ===================== SCOPES =====================

    public function scopePending($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            'STATUS_PENDING',
        ]);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }
}
