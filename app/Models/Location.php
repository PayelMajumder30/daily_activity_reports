<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Location extends Model
{
    //
    public $table = 'locations';
    protected $fillable = ['name','short_name', 'status'];

    public function assetInventory(): Hasmany
    {
        return $this->hasMany(AssetInventory::class);
    }

    public function custodian(): HasMany
    {
        return $this->hasMany(Custodian::class);
    }

    public function airportStation(): HasMany
    {
        return $this->hasMany(AirportStation::class, 'location_id');
    }

    public function retainedAssets(): HasMany
    {
        return $this->hasMany(AssetRetainedAsset::class, 'asset_inventory_id');
    }
}
