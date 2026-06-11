<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 3)->nullable();       // FIFA 3-letter code
            $table->string('flag', 16)->nullable();       // emoji bandera
            $table->foreignId('group_id')->nullable()->constrained('groups')->nullOnDelete();
            $table->string('external_id')->nullable();    // mapeo con API de resultados
            $table->timestamp('eliminated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
