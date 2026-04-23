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
        Schema::table('equipment', function (Blueprint $table) {
            $table->string('category', 100)->change();

            // Optionnel mais recommandé : on ajoute la contrainte de clé étrangère
            // (pour éviter d'avoir des catégories qui n'existent pas)
            $table->foreign('category')
                  ->references('code')
                  ->on('equipment_categories')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->string('category', 30)->change();
            $table->dropForeign(['category']);
        });
    }
};
