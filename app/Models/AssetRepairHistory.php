<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetRepairHistory extends Model
{
    //
    public $table = 'asset_repair_histories';
    
    protected $fillable = ['asset_inventory_id', 'vendor_name', 'send_date', 'return_date', 'remarks', 'created_by'];

    protected $casts = [
        'send_date'   => 'date',
        'return_date' => 'date',
    ];

    public function assetInventory(): BelongsTo
    {
        return $this->belongsTo(AssetInventory::class, 'asset_inventory_id');     
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');          
    }

}
