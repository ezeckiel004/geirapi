<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Intervention;
use Illuminate\Support\Str;
use App\Mail\ClientWelcomeMail;
use App\Models\Agency;
use App\Mail\TechnicianWelcomeMail;

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

    // Envoi de l'email de bienvenue
    \Mail::to($technician->email)->send(new \App\Mail\TechnicianWelcomeMail($technician, $data['password']));

    return response()->json([
        'message'    => 'Compte technicien créé avec succès. Un email avec les identifiants a été envoyé.',
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
        'company_name' => 'required|string|max:255',
        'phone'        => 'nullable|string|max:20',
    ]);

    // Générer un mot de passe aléatoire sécurisé
    $password = Str::password(12);   // 12 caractères aléatoires

    $client = User::create([
        'name'         => $data['name'],
        'email'        => $data['email'],
        'password'     => Hash::make($password),
        'role'         => 'client',
        'company_name' => $data['company_name'],
        'phone'        => $data['phone'] ?? null,
    ]);

    // Envoi de l'email de bienvenue avec le mot de passe
    \Mail::to($client->email)->send(new \App\Mail\ClientWelcomeMail($client, $password));

    $agency = Agency::create([
        'client_id' => $client->id,
        'name'      => $data['company_name'],
        'address'   => 'À compléter par le client',
    ]);

    // Création des interventions préventives automatiques (ton code existant)
    $technicians = User::where('role', 'technician')
        ->where('is_active', true)
        ->get();

    if ($technicians->isEmpty()) {
        $technicians = User::where('role', 'technician')->get();
    }

    $now = now();
    for ($i = 0; $i < 4; $i++) {
        $plannedDate = $now->copy()->addMonths(3 * ($i + 1));
        $tech = $technicians->random();

        Intervention::create([
            'agency_id'     => $agency->id,   // ou l'agence liée si tu en as une
            'technician_id' => $tech->id,
            'title'         => "Préventive - " . $plannedDate->format('F Y'),
            'type'          => 'preventive',
            'priority'      => 'medium',
            'planned_date'  => $plannedDate->format('Y-m-d'),
            'description'   => 'Intervention préventive automatique après inscription client',
            'status'        => 'scheduled',
        ]);
    }

    return response()->json([
        'message' => 'Compte client créé avec succès. Un email avec les identifiants a été envoyé.',
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
