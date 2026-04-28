<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Intervention;
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

    /** GET /api/admin/reports/{id} */
    public function show(Report $report)
    {
        return response()->json(
            $report->load(['technician:id,name,phone', 'intervention.agency', 'equipment'])
        );
    }

    /**
     * POST /api/admin/reports/{id}/send-to-client
     * L'admin envoie le rapport au client pour validation
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

        // Mettre à jour l'intervention en statut "reported"
        $report->intervention()->update(['status' => 'reported']);

        return response()->json(['message' => 'Rapport envoyé au client.', 'report' => $report]);
    }

    /**
 * POST /api/admin/reports/{id}/validate
 * L'admin valide le rapport du technicien
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

    // Mise à jour de l'intervention
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
     * L'admin met à jour les prix des désignations d'un rapport
     */
    public function updateDesignationPrices(Request $request, Report $report)
{
    $data = $request->validate([
        'designations' => 'required|array',
    ]);

    // On récupère les désignations actuelles (on ne les écrase jamais)
    $currentDesignations = $report->designations ?? [];

    foreach ($data['designations'] as $key => $values) {
        // On s'assure que la clé existe déjà
        if (!isset($currentDesignations[$key])) {
            $currentDesignations[$key] = ['status' => null];
        }

        // On met à jour SEULEMENT le prix (le status reste intact)
        if (isset($values['price'])) {
            $currentDesignations[$key]['price'] = $values['price'] !== null 
                ? (float) $values['price'] 
                : null;
        }
    }

    $report->update(['designations' => $currentDesignations]);

    return response()->json([
        'message' => 'Prix des désignations mis à jour avec succès.',
        'report'  => $report->fresh(),
    ]);
}

    /**
     * POST /api/admin/reports/{id}/send-designation-prices
     * Met à jour les prix des désignations et envoie un email au client
     */
    public function sendDesignationPricesToClient(Request $request, Report $report)
    {
        $data = $request->validate([
            'designations' => 'required|array',
        ]);

        // Fusionner avec les statuts existants
        $currentDesignations = $report->designations ?? [];
        $updatedDesignations = [];

        foreach ($data['designations'] as $key => $values) {
            $updatedDesignations[$key] = [
                'status' => $currentDesignations[$key]['status'] ?? null,
                'price'  => isset($values['price']) ? (float) $values['price'] : null,
            ];
        }

        $report->update(['designations' => $updatedDesignations]);

        // Trouver le client
        $intervention = $report->intervention()->with('agency.client')->first();
        $client = $intervention?->agency?->client;

        if (!$client) {
            return response()->json(['message' => 'Client introuvable pour cette intervention.'], 404);
        }

        // Envoyer l'email
        try {
            \Mail::to($client->email)->queue(
                new \App\Mail\DesignationPricesMail($report->fresh(), $intervention)
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Prix enregistrés mais erreur lors de l\'envoi de l\'email: ' . $e->getMessage(),
                'report'  => $report->fresh(),
            ], 200);
        }

        return response()->json([
            'message' => 'Prix enregistrés et devis envoyé au client (' . $client->email . ').',
            'report'  => $report->fresh(),
        ]);
    }
}
