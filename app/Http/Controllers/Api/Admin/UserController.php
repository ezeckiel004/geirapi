<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * GET /api/admin/technicians
     * Liste des techniciens (pour assignation)
     */
    public function technicians()
    {
        $technicians = User::where('role', 'technician')
            ->where('is_active', true)
            ->get(['id', 'name', 'email', 'phone', 'matricule']);

        return response()->json($technicians);
    }

    /**
     * GET /api/admin/clients
     * Liste des clients
     */
    public function clients()
    {
        $clients = User::where('role', 'client')
            ->where('is_active', true)
            ->with('agencies:id,name,client_id')
            ->get(['id', 'name', 'email', 'company_name', 'phone']);

        return response()->json($clients);
    }

    /**
     * POST /api/admin/technicians
     * Créer un compte technicien
     */
    public function createTechnician(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8',
            'phone'     => 'nullable|string|max:20',
            'matricule' => 'nullable|string|max:50',
        ]);

        $technician = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => 'technician',
            'phone'     => $data['phone'] ?? null,
            'matricule' => $data['matricule'] ?? null,
        ]);

        return response()->json([
            'message'    => 'Compte technicien créé avec succès.',
            'technician' => $technician->only(['id', 'name', 'email', 'phone', 'matricule', 'role']),
        ], 201);
    }

    /**
     * POST /api/admin/clients
     * Créer un compte client
     */
    public function createClient(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|string|min:8',
            'company_name' => 'required|string|max:255',
            'phone'        => 'nullable|string|max:20',
        ]);

        $client = User::create([
            'name'         => $data['name'],
            'email'        => $data['email'],
            'password'     => Hash::make($data['password']),
            'role'         => 'client',
            'company_name' => $data['company_name'],
            'phone'        => $data['phone'] ?? null,
        ]);

        return response()->json([
            'message' => 'Compte client créé avec succès.',
            'client'  => $client->only(['id', 'name', 'email', 'company_name', 'phone', 'role']),
        ], 201);
    }

    /**
     * PUT /api/admin/users/{id}/toggle-active
     * Activer/désactiver un compte
     */
    public function toggleActive(User $user)
    {
        $user->update(['is_active' => ! $user->is_active]);
        return response()->json([
            'message'   => $user->is_active ? 'Compte activé.' : 'Compte désactivé.',
            'is_active' => $user->is_active,
        ]);
    }
}
