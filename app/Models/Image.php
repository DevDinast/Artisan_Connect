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
        'is_principale',
    ];

    protected $casts = [
        'is_principale' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function oeuvre()
    {
        return $this->belongsTo(Oeuvre::class, 'oeuvre_id');
    }
}
