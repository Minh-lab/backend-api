<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouncilMember extends Model
{
    protected $table = 'council_members';

    // Bảng pivot, không có id riêng
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        'council_id',
        'lecturer_id',
        'position',
    ];

    // Các chức vụ trong hội đồng
    const POSITION_CHAIRMAN          = 'chairman';
    const POSITION_SECRETARY         = 'secretary';
    const POSITION_MEMBER            = 'member';
    const POSITION_REVIEWER_MEMBER   = 'reviewer_member';

    // ===================== RELATIONSHIPS =====================

    public function council()
    {
        return $this->belongsTo(Council::class, 'council_id', 'council_id');
    }

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id', 'lecturer_id');
    }
}
