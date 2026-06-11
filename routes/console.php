<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sincroniza marcadores cada 3 minutos (para marcadores casi en vivo).
// Requiere un cron en Hostinger que ejecute `php artisan schedule:run` cada minuto,
// o bien un cron directo a `php artisan quiniela:sync-results`.
Schedule::command('quiniela:sync-results')->everyThreeMinutes()->withoutOverlapping();
