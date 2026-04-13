<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Intervention extends Model
{
    use HasFactory;

    protected $fillable = [
        'agency_id',
        'technician_id',
        'title',
        'type',
        'priority',
        'quarter',
        'planned_date',
        'completed_date',
        'description',
        'status',
        'client_comment',
        'client_validated_at',
    ];

    protected $casts = [
        'planned_date'         => 'date',
        'completed_date'       => 'date',
        'client_validated_at'  => 'datetime',
    ];

    // ── Helpers ──────────────────────────────────────────────────────────
    public function isPending(): bool    { return $this->status === 'scheduled'; }
    public function isAccepted(): bool   { return $this->status === 'accepted'; }
    public function isCompleted(): bool  { return in_array($this->status, ['completed', 'reported', 'validated']); }

    public function typeLabel(): string
    {
        return match($this->type) {
            'preventive'  => 'Préventive',
            'curative'    => 'Curative',
            'inspection'  => 'Inspection',
            'revision'    => 'Révision',
            default       => 'Autre',
        };
    }

    // ── Relations ────────────────────────────────────────────────────────
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function report()
    {
        return $this->hasOne(Report::class);
    }
}
