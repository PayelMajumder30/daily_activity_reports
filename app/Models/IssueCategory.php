<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssueCategory extends Model
{
    //
    public $table = 'issue_categories';
    protected $fillable = ['title','status'];  

    public function incidentIssues()
    {
        return $this->hasMany(IncidentIssueRegister::class, 'category_id');                       
    }

}
