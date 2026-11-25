<?php

namespace App\Http\Resources\Api;

use App\Models\Entreprise;
use Illuminate\Http\Request;

/**
 * Pharmacie de garde (ligne "entreprises" + fiche annuaire).
 *
 * @mixin Entreprise
 */
class PharmacieResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $a = $this->relationLoaded('annuaire') ? $this->annuaire : null;

        return [
            'id' => $this->ID,
            'titre' => $this->TITRE,
            'lien' => $this->LIEN,
            'commune' => $this->whenLoaded('commune', fn () => $this->commune ? new CommuneResource($this->commune) : null),
            'adresse' => self::texte($a?->ADRESSE),
            'localisation' => self::texte($a?->LOCALISATION),
            'telephones' => $a?->telephones ?? [],
            'emails' => $a?->emails ?? [],
            'latitude' => self::coordonnee($a?->LATITUDE),
            'longitude' => self::coordonnee($a?->LONGITUDE),
            'distance_km' => $this->when(isset($this->distance_km), fn () => round((float) $this->distance_km, 2)),
        ];
    }
}
