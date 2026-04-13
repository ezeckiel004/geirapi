<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'company_name',
        'phone',
        'matricule',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ── Helpers rôle ──────────────────────────────────────────────────────
    public function isAdmin(): bool      { return $this->role === 'admin'; }
    public function isClient(): bool     { return $this->role === 'client'; }
    public function isTechnician(): bool { return $this->role === 'technician'; }

    // ── Relations ─────────────────────────────────────────────────────────
    /** Agences associées au client */
    public function agencies()
    {
        return $this->hasMany(Agency::class, 'client_id');
    }

    /** Interventions assignées au technicien */
    public function assignedInterventions()
    {
        return $this->hasMany(Intervention::class, 'technician_id');
    }

    /** Rapports soumis par le technicien */
    public function reports()
    {
        return $this->hasMany(Report::class, 'technician_id');
    }
}
