<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Integración con API de resultados (opcional)
    |--------------------------------------------------------------------------
    |
    | Si configuras una API key, el comando `quiniela:sync-results` puede traer
    | los marcadores automáticamente. Sin key, todo funciona con carga manual
    | desde el panel de admin.
    |
    | Driver soportado por defecto: "football-data" (https://www.football-data.org).
    | Crea una cuenta gratuita, copia tu token y ponlo en RESULTS_API_KEY.
    | El id de competición del Mundial en football-data.org es 2000 (WC).
    |
    */

    'results' => [
        'driver'         => env('RESULTS_DRIVER', 'football-data'),
        'api_key'        => env('RESULTS_API_KEY'),
        'competition_id' => env('RESULTS_COMPETITION_ID', '2000'),
        'season'         => env('RESULTS_SEASON', '2026'),
        'base_url'       => env('RESULTS_BASE_URL', 'https://api.football-data.org/v4'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Token de setup vía web (para hosting sin SSH)
    |--------------------------------------------------------------------------
    |
    | Si defines APP_SETUP_TOKEN, se habilita la ruta /setup/{token} que corre
    | migraciones + seeders una sola vez desde el navegador. Déjalo vacío en
    | producción una vez instalado para deshabilitarla.
    |
    */

    'setup_token' => env('APP_SETUP_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Acceso al panel de administración (sin login)
    |--------------------------------------------------------------------------
    |
    | La app es pública (solo lectura). El panel /admin para registrar
    | participantes y asignar equipos se protege con un PIN simple definido en
    | APP_ADMIN_PIN. Cámbialo en el .env del servidor.
    |
    */

    'admin_pin' => env('APP_ADMIN_PIN', '2026'),

    /*
    |--------------------------------------------------------------------------
    | Zona horaria para mostrar los horarios de los partidos
    |--------------------------------------------------------------------------
    |
    | La API entrega las fechas en UTC; se guardan en UTC y se convierten a esta
    | zona solo al mostrarlas. México centro = America/Mexico_City (UTC-6).
    |
    */

    'timezone' => env('QUINIELA_TZ', 'America/Mexico_City'),

    /*
    |--------------------------------------------------------------------------
    | Premios (bote)
    |--------------------------------------------------------------------------
    |
    | Bote total y reparto entre el top 3 (deben sumar 1.0). Por defecto
    | 50% / 30% / 20%. Ajusta a tu gusto.
    |
    */

    'prize' => [
        'pool'     => (int) env('QUINIELA_PRIZE_POOL', 6000),
        'currency' => env('QUINIELA_CURRENCY', 'MXN'),
        // Monto fijo a repartir por puesto (1º, 2º, 3º).
        'amounts'  => [4000, 1500, 500],
    ],

];
