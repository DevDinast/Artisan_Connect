<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PanierItem extends Model
{
    // 📝 Les champs qu'on peut remplir via create() ou update()
    protected $fillable = [
        'user_id',   // L'utilisateur qui a ajouté l'oeuvre
        'oeuvre_id', // L'oeuvre ajoutée au panier
        'quantite',  // Quantité choisie
    ];

    // 📝 Définition des types pour éviter les erreurs
    protected $casts = [
        'quantite' => 'integer',  // Toujours un entier
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    // Chaque PanierItem appartient à un utilisateur
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Chaque PanierItem correspond à une œuvre
    public function oeuvre()
    {
        return $this->belongsTo(Oeuvre::class);
    }
}
