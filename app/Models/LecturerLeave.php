<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LecturerLeave extends Model
{
    protected $table = 'lecturer_leaves';

    protected $primaryKey = 'leave_id';

    protected $fillable = [
        'request_id',
        'start_date',
        'end_date',
        'status',
        'delegate_completed',
    ];

    protected $casts = [
        'start_date'          => 'date',
        'end_date'            => 'date',
        'delegate_completed'  => 'boolean',
    ];

    // Các giá trị status hợp lệ
    const STATUS_APPROVED_PENDING = 'APPROVED_PENDING';
    const STATUS_LEAVE_ACTIVE     = 'LEAVE_ACTIVE';
    const STATUS_CANCELLED        = 'CANCELLED';
    const STATUS_COMPLETED        = 'COMPLETED';

    // ===================== RELATIONSHIPS =====================

    public function request()
    {
        return $this->belongsTo(LecturerRequest::class, 'request_id', 'request_id');
    }

    // ===================== SCOPES =====================

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_LEAVE_ACTIVE);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_APPROVED_PENDING);
    }
}
