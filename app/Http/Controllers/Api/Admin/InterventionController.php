<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Intervention;
use App\Models\User;
use Illuminate\Http\Request;

class InterventionController extends Controller
{
    /** GET /api/admin/interventions */
   /** GET /api/admin/interventions */
public function index(Request $request)
    {
        $query = Intervention::with([
            'agency:id,name,address',
            'technician:id,name,phone',
            'report',
            'report.equipment',   // belongsToMany — pas de sélection de colonnes via ':'
        ]);

        // Filtres (optionnels)
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->has('agency_id')) {
            $query->where('agency_id', $request->agency_id);
        }

        $interventions = $query->orderBy('planned_date')->get();

        // Force l'ajout de l'URL du PV pour chaque rapport qui existe
        foreach ($interventions as $intervention) {
            if ($intervention->report) {
                $intervention->report->append('pv_file_url');
            }
        }

        return response()->json($interventions);
    }
    /** POST /api/admin/interventions */
    public function store(Request $request)
    {
        $data = $request->validate([
            'agency_id'    => 'required|exists:agencies,id',
            'technician_id'=> 'nullable|exists:users,id',
            'title'        => 'required|string|max:255',
            'type'         => 'required|in:preventive,curative,inspection,revision',
            'priority'     => 'in:low,medium,high,urgent',
            'quarter'      => 'nullable|in:Q1,Q2,Q3,Q4',
            'planned_date' => 'required|date',
            'description'  => 'nullable|string',
        ]);

        $intervention = Intervention::create($data);

        return response()->json(
            $intervention->load(['agency:id,name', 'technician:id,name']),
            201
        );
    }

    /** GET /api/admin/interventions/{id} */
    public function show(Intervention $intervention)
    {
        return response()->json(
            $intervention->load(['agency', 'technician:id,name,phone,email', 'report.equipment'])
        );
    }

    /** PUT /api/admin/interventions/{id} */
    public function update(Request $request, Intervention $intervention)
    {
        $data = $request->validate([
            'agency_id'    => 'exists:agencies,id',
            'technician_id'=> 'nullable|exists:users,id',
            'title'        => 'string|max:255',
            'type'         => 'in:preventive,curative,inspection,revision',
            'priority'     => 'in:low,medium,high,urgent',
            'quarter'      => 'nullable|in:Q1,Q2,Q3,Q4',
            'planned_date' => 'date',
            'description'  => 'nullable|string',
            'status'       => 'in:scheduled,accepted,declined,in_progress,completed,reported,validated',
        ]);

        $intervention->update($data);
        return response()->json($intervention->load(['agency:id,name', 'technician:id,name']));
    }

    /** POST /api/admin/interventions/{id}/assign */
    public function assign(Request $request, Intervention $intervention)
    {
        $request->validate([
            'technician_id' => 'required|exists:users,id',
        ]);

        $technician = User::findOrFail($request->technician_id);
        if ($technician->role !== 'technician') {
            return response()->json(['message' => "Cet utilisateur n'est pas un technicien."], 422);
        }

        $intervention->update(['technician_id' => $request->technician_id]);
        return response()->json($intervention->load(['agency:id,name', 'technician:id,name,phone']));
    }

    /** DELETE /api/admin/interventions/{id} */
    public function destroy(Intervention $intervention)
    {
        $intervention->delete();
        return response()->json(['message' => 'Intervention supprimée.']);
    }
}
