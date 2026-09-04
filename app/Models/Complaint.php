<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    //
    public $table = 'complaints';
    protected $fillable = ['upload_id', 'complaint_title', 'engineer_name', 'emp_code', 'status', 'resolution_time', 'type_of_activity', 'asset_tag_no'];

    public function upload(): BelongsTo {
        return $this->belongsTo(Upload::class);
    }
}
