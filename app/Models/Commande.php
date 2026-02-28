<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{


    protected $fillable = [
        'user_id',
        'statut',
        'montant_total',
        'commission',
    ];

    protected $casts = [
        'montant_total' => 'float',
        'commission' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    // Une commande appartient à un utilisateur (acheteur)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Une commande contient plusieurs lignes
    public function items()
    {
        return $this->hasMany(CommandeItem::class);
    }
}
