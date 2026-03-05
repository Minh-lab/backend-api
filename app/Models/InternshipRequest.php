<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternshipRequest extends Model
{
    protected $table = 'internship_requests';

    protected $primaryKey = 'internship_request_id';

    protected $fillable = [
        'internship_id',
        'proposed_company_id',
        'company_id',
        'type',
        'status',
        'student_message',
        'feedback',
        'file_path',
    ];

    // Các loại yêu cầu
    const TYPE_COMPANY_REG = 'COMPANY_REG';
    const TYPE_CANCEL_REQ  = 'CANCEL_REQ';

    // Các trạng thái
    const STATUS_PENDING_TEACHER  = 'PENDING_TEACHER';
    const STATUS_PENDING_FACULTY  = 'PENDING_FACULTY';
    const STATUS_PENDING_COMPANY  = 'PENDING_COMPANY';
    const STATUS_APPROVED         = 'APPROVED';
    const STATUS_REJECTED         = 'REJECTED';

    // ===================== RELATIONSHIPS =====================

    public function internship()
    {
        return $this->belongsTo(Internship::class, 'internship_id', 'internship_id');
    }

    public function proposedCompany()
    {
        return $this->belongsTo(ProposedCompany::class, 'proposed_company_id', 'proposed_company_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    // ===================== SCOPES =====================

    public function scopePendingTeacher($query)
    {
        return $query->where('status', self::STATUS_PENDING_TEACHER);
    }

    public function scopePendingCompany($query)
    {
        return $query->where('status', self::STATUS_PENDING_COMPANY);
    }
}
