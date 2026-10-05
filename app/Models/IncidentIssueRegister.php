<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentIssueRegister extends Model
{
    //

    public $table = 'incident_issue_registers';
    protected $fillable = ['call_id','asset_issue_register_id', 'support_user_id', 'category_id', 'remarks', 'status', 'call_generated_at', 'call_attended_at', 'call_closed_at'];
    
    protected $casts = [
        'call_generated_at' => 'datetime',
        'call_attended_at' => 'datetime',
        'call_closed_at' => 'datetime',
    ];

    public function assetIssueRegister(): BelongsTo
    {
        return $this->belongsTo(AssetIssueRegister::class, 'asset_issue_register_id');                           
    }

    public function supportUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'support_user_id');                      
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(IssueCategory::class, 'category_id');                          
    }
}
