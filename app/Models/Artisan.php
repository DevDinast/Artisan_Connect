<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artisan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'artisans';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'biographie',
        'specialite',
        'region',
        'adresse_atelier',
        'compte_valide',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'compte_valide' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the artisan profile.
     */
    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'User_id');
    }

    /**
     * Get the oeuvres for the artisan.
     */
    public function oeuvres()
    {
        return $this->hasMany(Oeuvre::class, 'artisan_id');
    }

    /**
     * Get the transactions for the artisan.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'artisan_id');
    }

    /**
     * Get the avis for the artisan.
     */
    public function avis()
    {
        return $this->hasMany(Avis::class, 'artisan_id');
    }

    /**
     * Scope to get only validated artisans
     */
    public function scopeValidated($query)
    {
        return $query->where('compte_valide', true);
    }

    /**
     * Scope to get artisans by region
     */
    public function scopeByRegion($query, $region)
    {
        return $query->where('region', $region);
    }

    /**
     * Scope to get artisans by speciality
     */
    public function scopeBySpeciality($query, $specialite)
    {
        return $query->where('specialite', $specialite);
    }
}
