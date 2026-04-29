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
        'intervention_id'   => 'required|exists:interventions,id',
        'global_status'     => 'nullable|in:functional,partial,defective',
        'observations'      => 'nullable|string|max:2000',
        'actions_done'      => 'nullable|string|max:2000',
        'recommendations'   => 'nullable|string|max:1000',
        'equipment_ids'     => 'nullable|array',
        'equipment_ids.*'   => 'exists:equipment,id',
        'equipment_statuses'=> 'nullable|array',
        'pv_file'           => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        'pv_type'           => 'required|in:pv_visite,pv_constat,pv_intervention',
        'designations'      => 'nullable|string',
        'defective_photos'     => 'nullable|array',
        'defective_photos.*'   => 'file|mimes:jpg,jpeg,png|max:10240',
    ]);

    $intervention = Intervention::findOrFail($data['intervention_id']);

    // Sécurité
    if ($intervention->technician_id !== $request->user()->id) {
        return response()->json(['message' => 'Vous n\'êtes pas assigné à cette intervention.'], 403);
    }

    if ($intervention->report()->exists()) {
        return response()->json(['message' => 'Un rapport existe déjà pour cette intervention.'], 422);
    }

    // Sauvegarde du fichier scanné
    $pvPath = $request->file('pv_file')->store('reports/pvs', 'public');

    // Décoder les désignations (envoyées en JSON string depuis le formulaire multipart)
    $designations = null;
    if (!empty($data['designations'])) {
        $designations = json_decode($data['designations'], true);
    }

    $defectivePhotosPaths = [];
    if ($request->hasFile('defective_photos')) {
        foreach ($request->file('defective_photos') as $photo) {
            $path = $photo->store('reports/defective', 'public');
            $defectivePhotosPaths[] = $path;
        }
    }

    $report = Report::create([
        'intervention_id' => $data['intervention_id'],
        'technician_id'   => $request->user()->id,
        'global_status'   => $data['global_status'] ?? 'functional',
        'observations'    => $data['observations'],
        'actions_done'    => $data['actions_done'],
        'recommendations' => $data['recommendations'] ?? null,
        'pv_file'         => $pvPath,
        'pv_type'         => $data['pv_type'],
        'designations'    => $designations,
        'defective_photos' => $defectivePhotosPaths,
        'status'          => 'sent_to_client',
        'submitted_at'    => now(),
    ]);

    // Équipements (inchangé)
    if (!empty($data['equipment_ids'])) {
        $pivotData = [];
        foreach ($data['equipment_ids'] as $eqId) {
            $status = $data['equipment_statuses'][$eqId] ?? 'ok';
            $pivotData[$eqId] = ['equipment_status' => $status, 'note' => null];
        }
        $report->equipment()->attach($pivotData);
    }

    // Mise à jour intervention
    $intervention->update([
        'status'         => 'reported',
        'completed_date' => now(),
    ]);

    // === NOTIFICATIONS + EMAILS ===
$admin = \App\Models\User::where('role', 'admin')->first();
$client = $intervention->agency->client; // ou $intervention->agency->clientUser si tu as une relation

if ($admin) {
    \App\Models\Notification::create([
        'user_id'   => $admin->id,
        'title'     => 'Rapport soumis',
        'message'   => "Le technicien {$request->user()->name} a soumis un rapport pour l'intervention #{$intervention->id}",
        'type'      => 'report_submitted',
        'data'      => ['report_id' => $report->id, 'intervention_id' => $intervention->id],
    ]);

    // Email Admin
    \Mail::to($admin->email)->queue(new \App\Mail\ReportSubmittedMail($report, $intervention, 'admin'));
}

if ($client) {
    // Email Client
    \Mail::to($client->email)->queue(new \App\Mail\ReportSubmittedMail($report, $intervention, 'client'));
}

    return response()->json([
    'message' => 'Rapport soumis avec succès.',
    'report'  => $report->load(['equipment', 'intervention.agency:id,name'])
                       ->append(['pv_file_url', 'defective_photos_urls']),  // ← modifié
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
