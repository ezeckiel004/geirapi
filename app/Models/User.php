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
        'location',
        'parent_id',
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
    public function isClientTech(): bool { return $this->role === 'client_tech'; }

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

    /** Pour un technicien client, récupérer le client parent */
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /** Pour un client, récupérer ses techniciens */
    public function clientTechnicians()
    {
        return $this->hasMany(User::class, 'parent_id')->where('role', 'client_tech');
    }

    public function clientAccessId(): int
    {
        if ($this->isClientTech()) {
            if (empty($this->parent_id)) {
                abort(403, 'Technicien client sans client parent.');
            }
            return $this->parent_id;
        }

        if ($this->isClient()) {
            return $this->id;
        }

        abort(403, 'Accès réservé aux clients et techniciens clients.');
    }
}
