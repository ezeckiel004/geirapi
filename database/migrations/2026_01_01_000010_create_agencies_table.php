<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('address');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('responsable')->nullable();
            $table->enum('status', ['ok', 'warning', 'critical'])->default('ok');
            $table->unsignedTinyInteger('performance')->default(95); // 0-100
            $table->unsignedSmallInteger('alertes')->default(0);
            $table->string('image_url')->nullable();
            $table->date('next_maintenance')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agencies');
    }
};
