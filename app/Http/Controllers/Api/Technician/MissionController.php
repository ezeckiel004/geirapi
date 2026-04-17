<?php

namespace App\Http\Controllers\Api\Technician;

use App\Http\Controllers\Controller;
use App\Models\Intervention;
use App\Models\Report;
use Illuminate\Http\Request;

class MissionController extends Controller
{
    /**
     * GET /api/tech/missions
     * Interventions assignées au technicien connecté
     */
    public function index(Request $request)
    {
        $missions = Intervention::with(['agency:id,name,address', 'report:id,intervention_id,status'])
            ->where('technician_id', $request->user()->id)
            // ->whereIn('status', ['accepted', 'in_progress', 'completed', 'reported'])
            ->orderBy('planned_date')
            ->paginate(20);

        return response()->json($missions);
    }

    /**
     * GET /api/tech/missions/{id}
     */
    public function show(Request $request, Intervention $intervention)
    {
        abort_unless($intervention->technician_id === $request->user()->id, 403);

        return response()->json(
            $intervention->load(['agency.equipment', 'report.equipment'])
        );
    }

    /**
     * PUT /api/tech/missions/{id}/start
     * Technicien commence la mission
     */
    public function start(Request $request, Intervention $intervention)
    {
        abort_unless($intervention->technician_id === $request->user()->id, 403);
        abort_unless($intervention->status === 'accepted', 422, 'Mission non accessible.');

        $intervention->update(['status' => 'in_progress']);
        return response()->json(['message' => 'Mission démarrée.', 'intervention' => $intervention]);
    }
}
