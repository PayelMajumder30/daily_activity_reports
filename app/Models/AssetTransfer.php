<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetTransfer extends Model
{
    //
    public $table = 'asset_transfers';
    protected $fillable = ['asset_inventory_id', 'from_custodian_id', 'to_custodian_id', 'transfer_date', 'created_by', 'remarks'];

    protected $casts = [
        'transfer_date'  => 'date',
    ];

    public function assetInventory(): BelongsTo
    {
        return $this->belongsTo(AssetInventory::class, 'asset_inventory_id');
    }

    public function fromCustodian(): BelongsTo
    {
        return $this->belongsTo(Custodian::class,'from_custodian_id');    
    }

    public function toCustodian(): BelongsTo
    {
        return $this->belongsTo(Custodian::class,'to_custodian_id');    
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

}
