<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = ['name', 'description'];

    // ── Accesseur : permet d'utiliser $categorie->nom partout dans le code
    // sans avoir à changer ni la migration ni le contrôleur
    public function getNomAttribute(): string
    {
        return $this->name;
    }

    // ── Relations ─────────────────────────────────────────────────────────────
    public function oeuvres()
    {
        return $this->hasMany(Oeuvre::class, 'categorie_id');
    }
}
