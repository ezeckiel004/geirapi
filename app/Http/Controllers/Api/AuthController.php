<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Agency;
use App\Models\Intervention;

class AuthController extends Controller
{

    public function register(Request $request)
{
    $data = $request->validate([
        'name'          => 'required|string|max:255',
        'company_name'  => 'required|string|max:255',
        'email'         => 'required|email|unique:users,email',
        'password'      => 'required|string|min:6|confirmed',
        'phone'         => 'nullable|string',
    ]);

    $user = User::create([
        'name'         => $data['name'],
        'email'        => $data['email'],
        'password'     => Hash::make($data['password']),
        'role'         => 'client',
        'company_name' => $data['company_name'],
        'phone'        => $data['phone'] ?? null,
        'is_active'    => true,
    ]);

    // Création automatique d'une agence par défaut pour le client
    $agency = Agency::create([
        'client_id' => $user->id,
        'name'      => $data['company_name'],
        'address'   => 'À compléter par le client',
    ]);

    // Récupération des techniciens actifs
    $technicians = User::where('role', 'technician')
        ->where('is_active', true)
        ->get();

    if ($technicians->isEmpty()) {
        $technicians = User::where('role', 'technician')->get();
    }

    // 4 interventions préventives tous les 3 mois (première dans 3 mois)
    $now = now();
    for ($i = 0; $i < 4; $i++) {
        $plannedDate = $now->copy()->addMonths(3 * ($i + 1));

        $tech = $technicians->random();

        Intervention::create([
            'agency_id'     => $agency->id,
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
        'message' => 'Compte client créé avec succès. 4 interventions préventives ont été programmées.',
        'user'    => $this->formatUser($user),
    ], 201);
}
    /**
     * POST /api/auth/login
     * Retourne le token Sanctum + infos utilisateur
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Identifiants incorrects.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Compte désactivé.'], 403);
        }

        // Révoquer les anciens tokens (session unique)
        $user->tokens()->delete();

        $token = $user->createToken('geer-api-token', ['*'])->plainTextToken;

        
        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ]);
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté avec succès.']);
    }

    /**
     * GET /api/auth/me
     */
    public function me(Request $request)
    {
        return response()->json($this->formatUser($request->user()));
    }

    private function formatUser(User $user): array
    {
        return [
            'id'           => $user->id,
            'name'         => $user->name,
            'email'        => $user->email,
            'role'         => $user->role,
            'company_name' => $user->company_name,
            'phone'        => $user->phone,
            'matricule'    => $user->matricule,
            'is_active'    => $user->is_active,
            'created_at'   => $user->created_at,
        ];
    }
}
