<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetModel extends Model
{
    //

    public $table = 'asset_models';
    protected $fillable = ['asset_type_id','model_name', 'manufacturer'];

    public function assetType(): BelongsTo {
        return $this->belongsTo(AssetType::class);
    }

    public function assetInventory(): Hasmany
    {
        return $this->hasMany(AssetInventory::class);
    }
}
