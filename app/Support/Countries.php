<?php

namespace App\Support;

/**
 * Mapeo de nombres de país (como los devuelve football-data.org, en inglés)
 * a nombre en español + bandera emoji, para que la app se vea futbolera.
 *
 * Si un país no está en el mapa, se conserva el nombre original y sin bandera.
 */
class Countries
{
    /** @var array<string, array{0:string,1:string}> nombre_inglés => [español, bandera] */
    private const MAP = [
        'Argentina'            => ['Argentina', '🇦🇷'],
        'Algeria'              => ['Argelia', '🇩🇿'],
        'Australia'            => ['Australia', '🇦🇺'],
        'Austria'              => ['Austria', '🇦🇹'],
        'Belgium'              => ['Bélgica', '🇧🇪'],
        'Bosnia-Herzegovina'   => ['Bosnia y Herzegovina', '🇧🇦'],
        'Brazil'               => ['Brasil', '🇧🇷'],
        'Canada'               => ['Canadá', '🇨🇦'],
        'Cape Verde Islands'   => ['Cabo Verde', '🇨🇻'],
        'Cape Verde'           => ['Cabo Verde', '🇨🇻'],
        'Colombia'             => ['Colombia', '🇨🇴'],
        'Congo DR'             => ['RD del Congo', '🇨🇩'],
        'DR Congo'             => ['RD del Congo', '🇨🇩'],
        'Croatia'              => ['Croacia', '🇭🇷'],
        'Curaçao'              => ['Curazao', '🇨🇼'],
        'Czechia'              => ['Chequia', '🇨🇿'],
        'Czech Republic'       => ['Chequia', '🇨🇿'],
        'Denmark'              => ['Dinamarca', '🇩🇰'],
        'Ecuador'              => ['Ecuador', '🇪🇨'],
        'Egypt'                => ['Egipto', '🇪🇬'],
        'England'              => ['Inglaterra', '🏴󠁧󠁢󠁥󠁮󠁧󠁿'],
        'France'               => ['Francia', '🇫🇷'],
        'Germany'              => ['Alemania', '🇩🇪'],
        'Ghana'                => ['Ghana', '🇬🇭'],
        'Haiti'                => ['Haití', '🇭🇹'],
        'Iran'                 => ['Irán', '🇮🇷'],
        'Iraq'                 => ['Irak', '🇮🇶'],
        'Italy'                => ['Italia', '🇮🇹'],
        'Ivory Coast'          => ['Costa de Marfil', '🇨🇮'],
        'Japan'                => ['Japón', '🇯🇵'],
        'Jordan'               => ['Jordania', '🇯🇴'],
        'Mexico'               => ['México', '🇲🇽'],
        'Morocco'              => ['Marruecos', '🇲🇦'],
        'Netherlands'          => ['Países Bajos', '🇳🇱'],
        'New Zealand'          => ['Nueva Zelanda', '🇳🇿'],
        'Nigeria'              => ['Nigeria', '🇳🇬'],
        'Norway'               => ['Noruega', '🇳🇴'],
        'Panama'               => ['Panamá', '🇵🇦'],
        'Paraguay'             => ['Paraguay', '🇵🇾'],
        'Portugal'             => ['Portugal', '🇵🇹'],
        'Qatar'                => ['Catar', '🇶🇦'],
        'Saudi Arabia'         => ['Arabia Saudita', '🇸🇦'],
        'Scotland'             => ['Escocia', '🏴󠁧󠁢󠁳󠁣󠁴󠁿'],
        'Senegal'              => ['Senegal', '🇸🇳'],
        'South Africa'         => ['Sudáfrica', '🇿🇦'],
        'South Korea'          => ['Corea del Sur', '🇰🇷'],
        'Korea Republic'       => ['Corea del Sur', '🇰🇷'],
        'Spain'                => ['España', '🇪🇸'],
        'Sweden'               => ['Suecia', '🇸🇪'],
        'Switzerland'          => ['Suiza', '🇨🇭'],
        'Tunisia'              => ['Túnez', '🇹🇳'],
        'Turkey'               => ['Turquía', '🇹🇷'],
        'Türkiye'              => ['Turquía', '🇹🇷'],
        'United States'        => ['Estados Unidos', '🇺🇸'],
        'Uruguay'              => ['Uruguay', '🇺🇾'],
        'Uzbekistan'           => ['Uzbekistán', '🇺🇿'],
        'Wales'                => ['Gales', '🏴󠁧󠁢󠁷󠁬󠁳󠁿'],
        'Cameroon'             => ['Camerún', '🇨🇲'],
        'Honduras'             => ['Honduras', '🇭🇳'],
        'Jamaica'              => ['Jamaica', '🇯🇲'],
        'Costa Rica'           => ['Costa Rica', '🇨🇷'],
    ];

    /**
     * Devuelve [nombre_español, bandera] o [nombre_original, ''] si no está mapeado.
     *
     * @return array{0:string,1:string}
     */
    public static function resolve(string $englishName): array
    {
        return self::MAP[$englishName] ?? [$englishName, ''];
    }
}
