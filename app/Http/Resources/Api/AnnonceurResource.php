<?php

namespace App\Http\Resources\Api;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * Professionnel de l'immobilier (compte role 5 + sa fiche entreprise).
 *
 * @mixin User
 */
class AnnonceurResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $fiche = $this->entreprise;
        $annuaire = $fiche?->annuaire;

        return [
            'id' => $this->id,
            'entreprise_id' => $fiche?->ID,
            'nom' => $fiche?->TITRE ?? $this->name,
            'lien' => $fiche?->LIEN,
            'logo_url' => self::urlAbsolue($annuaire?->logo_url),
            'commune' => $fiche?->commune ? new CommuneResource($fiche->commune) : null,
            'telephones' => $annuaire?->telephones ?? array_values(array_filter([self::texte($this->agent_tel), self::texte($this->agent_cel)])),
            'emails' => $annuaire?->emails ?? [],
            'nombre_biens' => (int) ($this->nombre_biens ?? 0),
        ];
    }
}
