<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpdatedTopic extends Model
{
    protected $table = 'updated_topics';

    protected $primaryKey = 'updated_topic_id';

    // Không có timestamps
    public $timestamps = false;

    protected $fillable = [
        'expertise_id',
        'title',
        'description',
        'technologies',
    ];

    // ===================== RELATIONSHIPS =====================

    public function expertise()
    {
        return $this->belongsTo(Expertise::class, 'expertise_id', 'expertise_id');
    }

    public function lecturerRequest()
    {
        return $this->hasOne(LecturerRequest::class, 'updated_topic_id', 'updated_topic_id');
    }
}
