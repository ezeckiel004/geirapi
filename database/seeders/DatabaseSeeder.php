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
            'name'      => 'Jean Martin',
            'email'     => 'admin@geir.fr',
            'password'  => Hash::make('password'),
            'role'      => 'admin',
            'phone'     => '+33 6 10 00 00 01',
        ]);

        $client = User::create([
            'name'         => 'Sophie Dupont',
            'email'        => 'client@acme.fr',
            'password'     => Hash::make('password'),
            'role'         => 'client',
            'company_name' => 'ACME Corp',
            'phone'        => '+33 6 20 00 00 02',
        ]);

        $client2 = User::create([
            'name'         => 'Marc Bernard',
            'email'        => 'client@bnp.fr',
            'password'     => Hash::make('password'),
            'role'         => 'client',
            'company_name' => 'BNP Paribas',
            'phone'        => '+33 6 21 00 00 03',
        ]);

        $tech = User::create([
            'name'      => 'Alexis Moreau',
            'email'     => 'technicien@geir.fr',
            'password'  => Hash::make('password'),
            'role'      => 'technician',
            'phone'     => '+33 6 30 00 00 03',
            'matricule' => 'TECH-001',
        ]);

        $tech2 = User::create([
            'name'      => 'Sarah Petit',
            'email'     => 'sarah@geir.fr',
            'password'  => Hash::make('password'),
            'role'      => 'technician',
            'phone'     => '+33 6 31 00 00 04',
            'matricule' => 'TECH-002',
        ]);

        // ── 2. Agences ────────────────────────────────────────────────────
        $agency1 = Agency::create([
            'client_id'        => $client->id,
            'name'             => 'ACME Corp — Agence Paris',
            'address'          => '12 Rue de la Paix, 75001 Paris',
            'phone'            => '+33 1 40 00 00 10',
            'email'            => 'paris@acme.fr',
            'responsable'      => 'Sophie Dupont',
            'status'           => 'ok',
            'performance'      => 94,
            'alertes'          => 0,
            'next_maintenance' => now()->addMonths(3)->toDateString(),
        ]);

        $agency2 = Agency::create([
            'client_id'        => $client->id,
            'name'             => 'ACME Corp — Agence Lyon',
            'address'          => '8 Place Bellecour, 69002 Lyon',
            'phone'            => '+33 4 72 00 00 20',
            'email'            => 'lyon@acme.fr',
            'responsable'      => 'Paul Renard',
            'status'           => 'warning',
            'performance'      => 78,
            'alertes'          => 2,
            'next_maintenance' => now()->addMonths(1)->toDateString(),
        ]);

        $agency3 = Agency::create([
            'client_id'        => $client2->id,
            'name'             => 'BNP Paribas — Lyon',
            'address'          => '45 Avenue des Frères Lumière, 69008 Lyon',
            'phone'            => '+33 4 78 00 00 30',
            'email'            => 'lyon@bnp.fr',
            'responsable'      => 'Marc Bernard',
            'status'           => 'ok',
            'performance'      => 91,
            'alertes'          => 0,
            'next_maintenance' => now()->addMonths(2)->toDateString(),
        ]);

        // ── 3. Équipements ────────────────────────────────────────────────
        $eq1 = Equipment::create(['agency_id' => $agency1->id, 'name' => 'Sas FB4', 'category' => 'access_control', 'status' => 'functional', 'performance' => 95, 'serial_number' => 'SAS-001']);
        $eq2 = Equipment::create(['agency_id' => $agency1->id, 'name' => 'Portique PDM', 'category' => 'detection', 'status' => 'functional', 'performance' => 92, 'serial_number' => 'PDM-001']);
        $eq3 = Equipment::create(['agency_id' => $agency1->id, 'name' => 'Système CTA', 'category' => 'access_control', 'status' => 'maintenance', 'performance' => 70, 'serial_number' => 'CTA-001']);
        $eq4 = Equipment::create(['agency_id' => $agency2->id, 'name' => 'Caméra Hall A', 'category' => 'video', 'status' => 'functional', 'performance' => 88, 'serial_number' => 'CAM-001']);
        $eq5 = Equipment::create(['agency_id' => $agency2->id, 'name' => 'Portique X2', 'category' => 'detection', 'status' => 'defective', 'performance' => 30, 'serial_number' => 'PX2-001']);
        $eq6 = Equipment::create(['agency_id' => $agency3->id, 'name' => 'Détecteur D8', 'category' => 'detection', 'status' => 'functional', 'performance' => 97, 'serial_number' => 'DET-001']);

        // ── 4. Interventions ──────────────────────────────────────────────
        $int1 = Intervention::create([
            'agency_id'      => $agency1->id,
            'technician_id'  => $tech->id,
            'title'          => 'Maintenance préventive Q1',
            'type'           => 'preventive',
            'priority'       => 'medium',
            'quarter'        => 'Q1',
            'planned_date'   => now()->year . '-03-15',
            'status'         => 'validated',
            'client_validated_at' => now()->subMonths(1),
            'completed_date' => now()->subWeeks(2),
        ]);

        $int2 = Intervention::create([
            'agency_id'    => $agency1->id,
            'technician_id'=> $tech->id,
            'title'        => 'Inspection semestrielle',
            'type'         => 'inspection',
            'priority'     => 'medium',
            'quarter'      => 'Q2',
            'planned_date' => now()->year . '-06-12',
            'status'       => 'accepted',
            'client_validated_at' => now()->subDays(5),
        ]);

        $int3 = Intervention::create([
            'agency_id'    => $agency1->id,
            'title'        => 'Maintenance préventive Q3',
            'type'         => 'preventive',
            'priority'     => 'medium',
            'quarter'      => 'Q3',
            'planned_date' => now()->year . '-09-18',
            'status'       => 'scheduled',
        ]);

        $int4 = Intervention::create([
            'agency_id'    => $agency1->id,
            'title'        => 'Révision annuelle complète',
            'type'         => 'revision',
            'priority'     => 'high',
            'quarter'      => 'Q4',
            'planned_date' => now()->year . '-12-10',
            'status'       => 'scheduled',
        ]);

        $int5 = Intervention::create([
            'agency_id'    => $agency2->id,
            'technician_id'=> $tech2->id,
            'title'        => 'Remplacement portique défectueux',
            'type'         => 'curative',
            'priority'     => 'urgent',
            'quarter'      => 'Q2',
            'planned_date' => now()->addWeek()->toDateString(),
            'status'       => 'accepted',
        ]);

        // ── 5. Rapport technicien ─────────────────────────────────────────
        $report = Report::create([
            'intervention_id' => $int1->id,
            'technician_id'   => $tech->id,
            'global_status'   => 'functional',
            'observations'    => "Vérification complète des équipements du sas FB4. Joints en bon état. Portique PDM calibré. Système CTA nécessite intervention.",
            'actions_done'    => "Nettoyage et vérification sas FB4. Calibration portique PDM. Relevé anomalie CTA.",
            'recommendations' => "Prévoir remplacement module CTA sous 30 jours.",
            'status'          => 'validated',
            'submitted_at'    => now()->subMonths(1)->subDays(3),
            'sent_to_client_at' => now()->subMonths(1)->subDays(2),
            'client_validated_at' => now()->subMonths(1),
        ]);

        $report->equipment()->attach([
            $eq1->id => ['equipment_status' => 'ok', 'note' => null],
            $eq2->id => ['equipment_status' => 'ok', 'note' => null],
            $eq3->id => ['equipment_status' => 'defective', 'note' => 'Module à remplacer'],
        ]);

        $this->command->info('✅ Base de données peuplée avec succès !');
        $this->command->table(
            ['Rôle', 'Email', 'Mot de passe'],
            [
                ['Admin',      'admin@geir.fr',      'password'],
                ['Client 1',   'client@acme.fr',     'password'],
                ['Client 2',   'client@bnp.fr',      'password'],
                ['Technicien', 'technicien@geir.fr',  'password'],
                ['Technicien', 'sarah@geir.fr',       'password'],
            ]
        );
    }
}
