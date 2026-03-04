<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'utilisateur_id',
        'type',
        'titre',
        'message',
        'lue',
    ];

    protected function casts(): array
    {
        return [
            'lue' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('lue', false);
    }

    public function scopeRead($query)
    {
        return $query->where('lue', true);
    }

    public function markAsRead()
    {
        $this->update(['lue' => true]);
    }

    public function markAsUnread()
    {
        $this->update(['lue' => false]);
    }
}
