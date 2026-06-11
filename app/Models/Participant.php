<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Participant extends Model
{
    protected $fillable = ['name', 'slug', 'position'];

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
