<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expertise extends Model
{
    protected $primaryKey = 'expertise_id';

    protected $fillable = [
        'name',
        'description',
    ];

    // ===================== RELATIONSHIPS =====================

    public function lecturers()
    {
        return $this->belongsToMany(
            Lecturer::class,
            'lecturer_expertises',
            'expertise_id',
            'lecturer_id'
        )->withPivot('created_at');
    }

    public function topics()
    {
        return $this->hasMany(Topic::class, 'expertise_id', 'expertise_id');
    }

    public function updatedTopics()
    {
        return $this->hasMany(UpdatedTopic::class, 'expertise_id', 'expertise_id');
    }

    public function proposedTopics()
    {
        return $this->hasMany(ProposedTopic::class, 'expertise_id', 'expertise_id');
    }
}
