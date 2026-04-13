<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'intervention_id',
        'technician_id',
        'global_status',
        'observations',
        'actions_done',
        'recommendations',
        'status',
        'client_comment',
        'submitted_at',
        'sent_to_client_at',
        'client_validated_at',
    ];

    protected $casts = [
        'submitted_at'        => 'datetime',
        'sent_to_client_at'   => 'datetime',
        'client_validated_at' => 'datetime',
    ];

    // ── Relations ────────────────────────────────────────────────────────
    public function intervention()
    {
        return $this->belongsTo(Intervention::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function equipment()
    {
        return $this->belongsToMany(Equipment::class, 'report_equipment')
                    ->withPivot('equipment_status', 'note');
    }
}
