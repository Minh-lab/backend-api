<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProposedTopic extends Model
{
    protected $table = 'proposed_topics';

    protected $primaryKey = 'proposed_topic_id';

    // Không có timestamps
    public $timestamps = false;

    protected $fillable = [
        'expertise_id',
        'proposed_title',
        'proposed_description',
        'technologies',
    ];

    // ===================== RELATIONSHIPS =====================

    public function expertise()
    {
        return $this->belongsTo(Expertise::class, 'expertise_id', 'expertise_id');
    }

    public function capstoneRequests()
    {
        return $this->hasMany(CapstoneRequest::class, 'proposed_topic_id', 'proposed_topic_id');
    }
}
