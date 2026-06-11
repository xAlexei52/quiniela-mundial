<?php

namespace App\Console\Commands;

use App\Services\ResultsSyncService;
use Illuminate\Console\Command;

class SyncResults extends Command
{
    protected $signature = 'quiniela:sync-results';

    protected $description = 'Sincroniza los marcadores desde la API y recalcula la quiniela (pensado para cron)';

    public function handle(ResultsSyncService $sync): int
    {
        if (! $sync->isConfigured()) {
            $this->warn('No hay API de resultados configurada (RESULTS_API_KEY). Nada que sincronizar.');
            return self::SUCCESS;
        }

        try {
            $result = $sync->sync();
            $this->info("Sincronizado: {$result['updated']} partidos actualizados, {$result['skipped']} sin mapear.");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error al sincronizar: '.$e->getMessage());
            return self::FAILURE;
        }
    }
}
