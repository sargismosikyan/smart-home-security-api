<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'external_id',
        'name',
        'type',
        'location',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(DeviceActivity::class);
    }

    public function securityAlerts(): HasMany
    {
        return $this->hasMany(SecurityAlert::class);
    }
}
