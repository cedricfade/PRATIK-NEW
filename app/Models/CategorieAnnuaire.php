<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table(name: 'categorie_annuaires', key: 'ID', timestamps: false)]
#[Guarded(['ID'])]
class CategorieAnnuaire extends Model
{
    public function sousCategories(): HasMany
    {
        return $this->hasMany(SousCategorie::class, 'CATEGORIE_ID', 'ID');
    }
}
