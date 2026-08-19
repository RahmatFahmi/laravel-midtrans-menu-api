<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDevice extends Model
{
    protected $fillable = [
        'user_id',
        'device_uuid',
        'fcm_token',
        'platform',
        'is_active'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
