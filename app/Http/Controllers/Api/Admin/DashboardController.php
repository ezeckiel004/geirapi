<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Intervention;
use App\Models\Report;
use App\Models\Equipment;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * GET /api/admin/dashboard
     */
    public function index()
    {
        $totalAgencies     = Agency::count();
        $totalEquipment    = Equipment::count();
        $okEquipment       = Equipment::where('status', 'functional')->count();
        $defectiveEquipment = Equipment::where('status', 'defective')->count();

        $interventions = Intervention::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $pendingInterventions    = $interventions->get('scheduled', 0);
        $acceptedInterventions   = $interventions->get('accepted', 0);
        $completedInterventions  = $interventions->get('completed', 0) +
                                   $interventions->get('reported', 0) +
                                   $interventions->get('validated', 0);

        $pendingReports  = Report::where('status', 'submitted')->count();
        $validatedReports = Report::where('status', 'validated')->count();

        $performanceAvg = Agency::avg('performance') ?? 0;

        // Top 5 agences par performance
        $topSites = Agency::with('client:id,name,company_name')
            ->orderByDesc('performance')
            ->limit(5)
            ->get(['id', 'name', 'performance', 'status', 'client_id']);

        // Interventions récentes
        $recentInterventions = Intervention::with(['agency:id,name', 'technician:id,name'])
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'kpis' => [
                'total_agencies'          => $totalAgencies,
                'total_equipment'         => $totalEquipment,
                'ok_equipment'            => $okEquipment,
                'defective_equipment'     => $defectiveEquipment,
                'pending_interventions'   => $pendingInterventions,
                'accepted_interventions'  => $acceptedInterventions,
                'completed_interventions' => $completedInterventions,
                'pending_reports'         => $pendingReports,
                'validated_reports'       => $validatedReports,
                'performance_avg'         => round($performanceAvg, 1),
            ],
            'top_sites'             => $topSites,
            'recent_interventions'  => $recentInterventions,
        ]);
    }
}
