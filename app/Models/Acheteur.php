<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Acheteur extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'acheteurs';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'utilisateur_id',
        'adresse_livraison',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'adresse_livraison' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the acheteur profile.
     */
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }

    /**
     * Get the transactions for the acheteur.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'acheteur_id');
    }

    /**
     * Get the avis for the acheteur.
     */
    public function avis()
    {
        return $this->hasMany(Avis::class, 'acheteur_id');
    }

    /**
     * Get the favoris for the acheteur.
     */
    public function favoris()
    {
        return $this->hasMany(Favori::class, 'acheteur_id');
    }

    /**
     * Get the oeuvres in favorites for the acheteur.
     */
    public function oeuvresFavoris()
    {
        return $this->belongsToMany(Oeuvre::class, 'favoris', 'acheteur_id', 'oeuvre_id');
    }
}
