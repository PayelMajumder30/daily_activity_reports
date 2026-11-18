<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetIssueRegister extends Model
{
    //
    public $table = 'asset_issue_registers';
    protected $fillable = ['asset_inventory_id', 'custodian_id', 'discipline_id', 'dept_section_id', 'user_type', 'operator_name', 'issued_date', 'retained_date', 'transfer_date', 'returned_date', 'issue_status', 'remarks'];

    protected $casts = [
        'issued_date'   => 'date',
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

    public function incidentIssues(): HasMany
    {
        return $this->hasMany(IncidentIssueRegister::class, 'asset_issue_register_id');                       
    }

    public function deptSection(): BelongsTo
    {
        return $this->belongsTo(DeptSection::class, 'dept_section_id');
    }

    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class, 'discipline_id');
    }

    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(AssetTransfer::class, 'from_asset_issue_register_id');    
        
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(AssetTransfer::class, 'to_asset_issue_register_id');  
    }
}
