<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Discipline extends Model
{
    //
    public $table = 'disciplines';
    protected $fillable = ['name','status'];

    public function deptSection(): HasMany
    {
        return $this->hasMany(DeptSection::class, 'discipline_id');
    }

    public function custodian(): HasMany
    {
        return $this->hasMany(Custodian::class);
    }
}
