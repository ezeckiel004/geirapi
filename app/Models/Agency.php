<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'address',
        'phone',
        'email',
        'responsable',
        'status',
        'performance',
        'alertes',
        'image_url',
        'next_maintenance',
    ];

    protected $casts = [
        'performance'      => 'integer',
        'alertes'          => 'integer',
        'next_maintenance' => 'date',
    ];

    // ── Relations ────────────────────────────────────────────────────────
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function equipment()
    {
        return $this->hasMany(Equipment::class);
    }

    public function interventions()
    {
        return $this->hasMany(Intervention::class);
    }
}
