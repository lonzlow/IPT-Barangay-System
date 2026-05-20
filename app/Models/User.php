<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasUuids, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'official_id',
        'email',
        'password',
        'last_accessed',
        'status',
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
            'last_accessed' => 'datetime',
        ];
    }

    public function official(): BelongsTo
    {
        return $this->belongsTo(Official::class, 'official_id');
    }

    public function blotter_filedBy(): HasMany
    {
        return $this->hasMany(Blotter::class, 'filed_by');
    }


    /* ROLE IS NOW ON OFFICIAL TABLE. KINDLY MOVE THIS
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole(string $roleName):bool
    {
        return $this->role?->role_name === $roleName;
    }

    public function hasAnyRole(array $roleNames):bool
    {
        return in_array($this->role?->role_name, $roleNames, true);
    }
        */

    public function activity_logs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}
