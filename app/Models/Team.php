<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Team extends Model
{
    protected $fillable = [
        'name', 'code', 'flag', 'group_id', 'external_id', 'eliminated_at',
    ];

    protected function casts(): array
    {
        return [
            'eliminated_at' => 'datetime',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Asignación (a qué participante pertenece este equipo).
     */
    public function assignment(): HasOne
    {
        return $this->hasOne(Assignment::class);
    }

    public function owner(): ?Participant
    {
        return $this->assignment?->participant;
    }

    public function isEliminated(): bool
    {
        return $this->eliminated_at !== null;
    }

    /**
     * URL de la bandera vía flagcdn.com (flagpedia). Deriva el código ISO-3166-1
     * alpha-2 del emoji guardado; null si no se puede (se usa el emoji como fallback).
     *
     * @param int $w ancho en px soportado por flagcdn (20,40,80,160,...)
     */
    public function flagUrl(int $w = 40): ?string
    {
        $iso = $this->iso2Code();

        return $iso ? "https://flagcdn.com/w{$w}/{$iso}.png" : null;
    }

    /** Código ISO-2 (o subdivisión gb-eng/sct/wls) para flagcdn. */
    public function iso2Code(): ?string
    {
        $flag = (string) $this->flag;

        // Los emoji de bandera son 2 "regional indicators" (U+1F1E6..U+1F1FF = A..Z).
        $letters = '';
        foreach (preg_split('//u', $flag, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $char) {
            $cp = mb_ord($char, 'UTF-8');
            if ($cp >= 0x1F1E6 && $cp <= 0x1F1FF) {
                $letters .= chr(ord('a') + ($cp - 0x1F1E6));
            }
        }
        if (strlen($letters) === 2) {
            return $letters;
        }

        // Subdivisiones del Reino Unido (banderas con secuencia de etiquetas).
        return match ($this->code) {
            'ENG' => 'gb-eng',
            'SCO' => 'gb-sct',
            'WAL' => 'gb-wls',
            default => null,
        };
    }
}
