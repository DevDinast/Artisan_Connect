<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommandeItem extends Model
{
    // 📝 Champs remplissables
    protected $fillable = [
        'commande_id',   // La commande à laquelle appartient la ligne
        'oeuvre_id',     // L'oeuvre achetée
        'quantite',      // Quantité achetée
        'prix_unitaire', // Prix de l'œuvre au moment de l'achat
        'sous_total',    // Prix total = quantite * prix_unitaire
    ];

    // 📝 Types
    protected $casts = [
        'quantite' => 'integer',
        'prix_unitaire' => 'float',
        'sous_total' => 'float',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    // Un CommandeItem appartient à une commande
    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    // Un CommandeItem appartient à une œuvre
    public function oeuvre()
    {
        return $this->belongsTo(Oeuvre::class);
    }
}
