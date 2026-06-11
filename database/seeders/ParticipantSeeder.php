<?php

namespace Database\Seeders;

use App\Models\Participant;
use Illuminate\Database\Seeder;

class ParticipantSeeder extends Seeder
{
    /**
     * Crea los 12 participantes con nombres de plantilla.
     * Renómbralos desde el panel de admin (o edita este arreglo).
     */
    public function run(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            Participant::firstOrCreate(
                ['slug' => 'participante-'.$i],
                ['name' => 'Participante '.$i, 'position' => $i]
            );
        }
    }
}
