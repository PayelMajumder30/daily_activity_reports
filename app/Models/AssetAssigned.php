<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetAssigned extends Model
{
    //
    public $table = 'asset_assigneds';
    protected $fillable = ['asset_inventory_id', 'custodian_id', 'user_type', 'operator_name', 'assigned_date', 'released_date', 'is_active', 'remarks', 'created_by'];

    public function assetInventory(): BelongsTo {
        return $this->belongsTo(AssetInventory::class);
    }

    public function custodian(): BelongsTo {
        return $this->belongsTo(Custodian::class);
    }

    public function createdBy(): BelongsTo {
        return $this->belongsTo(User::class, 'created_by');
    }
}
