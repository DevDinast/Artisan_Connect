<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Administrateur extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'administrateurs';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'niveau_acces',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the administrateur profile.
     */
    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the validated oeuvres for the administrator.
     */
    public function oeuvresValidees()
    {
        return $this->hasMany(Oeuvre::class, 'validateur_id');
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin()
    {
        return $this->niveau_acces === 'super_admin';
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return in_array($this->niveau_acces, ['super_admin', 'admin']);
    }

    /**
     * Check if user is moderator
     */
    public function isModerator()
    {
        return $this->niveau_acces === 'moderateur';
    }

    /**
     * Scope to get administrators by access level
     */
    public function scopeByAccessLevel($query, $level)
    {
        return $query->where('niveau_acces', $level);
    }
}
