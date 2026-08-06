<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetInventory extends Model
{
    //
    public $table = 'asset_inventories';
    protected $fillable = ['asset_tag_id', 'asset_model_id', 'serial_no', 'purchase_date', 'warranty_end', 'asset_status', 'remarks', 'status'];

    public function assetModel(): BelongsTo {
        return $this->belongsTo(AssetModel::class);
    }

    public function assetTag(): BelongsTo {
        return $this->belongsTo(AssetTag::class);
    }

    public function assetAssigned(): Hasmany
    {
        return $this->hasMany(AssetAssigned::class);
    }
}
