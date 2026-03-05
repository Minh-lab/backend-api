<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LecturerRequest extends Model
{
    protected $table = 'lecturer_requests';

    protected $primaryKey = 'request_id';

    protected $fillable = [
        'lecturer_id',
        'updated_topic_id',
        'topic_id',
        'type',
        'status',
        'title',
        'description',
        'file_path',
        'start_date',
        'end_date',
        'faculty_feedback',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    // Các loại yêu cầu
    const TYPE_LEAVE_REQ  = 'LEAVE_REQ';
    const TYPE_TOPIC_ADD  = 'TOPIC_ADD';
    const TYPE_TOPIC_EDIT = 'TOPIC_EDIT';
    const TYPE_TOPIC_DEL  = 'TOPIC_DEL';

    // Các trạng thái
    const STATUS_PENDING  = 'PENDING';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_REJECTED = 'REJECTED';

    // ===================== RELATIONSHIPS =====================

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id', 'lecturer_id');
    }

    public function leave()
    {
        return $this->hasOne(LecturerLeave::class, 'request_id', 'request_id');
    }

    public function updatedTopic()
    {
        return $this->belongsTo(UpdatedTopic::class, 'updated_topic_id', 'updated_topic_id');
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id', 'topic_id');
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

    public function scopeLeaveRequests($query)
    {
        return $query->where('type', self::TYPE_LEAVE_REQ);
    }

    public function scopeTopicRequests($query)
    {
        return $query->whereIn('type', [
            self::TYPE_TOPIC_ADD,
            self::TYPE_TOPIC_EDIT,
            self::TYPE_TOPIC_DEL,
        ]);
    }
}
