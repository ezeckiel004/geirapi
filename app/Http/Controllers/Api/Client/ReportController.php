<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * GET /api/client/reports
     * Retourne tous les rapports du client connecté (soumis, envoyés, validés, refusés)
     */
    public function index(Request $request)
    {
        $reports = Report::with([
            'technician:id,name',
            'intervention.agency:id,name',
            'equipment:id,name,category',
        ])
        ->whereHas('intervention.agency', fn($q) => $q->where('client_id', $request->user()->id))
        ->whereIn('status', ['submitted', 'sent_to_client', 'validated', 'rejected'])
        ->latest()
        ->get();

        return response()->json($reports);
    }

    /**
     * GET /api/client/reports/{id}
     */
    public function show(Request $request, Report $report)
    {
        $this->authorizeClientReport($request->user(), $report);
        return response()->json(
            $report->load(['technician:id,name,phone', 'intervention.agency', 'equipment'])
        );
    }

    /**
     * PUT /api/client/reports/{id}/validate
     */
    public function validate(Request $request, Report $report)
    {
        $this->authorizeClientReport($request->user(), $report);

        if ($report->status !== 'sent_to_client' && $report->status !== 'submitted') {
            return response()->json(['message' => 'Ce rapport ne peut plus être validé.'], 422);
        }

        $report->update([
            'status'               => 'validated',
            'client_validated_at'  => now(),
        ]);

        // Marquer l'intervention comme validée
        $report->intervention()->update(['status' => 'validated']);

        return response()->json([
            'message' => 'Rapport validé avec succès.',
            'report'  => $report
        ]);
    }

    /**
     * PUT /api/client/reports/{id}/reject
     */
    public function reject(Request $request, Report $report)
    {
        $this->authorizeClientReport($request->user(), $report);

        $request->validate(['comment' => 'required|string|max:1000']);

        if ($report->status !== 'sent_to_client' && $report->status !== 'submitted') {
            return response()->json(['message' => 'Ce rapport ne peut plus être modifié.'], 422);
        }

        $report->update([
            'status'         => 'rejected',
            'client_comment' => $request->comment,
        ]);

        // Remettre l'intervention en "completed" pour que le technicien puisse refaire
        $report->intervention()->update(['status' => 'completed']);

        return response()->json([
            'message' => 'Révision demandée.',
            'report'  => $report
        ]);
    }

    /**
     * Autorisation centralisée
     */
    private function authorizeClientReport($user, Report $report): void
    {
        // Chargement forcé des relations si elles ne sont pas déjà chargées
        $report->loadMissing(['intervention.agency']);

        $belongsToClient = $report->intervention?->agency?->client_id === $user->id;

        abort_unless($belongsToClient, 403, 'Accès non autorisé.');
    }
}