<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $primaryKey = 'notification_id';

    protected $fillable = [
        'title',
        'content',
    ];

    // ===================== RELATIONSHIPS =====================

    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class, 'notification_id', 'notification_id');
    }
}
