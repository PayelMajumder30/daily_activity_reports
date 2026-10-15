<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetReplacementHistory extends Model
{
    //
    public $table = 'asset_replacement_histories';
    protected $fillable = ['asset_inventory_id', 'old_asset_model_id', 'new_asset_model_id', 'old_serial_no', 'new_serial_no', 'replaced_by', 'replaced_at', 'remarks'];

    protected $casts = [
        'replaced_at' => 'datetime',
    ];

    public function assetInventory(): BelongsTo {
        return $this->belongsTo(AssetInventory::class, 'asset_inventory_id');
    }

    public function oldAssetModel(): BelongsTo {
        return $this->belongsTo(AssetModel::class, 'old_asset_model_id');
    }

    public function newAssetModel(): BelongsTo {
        return $this->belongsTo(AssetModel::class, 'new_asset_model_id');
    }

    public function replacedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replaced_by');
    }
}
