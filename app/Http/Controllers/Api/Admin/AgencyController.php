<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use Illuminate\Http\Request;

class AgencyController extends Controller
{
    /** GET /api/admin/agencies */
    public function index(Request $request)
    {
        $query = Agency::with('client:id,name,company_name')
            ->withCount('equipment')
            ->withCount('interventions');

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('address', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        return response()->json($query->latest()->paginate(20));
    }

    /** POST /api/admin/agencies */
    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'       => 'nullable|exists:users,id',
            'name'            => 'required|string|max:255',
            'address'         => 'required|string|max:500',
            'phone'           => 'nullable|string|max:20',
            'email'           => 'nullable|email',
            'responsable'     => 'nullable|string|max:255',
            'status'          => 'in:ok,warning,critical',
            'performance'     => 'integer|min:0|max:100',
            'next_maintenance'=> 'nullable|date',
            'image_url'       => 'nullable|url',
        ]);

        $agency = Agency::create($data);
        return response()->json($agency->load('client:id,name,company_name'), 201);
    }

    /** GET /api/admin/agencies/{id} */
    public function show(Agency $agency)
    {
        return response()->json(
            $agency->load(['client:id,name,company_name,email,phone', 'equipment', 'interventions.technician:id,name'])
        );
    }

    /** PUT /api/admin/agencies/{id} */
    public function update(Request $request, Agency $agency)
    {
        $data = $request->validate([
            'client_id'       => 'nullable|exists:users,id',
            'name'            => 'string|max:255',
            'address'         => 'string|max:500',
            'phone'           => 'nullable|string|max:20',
            'email'           => 'nullable|email',
            'responsable'     => 'nullable|string|max:255',
            'status'          => 'in:ok,warning,critical',
            'performance'     => 'integer|min:0|max:100',
            'alertes'         => 'integer|min:0',
            'next_maintenance'=> 'nullable|date',
            'image_url'       => 'nullable|url',
        ]);

        $agency->update($data);
        return response()->json($agency->load('client:id,name,company_name'));
    }

    /** DELETE /api/admin/agencies/{id} */
    public function destroy(Agency $agency)
    {
        $agency->delete();
        return response()->json(['message' => 'Agence supprimée.']);
    }
}
