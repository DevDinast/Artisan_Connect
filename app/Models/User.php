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
        'email_verirified_at',
        'actif',
    ];

    /**
     * The attributes that should be hidden for serialization.
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
            'mot_de_passe' => 'hashed',
            'actif' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the artisan profile associated with the user.
     */
    public function artisan()
    {
        return $this->hasOne(Artisan::class, 'utilisateur_id');
    }

    /**
     * Get the acheteur profile associated with the user.
     */
    public function acheteur()
    {
        return $this->hasOne(Acheteur::class, 'utilisateur_id');
    }

    /**
     * Get the administrateur profile associated with the user.
     */
    public function administrateur()
    {
        return $this->hasOne(Administrateur::class, 'utilisateur_id');
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'utilisateur_id');
    }

    /**
     * Check if user is artisan
     */
    public function isArtisan()
    {
        return $this->role === 'artisan';
    }

    /**
     * Check if user is acheteur
     */
    public function isAcheteur()
    {
        return $this->role === 'acheteur';
    }

    /**
     * Check if user is administrateur
     */
    public function isAdministrateur()
    {
        return $this->role === 'administrateur';
    }

    /**
     * Get the user's full name
     */
    public function getFullNameAttribute()
    {
        return "{$this->prenom} {$this->nom}";
    }

    /**
     * Scope to get only active users
     */
    public function scopeActive($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Scope to get users by role
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope to get verified users
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verifie_le');
    }
}
