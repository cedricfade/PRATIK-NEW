<?php

namespace App\Http\Resources\Api;

use App\Models\CategorieAnnuaire;
use Illuminate\Http\Request;

/** @mixin CategorieAnnuaire */
class CategorieAnnuaireResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->ID,
            'designation' => $this->DESIGNATION,
            'lien' => $this->LIEN,
            'description' => self::texte($this->DESCRIPTION),
            'nombre_entreprises' => (int) ($this->nombre_entreprises ?? 0),
        ];
    }
}
