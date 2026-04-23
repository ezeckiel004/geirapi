<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Agency;
use App\Models\Intervention;
use Illuminate\Support\Facades\DB;

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

    /**
     * PUT /api/auth/profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'phone'        => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
        ]);

        $user->update($data);

        return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'user'    => $this->formatUser($user),
        ]);
    }

    /**
     * PUT /api/auth/password
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Le mot de passe actuel est incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json(['message' => 'Mot de passe modifié avec succès.']);
    }

    private function formatUser(User $user): array
    {
        $stats = [
            'interventions' => 0,
            'sites' => 0,
            'performance' => 0,
        ];

        if ($user->role === 'admin') {
            $stats['interventions'] = \App\Models\Intervention::count();
            $stats['sites'] = \App\Models\Agency::count();
            $avg = \App\Models\Agency::avg('performance');
            $stats['performance'] = $avg ? round($avg) : 100;
        } elseif ($user->role === 'client') {
            $clientAgencies = \App\Models\Agency::where('client_id', $user->id)->pluck('id');
            $stats['interventions'] = \App\Models\Intervention::whereIn('agency_id', $clientAgencies)->count();
            $stats['sites'] = $clientAgencies->count();
            $avg = \App\Models\Agency::where('client_id', $user->id)->avg('performance');
            $stats['performance'] = $avg ? round($avg) : 100;
        } elseif ($user->role === 'technician') {
            $stats['interventions'] = \App\Models\Intervention::where('technician_id', $user->id)->count();
            $stats['sites'] = \App\Models\Intervention::where('technician_id', $user->id)->distinct('agency_id')->count('agency_id');
            // Mock perf for tech for now
            $stats['performance'] = 95;
        }

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
            'stats'        => $stats,
        ];
    }

    /**
 * DELETE /api/auth/delete-account
 * Suppression définitive et complète du compte client
 */
public function deleteAccount(Request $request)
{
    $user = $request->user();

    // Sécurité : seul un client peut supprimer son compte
    if ($user->role !== 'client') {
        return response()->json(['message' => 'Action non autorisée.'], 403);
    }

    DB::transaction(function () use ($user) {
        // 1. Supprimer les agences du client
        Agency::where('client_id', $user->id)->delete();

        // 2. Supprimer toutes les interventions liées à ces agences
        Intervention::whereIn('agency_id', function ($query) use ($user) {
            $query->select('id')->from('agencies')->where('client_id', $user->id);
        })->delete();

        // 3. Supprimer les rapports liés (si tu as une table reports)
        // Report::where('client_id', $user->id)->delete(); // si tu en as une

        // 4. Supprimer le compte utilisateur
        $user->delete();
    });

    // Déconnexion forcée
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'Votre compte a été supprimé définitivement.'
    ]);
}
}
