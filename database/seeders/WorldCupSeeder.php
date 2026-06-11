<?php

namespace Database\Seeders;

use App\Models\Fixture;
use App\Models\Group;
use App\Models\Team;
use Illuminate\Database\Seeder;

class WorldCupSeeder extends Seeder
{
    /**
     * Plantilla inicial del Mundial 2026 (48 equipos, 12 grupos de 4).
     *
     * IMPORTANTE: la composición de grupos es una PLANTILLA editable. La fuente
     * de verdad del cuadro oficial es el comando `php artisan quiniela:import-fixture`
     * (que la trae desde la API si hay key configurada) o el panel de admin.
     * El sorteo de la quiniela entre los 6 participantes NO depende de los grupos.
     *
     * Formato: [grupo => [[nombre, código FIFA, bandera emoji], ...]]
     */
    private const GROUPS = [
        'A' => [['México', 'MEX', '🇲🇽'], ['Países Bajos', 'NED', '🇳🇱'], ['Egipto', 'EGY', '🇪🇬'], ['Nueva Zelanda', 'NZL', '🇳🇿']],
        'B' => [['Canadá', 'CAN', '🇨🇦'], ['Croacia', 'CRO', '🇭🇷'], ['Nigeria', 'NGA', '🇳🇬'], ['Catar', 'QAT', '🇶🇦']],
        'C' => [['Estados Unidos', 'USA', '🇺🇸'], ['Bélgica', 'BEL', '🇧🇪'], ['Senegal', 'SEN', '🇸🇳'], ['Panamá', 'PAN', '🇵🇦']],
        'D' => [['Argentina', 'ARG', '🇦🇷'], ['Suiza', 'SUI', '🇨🇭'], ['Costa de Marfil', 'CIV', '🇨🇮'], ['Jordania', 'JOR', '🇯🇴']],
        'E' => [['Francia', 'FRA', '🇫🇷'], ['Uruguay', 'URU', '🇺🇾'], ['Argelia', 'ALG', '🇩🇿'], ['Uzbekistán', 'UZB', '🇺🇿']],
        'F' => [['Brasil', 'BRA', '🇧🇷'], ['Dinamarca', 'DEN', '🇩🇰'], ['Túnez', 'TUN', '🇹🇳'], ['Jamaica', 'JAM', '🇯🇲']],
        'G' => [['Inglaterra', 'ENG', '🏴󠁧󠁢󠁥󠁮󠁧󠁿'], ['Austria', 'AUT', '🇦🇹'], ['Sudáfrica', 'RSA', '🇿🇦'], ['Corea del Sur', 'KOR', '🇰🇷']],
        'H' => [['España', 'ESP', '🇪🇸'], ['Ecuador', 'ECU', '🇪🇨'], ['Ghana', 'GHA', '🇬🇭'], ['Arabia Saudita', 'KSA', '🇸🇦']],
        'I' => [['Portugal', 'POR', '🇵🇹'], ['Colombia', 'COL', '🇨🇴'], ['Camerún', 'CMR', '🇨🇲'], ['Irán', 'IRN', '🇮🇷']],
        'J' => [['Alemania', 'GER', '🇩🇪'], ['Paraguay', 'PAR', '🇵🇾'], ['Cabo Verde', 'CPV', '🇨🇻'], ['Australia', 'AUS', '🇦🇺']],
        'K' => [['Noruega', 'NOR', '🇳🇴'], ['Marruecos', 'MAR', '🇲🇦'], ['Honduras', 'HON', '🇭🇳'], ['Japón', 'JPN', '🇯🇵']],
        'L' => [['Italia', 'ITA', '🇮🇹'], ['Escocia', 'SCO', '🏴󠁧󠁢󠁳󠁣󠁴󠁿'], ['Curazao', 'CUW', '🇨🇼'], ['Irak', 'IRQ', '🇮🇶']],
    ];

    public function run(): void
    {
        foreach (self::GROUPS as $letter => $teams) {
            $group = Group::firstOrCreate(['name' => $letter]);

            $teamModels = [];
            foreach ($teams as [$name, $code, $flag]) {
                $teamModels[] = Team::firstOrCreate(
                    ['code' => $code],
                    ['name' => $name, 'flag' => $flag, 'group_id' => $group->id]
                );
            }

            // Genera los 6 partidos de la fase de grupos (round-robin de 4 equipos).
            for ($i = 0; $i < count($teamModels); $i++) {
                for ($j = $i + 1; $j < count($teamModels); $j++) {
                    Fixture::firstOrCreate([
                        'stage'        => 'group',
                        'group_id'     => $group->id,
                        'home_team_id' => $teamModels[$i]->id,
                        'away_team_id' => $teamModels[$j]->id,
                    ], [
                        'status' => 'scheduled',
                    ]);
                }
            }
        }
    }
}
