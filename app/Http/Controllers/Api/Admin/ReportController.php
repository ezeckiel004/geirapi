<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /** GET /api/admin/reports */
    public function index(Request $request)
    {
        $query = Report::with([
            'technician:id,name',
            'intervention.agency:id,name',
        ]);

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        return response()->json($query->latest()->paginate(20));
    }

    /** POST /api/admin/reports */
    public function store(Request $request)
    {
        $data = $request->validate([
            'intervention_id'   => 'required|exists:interventions,id',
            'technician_id'     => 'required|exists:users,id',
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
            'report_date'       => 'required|date',
        ]);

        $intervention = \App\Models\Intervention::findOrFail($data['intervention_id']);

        if ($intervention->report()->exists()) {
            return response()->json(['message' => 'Un rapport existe déjà pour cette intervention.'], 422);
        }

        $pvPath = $request->file('pv_file')->store('reports/pvs', 'public');

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
            'technician_id'   => $data['technician_id'],
            'global_status'   => $data['global_status'] ?? 'functional',
            'observations'    => $data['observations'] ?? '',
            'actions_done'    => $data['actions_done'] ?? '',
            'recommendations' => $data['recommendations'] ?? null,
            'pv_file'         => $pvPath,
            'pv_type'         => $data['pv_type'],
            'designations'    => $designations,
            'defective_photos' => $defectivePhotosPaths,
            'status'          => 'validated',
            'submitted_at'    => now(),
            'sent_to_client_at' => now(),
            'client_validated_at' => now(),
            'report_date'     => $data['report_date'],
        ]);

        if (!empty($data['equipment_ids'])) {
            $pivotData = [];
            foreach ($data['equipment_ids'] as $eqId) {
                $status = $data['equipment_statuses'][$eqId] ?? 'ok';
                $pivotData[$eqId] = ['equipment_status' => $status, 'note' => null];
            }
            $report->equipment()->attach($pivotData);
        }

        $intervention->update([
            'status'         => 'validated',
            'completed_date' => $data['report_date'],
        ]);

        return response()->json([
            'message' => 'Rapport antérieur enregistré avec succès.',
            'report'  => $report->load(['equipment', 'intervention.agency:id,name'])
                               ->append(['pv_file_url', 'defective_photos_urls']),
        ], 201);
    }

    /** GET /api/admin/reports/{id} */
    public function show(Report $report)
    {
        return response()->json(
            $report->load(['technician:id,name,phone', 'intervention.agency', 'equipment'])
        );
    }

    /**
     * POST /api/admin/reports/{id}/send-to-client
     */
    public function sendToClient(Report $report)
    {
        if ($report->status !== 'submitted') {
            return response()->json(['message' => 'Ce rapport ne peut pas être envoyé.'], 422);
        }

        $report->update([
            'status'            => 'sent_to_client',
            'sent_to_client_at' => now(),
        ]);

        $report->intervention()->update(['status' => 'reported']);

        return response()->json(['message' => 'Rapport envoyé au client.', 'report' => $report]);
    }

    /**
     * POST /api/admin/reports/{id}/validate
     */
    public function validate(Report $report)
    {
        if ($report->status !== 'sent_to_client') {
            return response()->json([
                'message' => 'Ce rapport ne peut pas être validé pour le moment.'
            ], 422);
        }

        $report->update([
            'status'               => 'validated',
            'client_validated_at'  => now(),
        ]);

        $report->intervention()->update([
            'status'               => 'validated',
            'client_validated_at'  => now(),
        ]);

        return response()->json([
            'message' => 'Rapport validé avec succès.',
            'report'  => $report->fresh(['equipment', 'intervention.agency:id,name']),
        ]);
    }

    /**
     * PUT /api/admin/reports/{id}/designation-prices
     * Mise à jour des prix uniquement
     */
    public function updateDesignationPrices(Request $request, Report $report)
    {
        $data = $request->validate([
            'designations' => 'required|array',
        ]);

        $this->mergeDesignationPrices($report, $data['designations']);

        return response()->json([
            'message' => 'Prix des désignations mis à jour.',
            'report'  => $report->fresh(),
        ]);
    }

    /**
 * POST /api/admin/reports/{id}/send-designation-prices
 * Mise à jour des prix + envoi email au client
 */
public function sendDesignationPricesToClient(Request $request, Report $report)
{
    $data = $request->validate([
        'designations' => 'required|array',
    ]);

    // Mise à jour des prix (réutilisation de la méthode privée)
    $this->mergeDesignationPrices($report, $data['designations']);

    // Chargement complet et sécurisé
    $report->loadMissing([
        'intervention.agency.client',     // relation complète
        'intervention.agency',            // fallback
    ]);

    $agency = $report->intervention?->agency;
    $client = $agency?->client;

    // === DEBUG DÉTAILLÉ POUR T’AIDER ===
    if (!$client) {
        return response()->json([
            'message' => 'Client introuvable pour cette intervention.',
            'debug'   => [
                'report_id'        => $report->id,
                'intervention_id'  => $report->intervention_id,
                'agency_id'        => $agency?->id,
                'agency_client_id' => $agency?->client_id,           // ← voilà le coupable
                'client_found'     => $client !== null,
                'agency_exists'    => $agency !== null,
                'intervention_exists' => $report->intervention !== null,
            ]
        ], 404);
    }

    // Envoi de l'email
    try {
        \Mail::to($client->email)->queue(
            new \App\Mail\DesignationPricesMail($report->fresh(), $report->intervention)
        );
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Prix enregistrés, mais erreur lors de l\'envoi de l\'email : ' . $e->getMessage(),
            'report'  => $report->fresh(),
        ], 200);
    }

    return response()->json([
        'message' => 'Prix enregistrés et devis envoyé au client (' . $client->email . ').',
        'report'  => $report->fresh(),
    ]);
}

    /**
     * Méthode privée réutilisable (évite la duplication de code)
     */
    private function mergeDesignationPrices(Report $report, array $newPrices): void
    {
        $current = $report->designations ?? [];

        foreach ($newPrices as $key => $values) {
            if (!isset($current[$key])) {
                $current[$key] = ['status' => null];
            }

            if (isset($values['price'])) {
                $current[$key]['price'] = $values['price'] !== null 
                    ? (float) $values['price'] 
                    : null;
            }
        }

        $report->update(['designations' => $current]);
    }
}