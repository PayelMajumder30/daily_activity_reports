<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssueRegister extends Model
{
    //
    public $table = 'issue_registers';
    protected $fillable = ['custodian_name', 'designation_id', 'discipline_id', 'section_id', 'user_type', 'operator_name', 'emp_id', 'asset_inventory_id', 'status'];

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    public function deptSection(): BelongsTo
    {
        return $this->belongsTo(DeptSection::class, 'section_id');
    }

    public function assetInventory(): BelongsTo
    {
        return $this->belongsTo(AssetInventory::class);
    }
}
