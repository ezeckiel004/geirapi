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
}
