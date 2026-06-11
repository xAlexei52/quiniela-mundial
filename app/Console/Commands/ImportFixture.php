<?php

namespace App\Console\Commands;

use App\Services\ResultsSyncService;
use Database\Seeders\WorldCupSeeder;
use Illuminate\Console\Command;

class ImportFixture extends Command
{
    protected $signature = 'quiniela:import-fixture';

    protected $description = 'Importa el cuadro del Mundial (equipos, grupos y partidos) desde la API o el seeder de respaldo';

    public function handle(ResultsSyncService $sync): int
    {
        if ($sync->isConfigured()) {
            $this->info('Importando cuadro oficial desde la API...');
            try {
                $result = $sync->importFixture();
                $this->info("Listo: {$result['teams']} equipos nuevos, {$result['fixtures']} partidos.");
                return self::SUCCESS;
            } catch (\Throwable $e) {
                $this->error('Falló la importación desde la API: '.$e->getMessage());
                $this->warn('Usando el seeder de respaldo...');
            }
        } else {
            $this->warn('No hay API configurada (RESULTS_API_KEY). Usando el seeder de respaldo.');
        }

        $this->call('db:seed', ['--class' => WorldCupSeeder::class, '--force' => true]);
        $this->info('Cuadro de respaldo cargado. Ajústalo desde el panel de admin si hace falta.');

        return self::SUCCESS;
    }
}
