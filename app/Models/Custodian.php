<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Custodian extends Model
{
    //
    public $table = 'custodians';
    protected $fillable = ['id', 'custodian_name', 'designation_id', 'discipline_id', 'section_id', 'emp_id', 'location_id', 'station_id', 'email', 'phone', 'status'];

    public function designation(): BelongsTo {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function discipline(): BelongsTo {
        return $this->belongsTo(Discipline::class, 'discipline_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(DeptSection::class, 'section_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(AirportStation::class, 'station_id');
    }

    public function issueHistory(): HasMany
    {
        return $this->hasMany(AssetIssueRegister::class, 'custodian_id');
    }

    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(
            AssetTransfer::class,
            'from_custodian_id'
        );
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(
            AssetTransfer::class,
            'to_custodian_id'
        );
    }
}
