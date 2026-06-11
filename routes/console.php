<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sincroniza marcadores cada 10 minutos (solo actúa si hay API configurada).
// En Hostinger también puedes correr esto vía cron job directo:
//   php /ruta/al/proyecto/artisan quiniela:sync-results
Schedule::command('quiniela:sync-results')->everyTenMinutes()->withoutOverlapping();
