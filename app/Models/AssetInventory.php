<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetInventory extends Model
{
    //
    public $table = 'asset_inventories';
    protected $fillable = ['tag_no', 'asset_model_id', 'location_id', 'po_number', 'serial_no', 'installation_date', 'warranty_year',
     'warranty_end', 'asset_status', 'remarks', 'created_by', 'status'];

    protected $casts = [
        'installation_date' => 'date',
        'warranty_end'      => 'date',
    ];

    public function assetModel(): BelongsTo {
        return $this->belongsTo(AssetModel::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function issueHistory(): HasMany
    {
        return $this->hasMany(AssetIssueRegister::class, 'asset_inventory_id');
    }

    public function assetTransfers(): HasMany
    {
        return $this->hasMany(AssetTransfer::class, 'asset_inventory_id');
    }
   
}
