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
        });

        // 2. On corrige les données invalides (on les passe à 'other')
        DB::statement("
            UPDATE equipment 
            SET category = 'ballistic' 
            WHERE category NOT IN (
                SELECT code FROM equipment_categories
            )
        ");

        // 3. Maintenant on ajoute la contrainte de clé étrangère
        Schema::table('equipment', function (Blueprint $table) {
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
           // On enlève la contrainte
            $table->dropForeign(['category']);
            
            // On remet la taille d'origine
            $table->string('category', 30)->change();
        });
    }
};
