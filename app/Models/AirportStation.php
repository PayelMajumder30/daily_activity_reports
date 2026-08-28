<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AirportStation extends Model
{
    //
    public $table = 'airport_stations';
    protected $fillable = ['location_id','station_name','short_name', 'status'];

    public function location(): BelongsTo {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function custodian(): HasMany
    {
        return $this->hasMany(Custodian::class);
    }

    public function assetInventory(): HasMany
    {
        return $this->hasMany(AssetInventory::class);
    }

}
