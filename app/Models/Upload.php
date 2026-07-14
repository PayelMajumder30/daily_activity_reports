<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Upload extends Model
{
    //
    public $table = 'uploads';
    protected $fillable = ['report_date', 'file_name'];

    protected $casts = [
        'report_date'   => 'date',
    ];

    public function complaints(){
        return $this->hasMany(Complaint::class);
    }

}
