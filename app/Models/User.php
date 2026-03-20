<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;




class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'telephone',
        'avatar',
        'email_verified_at',
        'actif',
    ];

    /**
     * The attributes that should be hidden.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'actif' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function artisan()
    {
        return $this->hasOne(Artisan::class, 'user_id');
    }

    public function acheteur()
    {
        return $this->hasOne(Acheteur::class, 'user_id');
    }

    public function administrateur()
    {
        return $this->hasOne(Administrateur::class, 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function isArtisan(): bool
    {
        return $this->role === 'artisan';
    }

    public function isAcheteur(): bool
    {
        return $this->role === 'acheteur';
    }

    public function isAdministrateur(): bool
    {
        return $this->role === 'administrateur';
    }


    public function oeuvres()
{
    return $this->hasManyThrough(Oeuvre::class, Artisan::class, 'user_id', 'artisan_id');
}

public function avisRecus()
{
    return $this->hasManyThrough(Avis::class, Artisan::class, 'user_id', 'artisan_id');
}
    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('actif', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }


}
