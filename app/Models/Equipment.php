<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'agency_id',
        'name',
        'serial_number',
        'category',
        'status',
        'performance',
        'last_maintenance',
        'next_maintenance',
        'image_url',
        'notes',
    ];

    protected $casts = [
        'performance'      => 'integer',
        'last_maintenance' => 'date',
        'next_maintenance' => 'date',
    ];

    // ── Helpers ──────────────────────────────────────────────────────────
    public function categoryLabel(): string
    {
        return match($this->category) {
            'access_control' => "Contrôle d'accès",
            'detection'      => 'Détection',
            'video'          => 'Vidéosurveillance',
            'communication'  => 'Communication',
            'ballistic'      => 'Protection balistique',
            default          => 'Autre',
        };
    }

    // ── Relations ────────────────────────────────────────────────────────
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function reports()
    {
        return $this->belongsToMany(Report::class, 'report_equipment')
                    ->withPivot('equipment_status', 'note');
    }
}
