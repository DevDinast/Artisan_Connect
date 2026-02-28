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
        'stock',
        'statut',
    ];

    protected $casts = [
        'prix' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function artisan()
    {
        return $this->belongsTo(Artisan::class, 'artisan_id');
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }

    public function images()
    {
        return $this->hasMany(Image::class, 'oeuvre_id');
    }
}
