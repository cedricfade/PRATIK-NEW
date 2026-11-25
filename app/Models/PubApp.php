<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Bannière publicitaire du carrousel de l'accueil de l'app (images dans images/Heros). */
#[Table(name: 'pubs_app')]
#[Fillable(['titre', 'image', 'lien', 'ordre', 'actif', 'debut', 'fin'])]
class PubApp extends Model
{
    protected function casts(): array
    {
        return ['actif' => 'boolean', 'debut' => 'date', 'fin' => 'date', 'ordre' => 'integer'];
    }

    protected static function booted(): void
    {
        static::deleted(fn (PubApp $pub) => Media::delete('heros', $pub->image));
        static::updated(function (PubApp $pub) {
            if ($pub->wasChanged('image')) {
                Media::delete('heros', $pub->getOriginal('image'));
            }
        });
    }

    /** Bannières à afficher aujourd'hui, dans l'ordre choisi. */
    public function scopeEnLigne(Builder $q): Builder
    {
        $jour = now()->toDateString();

        return $q->where('actif', true)
            ->where(fn ($w) => $w->whereNull('debut')->orWhere('debut', '<=', $jour))
            ->where(fn ($w) => $w->whereNull('fin')->orWhere('fin', '>=', $jour))
            ->orderBy('ordre')
            ->orderByDesc('id');
    }

    public function getImageUrlAttribute(): ?string
    {
        return Media::url('heros', $this->image);
    }

    public function getEnLigneAttribute(): bool
    {
        $jour = now()->toDateString();

        return $this->actif
            && (! $this->debut || $this->debut->toDateString() <= $jour)
            && (! $this->fin || $this->fin->toDateString() >= $jour);
    }
}
