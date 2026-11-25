<?php

namespace App\Http\Resources\Api;

use App\Models\SousCategorie;
use Illuminate\Http\Request;

/** @mixin SousCategorie */
class SousCategorieResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->ID,
            'designation' => $this->DESIGNATION,
            'lien' => $this->LIEN,
            'categorie_id' => $this->CATEGORIE_ID,
            'description' => self::texte(strip_tags((string) $this->DESCRIPTIF)),
            'pub_url' => self::urlAbsolue($this->pub_url),
            'nombre_entreprises' => $this->when(isset($this->nombre_entreprises), fn () => (int) $this->nombre_entreprises),
        ];
    }
}
