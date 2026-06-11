<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Solo los participantes. Los equipos/grupos/partidos los carga
        // `php artisan quiniela:import-fixture` (desde la API, o con WorldCupSeeder
        // de respaldo si no hay API key). Así se evita duplicar equipos.
        $this->call([
            ParticipantSeeder::class,
        ]);
    }
}
