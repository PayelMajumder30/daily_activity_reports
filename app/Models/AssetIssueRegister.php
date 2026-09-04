<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetIssueRegister extends Model
{
    //
    public $table = 'asset_issue_registers';
    protected $fillable = ['asset_inventory_id', 'custodian_id', 'user_type', 'operator_name', 'issued_date', 'retained_date', 'transfer_date', 'returned_date', 'issue_status', 'remarks'];

    protected $casts = [
        'issued_date'  => 'date',
        'returned_date' => 'date',
        'retained_date' => 'date',
        'transfer_date' => 'date',
    ];

    public function assetInventory(): BelongsTo
    {
        return $this->belongsTo(AssetInventory::class, 'asset_inventory_id');
    }

    public function custodian(): BelongsTo
    {
        return $this->belongsTo(Custodian::class,'custodian_id');    
    }
}
