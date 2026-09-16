<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetOutstationHistory extends Model
{
    //
    public $table = 'asset_outstation_history';
    protected $fillable = ['asset_inventory_id', 'from_location_id', 'from_station_id', 'to_location_id', 'to_station_id', 'outstation_date', 'created_by'];

    protected $casts = ['outstation_date'   => 'date'];
           
    public function assetInventory(): BelongsTo
    {
        return $this->belongsTo(AssetInventory::class, 'asset_inventory_id');     
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');                        
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');                        
    }

    public function fromStation(): BelongsTo
    {
        return $this->belongsTo(AirportStation::class, 'from_station_id');         
    }

    public function toStation(): BelongsTo
    {
        return $this->belongsTo(AirportStation::class, 'to_station_id');         
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');          
    }
}
