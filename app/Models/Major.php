<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Major extends Model
{
    protected $primaryKey = 'major_id';

    protected $fillable = [
        'major_name',
    ];

    // ===================== RELATIONSHIPS =====================

    public function classes()
    {
        return $this->hasMany(Classes::class, 'major_id', 'major_id');
    }
}
