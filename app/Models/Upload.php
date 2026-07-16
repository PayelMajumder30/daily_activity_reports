<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Upload extends Model
{
    //
    public $table = 'uploads';
    protected $fillable = ['user_id', 'report_date', 'file_name'];

    protected $casts = [
        'report_date'   => 'date',
    ];

    public function complaints(){
        return $this->hasMany(Complaint::class);
    }

    public function complaint_temps(){
        return $this->hasMany(ComplaintTemp::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

}
