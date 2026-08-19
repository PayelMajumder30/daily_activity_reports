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
}
