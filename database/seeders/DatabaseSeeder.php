<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\Equipment;
use App\Models\Intervention;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Utilisateurs ───────────────────────────────────────────────
        $admin = User::create([
            'name'      => 'Jean Martin (Admin Serveur)',
            'email'     => 'geirmaintenance@gmail.com',
            'password'  => Hash::make('password'),
            'role'      => 'admin',
            'phone'     => '+33 6 10 00 00 01',
        ]);

        

        $this->command->info('✅ Base de données peuplée avec succès !');
        $this->command->table(
            ['Rôle', 'Email', 'Mot de passe'],
            [
                ['Admin',      'geirmaintenance@gmail.com',      'password'],
            ]
        );
    }
}
