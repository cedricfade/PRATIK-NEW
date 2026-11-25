<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Une pharmacie (entreprise) de garde entre DEBUT_GARDE et FIN_GARDE. */
#[Table(name: 'pharmacie_garde', key: 'ID', timestamps: false)]
#[Guarded(['ID'])]
class PharmacieGarde extends Model
{
    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class, 'ENTREPRISE_ID', 'ID');
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UTILISATEUR', 'id');
    }

    public function scopeEnCours(Builder $query, ?string $date = null): Builder
    {
        $date ??= now()->toDateString();

        return $query->where('pharmacie_garde.DEBUT_GARDE', '<=', $date)
            ->where('pharmacie_garde.FIN_GARDE', '>=', $date);
    }
}
