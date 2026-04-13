<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained('agencies')->cascadeOnDelete();
            $table->string('name');
            $table->string('serial_number')->nullable();
            $table->enum('category', [
                'access_control',
                'detection',
                'video',
                'communication',
                'ballistic',
                'other'
            ])->default('other');
            $table->enum('status', ['functional', 'maintenance', 'defective'])->default('functional');
            $table->unsignedTinyInteger('performance')->default(95);
            $table->date('last_maintenance')->nullable();
            $table->date('next_maintenance')->nullable();
            $table->string('image_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
