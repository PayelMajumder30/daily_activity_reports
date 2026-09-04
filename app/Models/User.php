<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'role' => 'integer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function uploads(): HasMany
    {
        return $this->hasMany(Upload::class);
    }

    public function eventlogs(): HasMany
    {
        return $this->hasMany(EventLog::class);
    }

    public function assetAssigned(): Hasmany
    {
        return $this->hasMany(AssetAssigned::class, 'created_by');
    }

    public function assetTransfers(): HasMany
    {
        return $this->hasMany(AssetTransfer::class, 'created_by');
    }

    public function retainedAssets(): HasMany
    {
        return $this->hasMany(AssetRetainedAsset::class, 'asset_inventory_id');
    }
}
