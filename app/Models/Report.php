<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;   // ← AJOUT OBLIGATOIRE

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
        'pv_file',
        'pv_type',
        'designations',
        'status',
        'client_comment',
        'submitted_at',
        'sent_to_client_at',
        'client_validated_at',
        'defective_photos',
        'report_date',
    ];

    protected $casts = [
        'submitted_at'        => 'datetime',
        'sent_to_client_at'   => 'datetime',
        'client_validated_at' => 'datetime',
        'report_date'         => 'date',
        'designations'        => 'array',
        'defective_photos'    => 'array',
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

    // Accessor pour l'URL du PV scanné
    public function getPvFileUrlAttribute()
    {
        return $this->pv_file ? Storage::url($this->pv_file) : null;
    }

    public function getDefectivePhotosUrlsAttribute()
    {
        if (empty($this->defective_photos)) {
            return [];
        }
        return array_map(fn($path) => Storage::url($path), $this->defective_photos);
    }
}