<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Capstone extends Model
{
    protected $primaryKey = 'capstone_id';

    protected $fillable = [
        'topic_id',
        'student_id',
        'lecturer_id',
        'council_id',
        'semester_id',
        'status',
        'instructor_grade',
        'council_grade',
        'defense_order',
    ];

    protected $casts = [
        'instructor_grade' => 'decimal:2',
        'council_grade'    => 'decimal:2',
    ];

    // Các trạng thái đồ án
    const STATUS_INITIALIZED        = 'INITIALIZED';
    const STATUS_LECTURER_APPROVED  = 'LECTURER_APPROVED';
    const STATUS_TOPIC_APPROVED     = 'TOPIC_APPROVED';
    const STATUS_REPORTING          = 'REPORTING';
    const STATUS_OFFICIAL_SUBMITTED = 'OFFICIAL_SUBMITTED';
    const STATUS_REVIEW_ELIGIBLE    = 'REVIEW_ELIGIBLE';
    const STATUS_DEFENSE_ELIGIBLE   = 'DEFENSE_ELIGIBLE';
    const STATUS_CANCEL             = 'CANCEL';
    const STATUS_FAILED             = 'FAILED';
    const STATUS_COMPLETED          = 'COMPLETED';

    // ===================== RELATIONSHIPS =====================

    public function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id', 'topic_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id', 'lecturer_id');
    }

    public function council()
    {
        return $this->belongsTo(Council::class, 'council_id', 'council_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }

    public function requests()
    {
        return $this->hasMany(CapstoneRequest::class, 'capstone_id', 'capstone_id');
    }

    public function reviewers()
    {
        return $this->hasMany(CapstoneReviewer::class, 'capstone_id', 'capstone_id');
    }

    public function reports()
    {
        return $this->hasMany(CapstoneReport::class, 'capstone_id', 'capstone_id');
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
     * Tính điểm tổng kết đồ án
     * Công thức: 30% instructor + 70% council (tuỳ chỉnh theo quy định)
     */
    public function getFinalGradeAttribute(): ?float
    {
        if ($this->instructor_grade === null || $this->council_grade === null) {
            return null;
        }

        return round($this->instructor_grade * 0.3 + $this->council_grade * 0.7, 2);
    }
}
