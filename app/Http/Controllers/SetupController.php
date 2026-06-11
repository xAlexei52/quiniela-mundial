<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;

class SetupController extends Controller
{
    /**
     * Setup vía web para hosting sin SSH: corre migraciones y seeders una vez.
     * Protegido por APP_SETUP_TOKEN. Deshabilita borrando el token tras instalar.
     */
    public function run(string $token)
    {
        $expected = config('quiniela.setup_token');

        if (empty($expected) || ! hash_equals($expected, $token)) {
            abort(404);
        }

        Artisan::call('migrate', ['--force' => true]);
        $migrate = Artisan::output();

        Artisan::call('db:seed', ['--force' => true]);
        $seed = Artisan::output();

        Artisan::call('quiniela:import-fixture');
        $import = Artisan::output();

        return response(
            "<pre style='font-family:monospace;padding:24px;background:#0b6e3b;color:#fff'>".
            "✅ Setup ejecutado\n\n=== migrate ===\n".e($migrate).
            "\n=== seed ===\n".e($seed).
            "\n=== import-fixture ===\n".e($import).
            "\nListo. Borra APP_SETUP_TOKEN del .env para deshabilitar esta ruta.</pre>"
        );
    }
}
