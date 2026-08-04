<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetTag extends Model
{
    //
    public $table = 'asset_tags';
    protected $fillable = ['tag_no', 'status'];

    public function assetInventory(): Hasmany
    {
        return $this->hasMany(AssetInventory::class);
    }
}
