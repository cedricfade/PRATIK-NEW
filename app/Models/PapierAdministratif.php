<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table(name: 'papier_administratif', key: 'ID', timestamps: false)]
#[Guarded(['ID'])]
class PapierAdministratif extends Model
{
    protected static function booted(): void
    {
        static::deleting(function (PapierAdministratif $papier) {
            $papier->pieces()->delete();
        });

        static::deleted(fn (PapierAdministratif $papier) => Media::delete('papiers', $papier->IMAGE));

        static::updated(function (PapierAdministratif $papier) {
            if ($papier->wasChanged('IMAGE')) {
                Media::delete('papiers', $papier->getOriginal('IMAGE'));
            }
        });
    }

    public function pieces(): HasMany
    {
        return $this->hasMany(PapierAdministratifPiece::class, 'PAPIER_ID', 'ID')->orderBy('RANG');
    }

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class, 'COMMUNE_ID', 'ID');
    }

    public function getImageUrlAttribute(): ?string
    {
        return Media::url('papiers', $this->IMAGE);
    }
}
