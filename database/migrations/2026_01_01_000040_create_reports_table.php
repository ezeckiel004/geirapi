<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intervention_id')->constrained('interventions')->cascadeOnDelete();
            $table->foreignId('technician_id')->constrained('users');
            $table->enum('global_status', ['functional', 'partial', 'defective'])->default('functional');
            $table->text('observations');
            $table->text('actions_done');
            $table->text('recommendations')->nullable();
            $table->enum('status', [
                'draft',           // Brouillon technicien
                'submitted',       // Soumis à l'admin
                'sent_to_client',  // Admin l'a envoyé au client
                'validated',       // Client a validé
                'rejected'         // Client a refusé (demande révision)
            ])->default('submitted');
            $table->string('client_comment')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('sent_to_client_at')->nullable();
            $table->timestamp('client_validated_at')->nullable();
            $table->timestamps();
        });

        // Pivot table report <-> equipment
        Schema::create('report_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('reports')->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->enum('equipment_status', ['ok', 'repaired', 'replaced', 'defective'])->default('ok');
            $table->string('note')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_equipment');
        Schema::dropIfExists('reports');
    }
};
