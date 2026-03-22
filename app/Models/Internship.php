<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Internship extends Model
{
    protected $primaryKey = 'internship_id';

    protected $fillable = [
        'student_id',
        'lecturer_id',
        'company_id',
        'semester_id',
        'status',
        'company_grade',
        'company_feedback',
        'university_feedback',
        'position',
        'university_grade',
    ];

    protected $casts = [
        'company_grade'    => 'decimal:2',
        'university_grade' => 'decimal:2',
    ];

    // Các trạng thái thực tập
    const STATUS_INITIALIZED       = 'INITIALIZED';
    const STATUS_PENDING           = 'PENDING';
    const STATUS_LECTURER_APPROVED = 'LECTURER_APPROVED';
    const STATUS_COMPANY_APPROVED  = 'COMPANY_APPROVED';
    const STATUS_INTERNING         = 'INTERNING';
    const STATUS_CANCEL            = 'CANCEL';
    const STATUS_FAILED            = 'FAILED';
    const STATUS_COMPLETED         = 'COMPLETED';

    // ===================== RELATIONSHIPS =====================

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id', 'lecturer_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }

    public function requests()
    {
        return $this->hasMany(InternshipRequest::class, 'internship_id', 'internship_id');
    }

    public function reports()
    {
        return $this->hasMany(InternshipReport::class, 'internship_id', 'internship_id');
    }

    // ===================== SCOPES =====================

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [
            self::STATUS_CANCEL,
            self::STATUS_FAILED,
            self::STATUS_COMPLETED,
        ]);
    }

    public function scopeBySemester($query, int $semesterId)
    {
        return $query->where('semester_id', $semesterId);
    }

    // ===================== HELPERS =====================

    /**
     * Tính điểm tổng kết thực tập
     * Công thức: 50% doanh nghiệp + 50% nhà trường (tuỳ chỉnh theo quy định)
     */
    public function getFinalGradeAttribute(): ?float
    {
        if ($this->company_grade === null || $this->university_grade === null) {
            return null;
        }

        return round($this->company_grade * 0.5 + $this->university_grade * 0.5, 2);
    }
}
