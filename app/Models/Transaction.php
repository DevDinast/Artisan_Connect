<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'reference',
        'acheteur_id',
        'oeuvre_id',
        'artisan_id',
        'quantite',
        'montant_total',
        'commission',
        'montant_artisan',
        'statut',
        'mode_paiement',
        'adresse_livraison',
    ];

    protected function casts(): array
    {
        return [
            'montant_total' => 'decimal:2',
            'commission' => 'decimal:2',
            'montant_artisan' => 'decimal:2',
            'quantite' => 'integer',
            'adresse_livraison' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function acheteur()
    {
        return $this->belongsTo(Acheteur::class, 'acheteur_id');
    }

    public function oeuvre()
    {
        return $this->belongsTo(Oeuvre::class, 'oeuvre_id');
    }

    public function artisan()
    {
        return $this->belongsTo(Artisan::class, 'artisan_id');
    }

    public function avis()
    {
        return $this->hasOne(Avis::class, 'transaction_id');
    }
}
