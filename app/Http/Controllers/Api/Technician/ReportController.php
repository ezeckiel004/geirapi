<?php

namespace App\Http\Controllers\Api\Technician;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Intervention;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * GET /api/tech/reports
     * Rapports soumis par le technicien
     */
    public function index(Request $request)
    {
        $reports = Report::with(['intervention.agency:id,name'])
            ->where('technician_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json($reports);
    }

    /**
     * POST /api/tech/reports
     * Soumettre un nouveau rapport de maintenance
     */
    public function store(Request $request)
{
    $data = $request->validate([
        'intervention_id' => 'required|exists:interventions,id',
        'global_status'   => 'required|in:functional,partial,defective',
        'observations'    => 'required|string|max:2000',
        'actions_done'    => 'required|string|max:2000',
        'recommendations' => 'nullable|string|max:1000',
        'equipment_ids'   => 'nullable|array',
        'equipment_ids.*' => 'exists:equipment,id',
        'equipment_statuses' => 'nullable|array',
    ]);

    $intervention = Intervention::findOrFail($data['intervention_id']);

    // Sécurité : le technicien doit être assigné à cette intervention
    if ($intervention->technician_id !== $request->user()->id) {
        return response()->json(['message' => 'Vous n\'êtes pas assigné à cette intervention.'], 403);
    }

    // Un seul rapport par intervention
    if ($intervention->report()->exists()) {
        return response()->json(['message' => 'Un rapport existe déjà pour cette intervention.'], 422);
    }

    $report = Report::create([
        'intervention_id' => $data['intervention_id'],
        'technician_id'   => $request->user()->id,
        'global_status'   => $data['global_status'],
        'observations'    => $data['observations'],
        'actions_done'    => $data['actions_done'],
        'recommendations' => $data['recommendations'] ?? null,
        'status'          => 'sent_to_client',
        'submitted_at'    => now(),
    ]);

    // Attacher les équipements (correction principale)
    if (!empty($data['equipment_ids'])) {
        $pivotData = [];
        foreach ($data['equipment_ids'] as $eqId) {
            $status = $data['equipment_statuses'][$eqId] ?? 'ok'; // valeur par défaut
            $pivotData[$eqId] = [
                'equipment_status' => $status,
                'note'             => null,
            ];
        }
        $report->equipment()->attach($pivotData);
    }

    // Mise à jour de l'intervention
    $intervention->update([
        'status'         => 'reported',
        'completed_date' => now(),
    ]);

    return response()->json([
        'message' => 'Rapport soumis avec succès. En attente de validation.',
        'report'  => $report->load(['equipment', 'intervention.agency:id,name']),
    ], 201);
}

    /**
     * GET /api/tech/reports/{id}
     */
    public function show(Request $request, Report $report)
    {
        abort_unless($report->technician_id === $request->user()->id, 403);
        return response()->json($report->load(['equipment', 'intervention.agency']));
    }
}
