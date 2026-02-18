<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Avis extends Model
{
    use HasFactory;

    protected $table = 'avis';

    protected $fillable = [
        'transaction_id',
        'acheteur_id',
        'oeuvre_id',
        'artisan_id',
        'note',
        'commentaire',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'note' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
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

    public function scopePublished($query)
    {
        return $query->where('statut', 'publie');
    }

    public function scopePending($query)
    {
        return $query->where('statut', 'en_attente');
    }
}
