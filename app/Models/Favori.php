<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favori extends Model
{
    use HasFactory;

    protected $table = 'favoris';

    protected $fillable = [
        'acheteur_id',
        'oeuvre_id',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
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
}
