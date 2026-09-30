<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLocationPermission extends Model
{
    //
    public $table = 'user_location_permissions';
    protected $fillable = ['user_id', 'location_id', 'station_id'];

    public function user():BelongsTo {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function location():BelongsTo {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(AirportStation::class, 'station_id');
    }
}
