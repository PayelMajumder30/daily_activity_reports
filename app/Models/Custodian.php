<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Custodian extends Model
{
    //
    public $table = 'custodians';
    protected $fillable = ['id', 'custodian_name', 'designation_id', 'discipline_id', 'email', 'phone', 'status'];

    public function designation(): BelongsTo {
        return $this->belongsTo(Designation::class);
    }

    public function discipline(): BelongsTo {
        return $this->belongsTo(Discipline::class);
    }
}
