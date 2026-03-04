<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'nom',
        'slug',
        'description',
        'parent_id',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function parent()
    {
        return $this->belongsTo(Categorie::class, 'parent_id');
    }

    public function enfants()
    {
        return $this->hasMany(Categorie::class, 'parent_id');
    }

    public function oeuvres()
    {
        return $this->hasMany(Oeuvre::class, 'categorie_id');
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }
}
