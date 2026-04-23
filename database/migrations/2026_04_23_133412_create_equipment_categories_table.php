<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('equipment_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();           // ex: access_control
            $table->string('name');                     // ex: Contrôle d'accès
            $table->text('description')->nullable();
            $table->timestamps();
        });

        DB::table('equipment_categories')->insert([
            ['code' => 'access_control', 'name' => "Contrôle d'accès",     'created_at' => now(), 'updated_at' => now()],
            ['code' => 'detection',      'name' => 'Détection',            'created_at' => now(), 'updated_at' => now()],
            ['code' => 'video',          'name' => 'Vidéosurveillance',    'created_at' => now(), 'updated_at' => now()],
            ['code' => 'communication',  'name' => 'Communication',        'created_at' => now(), 'updated_at' => now()],
            ['code' => 'ballistic',      'name' => 'Protection balistique','created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_categories');
    }
};
