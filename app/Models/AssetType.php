<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetType extends Model
{
    //
    public $table = 'asset_types';
    protected $fillable = ['name','status'];

    public function assetModel(): HasMany
    {
        return $this->hasMany(AssetModel::class);
    }
}
