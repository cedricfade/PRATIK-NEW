<?php

namespace App\Http\Resources\Api;

use App\Models\Entreprise;
use Illuminate\Http\Request;

/**
 * Fiche annuaire résumée (listes, recherche).
 *
 * @mixin Entreprise
 */
class EntrepriseResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $a = $this->relationLoaded('annuaire') ? $this->annuaire : null;

        return [
            'id' => $this->ID,
            'titre' => $this->TITRE,
            'sigle' => self::texte($this->SIGLE),
            'lien' => $this->LIEN,
            'activite' => self::texte($this->ACTIVITE),
            'commune' => $this->whenLoaded('commune', fn () => $this->commune ? new CommuneResource($this->commune) : null),
            'logo_url' => self::urlAbsolue($a?->logo_url),
            'est_client' => (bool) $a?->est_client,
            'top12' => (bool) $a?->est_top12, // pro VIP : afficher la couronne « TOP 12 »
            'telephones' => $a?->telephones ?? [],
            'adresse' => self::texte($a?->ADRESSE),
            'latitude' => self::coordonnee($a?->LATITUDE),
            'longitude' => self::coordonnee($a?->LONGITUDE),
            'url_web' => url('/details/'.$this->LIEN),
        ];
    }
}
