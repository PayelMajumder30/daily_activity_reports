<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeptSection extends Model
{
    //
    public $table = 'dept_sections';
    protected $fillable = ['discipline_id','section_name', 'status'];

    public function discipline(): BelongsTo {
        return $this->belongsTo(Discipline::class, 'discipline_id');
    }
}
