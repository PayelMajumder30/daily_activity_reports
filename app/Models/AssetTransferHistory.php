<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetTransferHistory extends Model
{
    //
    public $table = 'asset_transfer_histories';
    protected $fillable = ['from_asset_issue_register_id', 'to_asset_issue_register_id', 'transfer_date', 'created_by', 'remarks'];

    protected $casts = ['transfer_date' => 'date',];

    public function fromIssue(): BelongsTo
    {
        return $this->belongsTo(AssetIssueRegister::class, 'from_asset_issue_register_id');    
    }

    public function toIssue(): BelongsTo
    {
        return $this->belongsTo(AssetIssueRegister::class, 'to_asset_issue_register_id');    
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
        
    
}
