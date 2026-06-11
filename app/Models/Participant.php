<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Participant extends Model
{
    protected $fillable = ['name', 'slug', 'position'];

    /** Paleta de colores distinguibles sobre el tema oscuro (uno por participante). */
    private const PALETTE = [
        '#22d3ee', '#f4c430', '#f87171', '#60a5fa', '#34d399', '#c084fc',
        '#fb923c', '#f472b6', '#a3e635', '#2dd4bf', '#fbbf24', '#818cf8',
    ];

    /** Color asignado a este participante (por su posición). */
    public function color(): string
    {
        $i = (((int) $this->position) - 1) % count(self::PALETTE);
        if ($i < 0) {
            $i += count(self::PALETTE);
        }

        return self::PALETTE[$i];
    }

    protected static function booted(): void
    {
        static::saving(function (Participant $participant) {
            if (empty($participant->slug)) {
                $participant->slug = static::uniqueSlug($participant->name);
            }
        });
    }

    /**
     * Equipos asignados a este participante.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'assignments')->withTimestamps();
    }

    private static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'participante';
        $slug = $base;
        $i = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
