<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Designation extends Model
{
    //
    public $table = 'designations';
    protected $fillable = ['name','status'];

    // public function custodian(): HasMany
    // {
    //     return $this->hasMany(Custodian::class);
    // }

    public function issueRegister(): HasMany
    {
        return $this->hasMany(IssueRegister::class);
    }
}
