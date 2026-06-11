<?php

namespace App\Services\Results;

use Illuminate\Support\Collection;

interface ResultsProvider
{
    /**
     * Devuelve los partidos del torneo desde la API externa.
     *
     * Cada elemento es un array normalizado:
     * [
     *   'external_id'  => string,      // id del partido en la API
     *   'home_ext'     => string|null, // id del equipo local en la API
     *   'away_ext'     => string|null, // id del equipo visitante en la API
     *   'home_name'    => string|null,
     *   'away_name'    => string|null,
     *   'home_score'   => int|null,
     *   'away_score'   => int|null,
     *   'status'       => string,      // scheduled|live|finished
     *   'kickoff_at'   => string|null, // ISO 8601
     *   'stage'        => string|null, // group|r32|r16|qf|sf|final
     *   'group'        => string|null, // A..L
     * ]
     *
     * @return Collection<int, array>
     */
    public function matches(): Collection;
}
