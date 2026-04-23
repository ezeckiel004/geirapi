<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data'     => 'array',
        'read_at'  => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper pour marquer comme lu
    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }
}