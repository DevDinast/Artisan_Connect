<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Panier extends Model
{
    protected $table = 'panier_items';

    protected $fillable = [
        'acheteur_id',
        'oeuvre_id',
        'quantite',
    ];

    public function oeuvre()
    {
        return $this->belongsTo(Oeuvre::class, 'oeuvre_id');
    }

    public function acheteur()
    {
        return $this->belongsTo(Acheteur::class, 'acheteur_id');
    }
}
