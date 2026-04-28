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

        // Mise à jour des prix (sans écraser les statuts existants)
        $this->mergeDesignationPrices($report, $data['designations']);

        // === CHARGEMENT CORRECT DU CLIENT ===
        $report->loadMissing([
            'intervention.agency.client'   // ← C'est la correction principale
        ]);

        $client = $report->intervention?->agency?->client;

        if (!$client) {
            return response()->json([
                'message' => 'Client introuvable pour cette intervention.',
                'debug'   => [
                    'has_intervention' => $report->relationLoaded('intervention'),
                    'has_agency'       => $report->intervention?->relationLoaded('agency'),
                    'client_id_present'=> $report->intervention?->agency?->client_id ?? null,
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