<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained('agencies')->cascadeOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->enum('type', ['preventive', 'curative', 'inspection', 'revision'])->default('preventive');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('quarter', ['Q1', 'Q2', 'Q3', 'Q4'])->nullable();
            $table->date('planned_date');
            $table->date('completed_date')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', [
                'scheduled',    // Planifiée par admin
                'accepted',     // Acceptée par client
                'declined',     // Refusée par client
                'in_progress',  // En cours (technicien)
                'completed',    // Terminée (technicien)
                'reported',     // Rapport soumis
                'validated'     // Rapport validé par client
            ])->default('scheduled');
            $table->string('client_comment')->nullable(); // Motif refus client
            $table->timestamp('client_validated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};
