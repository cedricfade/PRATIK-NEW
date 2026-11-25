<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Rattachement d'une entreprise à une catégorie / sous-catégorie de l'annuaire. */
#[Table(name: 'thematique_annuaire', key: 'ID', timestamps: false)]
#[Guarded(['ID'])]
class ThematiqueAnnuaire extends Model
{
    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class, 'ENTREPRISE_ID', 'ID');
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(CategorieAnnuaire::class, 'CATEGORIE_ID', 'ID');
    }

    public function sousCategorie(): BelongsTo
    {
        return $this->belongsTo(SousCategorie::class, 'SOUS_CATEGORIE_ID', 'ID');
    }
}
