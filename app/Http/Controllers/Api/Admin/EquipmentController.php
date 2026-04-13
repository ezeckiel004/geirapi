<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    /** GET /api/admin/equipment */
    public function index(Request $request)
    {
        $query = Equipment::with('agency:id,name');

        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }
        if ($request->has('agency_id')) {
            $query->where('agency_id', $request->agency_id);
        }

        return response()->json($query->latest()->paginate(20));
    }

    /** POST /api/admin/equipment */
    public function store(Request $request)
    {
        $data = $request->validate([
            'agency_id'       => 'required|exists:agencies,id',
            'name'            => 'required|string|max:255',
            'serial_number'   => 'nullable|string|max:100',
            'category'        => 'required|in:access_control,detection,video,communication,ballistic,other',
            'status'          => 'in:functional,maintenance,defective',
            'performance'     => 'integer|min:0|max:100',
            'last_maintenance'=> 'nullable|date',
            'next_maintenance'=> 'nullable|date',
            'notes'           => 'nullable|string',
            'image_url'       => 'nullable|url',
        ]);

        $equipment = Equipment::create($data);
        return response()->json($equipment->load('agency:id,name'), 201);
    }

    /** GET /api/admin/equipment/{id} */
    public function show(Equipment $equipment)
    {
        return response()->json($equipment->load(['agency:id,name,address', 'reports']));
    }

    /** PUT /api/admin/equipment/{id} */
    public function update(Request $request, Equipment $equipment)
    {
        $data = $request->validate([
            'agency_id'       => 'exists:agencies,id',
            'name'            => 'string|max:255',
            'serial_number'   => 'nullable|string|max:100',
            'category'        => 'in:access_control,detection,video,communication,ballistic,other',
            'status'          => 'in:functional,maintenance,defective',
            'performance'     => 'integer|min:0|max:100',
            'last_maintenance'=> 'nullable|date',
            'next_maintenance'=> 'nullable|date',
            'notes'           => 'nullable|string',
            'image_url'       => 'nullable|url',
        ]);

        $equipment->update($data);
        return response()->json($equipment->load('agency:id,name'));
    }

    /** DELETE /api/admin/equipment/{id} */
    public function destroy(Equipment $equipment)
    {
        $equipment->delete();
        return response()->json(['message' => 'Équipement supprimé.']);
    }
}
