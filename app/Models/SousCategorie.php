<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table(name: 'sous_categorie', key: 'ID', timestamps: false)]
#[Guarded(['ID'])]
class SousCategorie extends Model
{
    public function categorie(): BelongsTo
    {
        return $this->belongsTo(CategorieAnnuaire::class, 'CATEGORIE_ID', 'ID');
    }

    public function thematiques(): HasMany
    {
        return $this->hasMany(ThematiqueAnnuaire::class, 'SOUS_CATEGORIE_ID', 'ID');
    }

    public function getPubUrlAttribute(): ?string
    {
        return Media::url('pubs', $this->PUBS);
    }
}
