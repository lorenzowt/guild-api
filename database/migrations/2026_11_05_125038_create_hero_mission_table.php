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
        Schema::create('hero_mission', function (Blueprint $table) {
            $table->foreignId('mission_id')->constrained('missions')->cascadeOnDelete();
            $table->foreignId('hero_id')->constrained('heroes')->restrictOnDelete();
            $table->timestamps();
            $table->primary(['mission_id', 'hero_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hero_mission');
    }
};
