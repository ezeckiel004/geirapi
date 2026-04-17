<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TechnicianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // ====================== TECHNICIEN 3 ======================
        User::create([
            'name'       => 'Lucas Bernard',
            'email'      => 'lucas.bernard@geir.fr',
            'password'   => Hash::make('password'),
            'role'       => 'technician',
            'phone'      => '+33 6 98 76 54 32',
            'matricule'  => 'TECH-003',
            'is_active'  => true,
        ]);

        $this->command->info('✅ 3 techniciens ont été créés avec succès !');
    }
}