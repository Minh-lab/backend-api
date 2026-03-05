<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CapstoneRequest extends Model
{
    protected $table = 'capstone_requests';

    protected $primaryKey = 'capstone_request_id';

    protected $fillable = [
        'proposed_topic_id',
        'capstone_id',
        'lecturer_id',
        'topic_id',
        'type',
        'status',
        'student_message',
        'lecturer_feedback',
        'file_path',
    ];

    // Các loại yêu cầu
    const TYPE_LECTURER_REG = 'LECTURER_REG';
    const TYPE_TOPIC_PROP   = 'TOPIC_PROP';
    const TYPE_TOPIC_BANK   = 'TOPIC_BANK';
    const TYPE_CANCEL_REQ   = 'CANCEL_REQ';

    // Các trạng thái
    const STATUS_PENDING_TEACHER  = 'PENDING_TEACHER';
    const STATUS_PENDING_FACULTY  = 'PENDING_FACULTY';
    const STATUS_APPROVED         = 'APPROVED';
    const STATUS_REJECTED         = 'REJECTED';

    // ===================== RELATIONSHIPS =====================

    public function capstone()
    {
        return $this->belongsTo(Capstone::class, 'capstone_id', 'capstone_id');
    }

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id', 'lecturer_id');
    }

    public function proposedTopic()
    {
        return $this->belongsTo(ProposedTopic::class, 'proposed_topic_id', 'proposed_topic_id');
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id', 'topic_id');
    }

    // ===================== SCOPES =====================

    public function scopePendingTeacher($query)
    {
        return $query->where('status', self::STATUS_PENDING_TEACHER);
    }

    public function scopePendingFaculty($query)
    {
        return $query->where('status', self::STATUS_PENDING_FACULTY);
    }
}
