<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventLog extends Model
{
    //
    protected $fillable = [
        'user_id',
        'module',
        'action',
        'record_id',
        'description',
        'ip_address',
        'browser',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
