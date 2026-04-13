<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Intervention;
use Illuminate\Http\Request;

class InterventionController extends Controller
{
    /**
     * GET /api/client/year
     * Les 4 interventions annuelles de l'agence du client
     */
    public function year(Request $request)
    {
        $year = $request->query('year', now()->year);

        $interventions = Intervention::with(['agency:id,name', 'technician:id,name'])
            ->whereHas('agency', fn($q) => $q->where('client_id', $request->user()->id))
            ->whereYear('planned_date', $year)
            ->orderBy('planned_date')
            ->get();

        return response()->json($interventions);
    }

    /**
     * GET /api/client/interventions
     * Interventions en attente de validation client
     */
    public function index(Request $request)
    {
        $interventions = Intervention::with(['agency:id,name', 'technician:id,name'])
            ->whereHas('agency', fn($q) => $q->where('client_id', $request->user()->id))
            ->orderBy('planned_date')
            ->paginate(20);

        return response()->json($interventions);
    }

    /**
     * PUT /api/client/interventions/{id}/accept
     */
    public function accept(Request $request, Intervention $intervention)
    {
        $this->authorizeClientIntervention($request->user(), $intervention);

        if ($intervention->status !== 'scheduled') {
            return response()->json(['message' => 'Cette intervention ne peut plus être modifiée.'], 422);
        }

        $intervention->update([
            'status'               => 'accepted',
            'client_validated_at'  => now(),
        ]);

        return response()->json(['message' => 'Intervention acceptée.', 'intervention' => $intervention]);
    }

    /**
     * PUT /api/client/interventions/{id}/decline
     */
    public function decline(Request $request, Intervention $intervention)
    {
        $this->authorizeClientIntervention($request->user(), $intervention);

        $request->validate(['comment' => 'required|string|max:500']);

        if ($intervention->status !== 'scheduled') {
            return response()->json(['message' => 'Cette intervention ne peut plus être modifiée.'], 422);
        }

        $intervention->update([
            'status'               => 'declined',
            'client_comment'       => $request->comment,
            'client_validated_at'  => now(),
        ]);

        return response()->json(['message' => 'Intervention refusée.', 'intervention' => $intervention]);
    }

    private function authorizeClientIntervention($user, Intervention $intervention): void
    {
        $belongsToClient = $intervention->agency->client_id === $user->id;
        abort_unless($belongsToClient, 403, 'Accès non autorisé.');
    }
}
