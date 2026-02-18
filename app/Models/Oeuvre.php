<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oeuvre extends Model
{
    use HasFactory;

    protected $table = 'oeuvres';

    protected $fillable = [
        'artisan_id',
        'categorie_id',
        'titre',
        'description',
        'prix',
        'quantite_disponible',
        'dimensions',
        'materiaux',
        'statut',
        'motif_refus',
        'date_validation',
        'validateur_id',
    ];

    protected function casts(): array
    {
        return [
            'prix' => 'decimal:2',
            'quantite_disponible' => 'integer',
            'dimensions' => 'array',
            'materiaux' => 'array',
            'date_validation' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function artisan()
    {
        return $this->belongsTo(Artisan::class, 'artisan_id');
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }

    public function validateur()
    {
        return $this->belongsTo(Administrateur::class, 'validateur_id');
    }

    public function images()
    {
        return $this->hasMany(Image::class, 'oeuvre_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'oeuvre_id');
    }

    public function avis()
    {
        return $this->hasMany(Avis::class, 'oeuvre_id');
    }

    public function favoris()
    {
        return $this->belongsToMany(Acheteur::class, 'favoris', 'oeuvre_id', 'acheteur_id');
    }
}
