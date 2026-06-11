<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fixture extends Model
{
    protected $fillable = [
        'stage', 'group_id', 'home_team_id', 'away_team_id',
        'home_score', 'away_score', 'home_pens', 'away_pens',
        'kickoff_at', 'status', 'external_id', 'label',
    ];

    protected function casts(): array
    {
        return [
            'kickoff_at' => 'datetime',
        ];
    }

    public const STAGES = [
        'group'       => 'Fase de grupos',
        'r32'         => 'Dieciseisavos',
        'r16'         => 'Octavos',
        'qf'          => 'Cuartos de final',
        'sf'          => 'Semifinal',
        'third_place' => 'Tercer puesto',
        'final'       => 'Final',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function isFinished(): bool
    {
        return $this->status === 'finished'
            && $this->home_score !== null
            && $this->away_score !== null;
    }

    /**
     * Equipo ganador (considera penales en eliminatorias). Null si empate/sin jugar.
     */
    public function winnerTeamId(): ?int
    {
        if (! $this->isFinished()) {
            return null;
        }

        if ($this->home_score > $this->away_score) {
            return $this->home_team_id;
        }
        if ($this->away_score > $this->home_score) {
            return $this->away_team_id;
        }

        // Empate: desempate por penales (eliminatorias)
        if ($this->home_pens !== null && $this->away_pens !== null) {
            return $this->home_pens > $this->away_pens
                ? $this->home_team_id
                : $this->away_team_id;
        }

        return null;
    }

    public function loserTeamId(): ?int
    {
        $winner = $this->winnerTeamId();
        if ($winner === null) {
            return null;
        }

        return $winner === $this->home_team_id
            ? $this->away_team_id
            : $this->home_team_id;
    }
}
