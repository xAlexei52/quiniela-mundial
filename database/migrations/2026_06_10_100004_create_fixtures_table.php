<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixtures', function (Blueprint $table) {
            $table->id();
            // group, r32, r16, qf, sf, third_place, final
            $table->string('stage', 20)->default('group');
            $table->foreignId('group_id')->nullable()->constrained('groups')->nullOnDelete();
            $table->foreignId('home_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('away_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->unsignedTinyInteger('home_score')->nullable();
            $table->unsignedTinyInteger('away_score')->nullable();
            // para eliminatorias con desempate por penales
            $table->unsignedTinyInteger('home_pens')->nullable();
            $table->unsignedTinyInteger('away_pens')->nullable();
            $table->timestamp('kickoff_at')->nullable();
            $table->string('status', 12)->default('scheduled'); // scheduled, live, finished
            $table->string('external_id')->nullable();
            $table->string('label')->nullable(); // ej. "Octavos 1", "Cuartos B"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixtures');
    }
};
