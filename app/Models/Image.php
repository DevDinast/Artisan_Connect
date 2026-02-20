<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $table = 'images';

    protected $fillable = [
        'oeuvre_id',
        'chemin',
        'type',
        'ordre',
    ];

    protected function casts(): array
    {
        return [
            'ordre' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function oeuvre()
    {
        return $this->belongsTo(Oeuvre::class, 'oeuvre_id');
    }

    public function scopePrincipale($query)
    {
        return $query->where('type', 'principale');
    }

    public function scopeSecondaire($query)
    {
        return $query->where('type', 'secondaire');
    }

    public function scopeByOrder($query)
    {
        return $query->orderBy('ordre', 'asc');
    }
}
