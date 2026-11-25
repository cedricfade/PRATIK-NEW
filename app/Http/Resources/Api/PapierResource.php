<?php

namespace App\Http\Resources\Api;

use App\Models\PapierAdministratif;
use Illuminate\Http\Request;

/** @mixin PapierAdministratif */
class PapierResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->ID,
            'titre' => $this->TITRE,
            'description' => self::texte($this->DESCRIPTION),
            'lien' => self::texte($this->LIEN),
            'image_url' => self::urlAbsolue($this->image_url),
            'commune_id' => $this->COMMUNE_ID,
            'pieces' => $this->pieces->pluck('LIBELLE')->values(),
        ];
    }
}
