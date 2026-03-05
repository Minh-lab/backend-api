<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $table = 'user_notifications';

    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        'notification_id',
        'user_id',   // ID của student/lecturer/admin tuỳ role
        'role_id',   // phân biệt loại user
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notification_id', 'notification_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }
}