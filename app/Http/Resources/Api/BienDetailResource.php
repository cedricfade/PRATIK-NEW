<?php

namespace App\Http\Resources\Api;

use App\Models\Immobilier;
use Illuminate\Http\Request;

/**
 * Bien immobilier complet.
 *
 * @mixin Immobilier
 */
class BienDetailResource extends BienResource
{
    public function toArray(Request $request): array
    {
        $base = parent::toArray($request);
        $annonceur = $this->relationLoaded('auteur') ? $this->auteur : null;
        $fiche = $annonceur && $annonceur->relationLoaded('entreprise') ? $annonceur->entreprise : null;
        $annuaire = $fiche && $fiche->relationLoaded('annuaire') ? $fiche->annuaire : null;

        $equipements = [];
        foreach (Immobilier::EQUIPEMENTS as $colonne => $libelle) {
            $equipements[strtolower($colonne)] = self::oui($this->{$colonne});
        }

        return $base + [
            'matricule' => self::texte($this->MATRICULE),
            'prix_m2' => self::texte($this->PRIX_M2),
            'salons' => $this->SALONS ?: null,
            'etat' => self::texte($this->ETAT_BIEN),
            'age' => $this->AGE ?: null,
            'adresse' => self::texte($this->ADRESSE),
            'latitude' => self::coordonnee($this->LATITUDE),
            'longitude' => self::coordonnee($this->LONGITUDE),
            'description' => self::texte($this->DESCRIPTION),
            'video' => self::texte($this->VIDEO),
            'video_3d' => self::texte($this->VIDEOS_3D),
            'equipements' => $equipements,
            'equipements_liste' => $this->equipements_liste,
            'photos' => $this->whenLoaded('photos', fn () => $this->photos->map(fn ($p) => [
                'id' => $p->ID,
                'url' => self::urlAbsolue($p->url),
            ])->values()),
            'vues' => (int) ($this->entreprise?->vues_affichees ?? 0),
            'annonceur' => [
                'id' => $annonceur?->id,
                'entreprise_id' => $fiche?->ID,
                'nom' => $fiche?->TITRE ?? self::texte($this->V_NOM) ?? $annonceur?->name,
                'logo_url' => self::urlAbsolue($annuaire?->logo_url),
                'telephones' => $annuaire?->telephones ?: array_values(array_filter([
                    self::texte($this->V_TEL), self::texte($this->V_CEL), self::texte($annonceur?->agent_tel), self::texte($annonceur?->agent_cel),
                ])),
                'emails' => $annuaire?->emails ?: array_values(array_filter([self::texte($this->V_EMAIL) ?? self::texte($annonceur?->email)])),
                'whatsapp' => self::texte($this->V_WHATSAPP) ?? self::texte($annuaire?->WHATSAPP),
                'lien_annuaire' => $fiche?->LIEN ? url('/details/'.$fiche->LIEN) : null,
            ],
        ];
    }
}
