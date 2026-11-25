<?php

namespace App\Http\Resources\Api;

use App\Models\Entreprise;
use Illuminate\Http\Request;

/**
 * Fiche annuaire complète.
 *
 * @mixin Entreprise
 */
class EntrepriseDetailResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $a = $this->annuaire;

        return [
            'id' => $this->ID,
            'titre' => $this->TITRE,
            'sigle' => self::texte($this->SIGLE),
            'lien' => $this->LIEN,
            'activite' => self::texte($this->ACTIVITE),
            'commune' => $this->commune ? new CommuneResource($this->commune) : null,
            'logo_url' => self::urlAbsolue($a?->logo_url),
            'est_client' => (bool) $a?->est_client,
            'top12' => (bool) $a?->est_top12, // pro VIP : afficher la couronne « TOP 12 »
            'vues' => $this->vues_affichees,
            'telephones' => $a?->telephones ?? [],
            'emails' => $a?->emails ?? [],
            'whatsapp' => self::texte($a?->WHATSAPP),
            'fax' => self::texte($a?->FAX),
            'site' => $a?->site_url,
            'service_client' => self::texte($a?->SERVICE_CLIENT),
            'reseaux' => array_filter([
                'facebook' => self::texte($a?->FACEBOOK),
                'twitter' => self::texte($a?->TWEETER),
                'instagram' => self::texte($a?->INSTAGRAM),
                'linkedin' => self::texte($a?->LINKEDIN),
            ]),
            'adresse' => self::texte($a?->ADRESSE),
            'boite_postale' => self::texte($a?->BP),
            'localisation' => self::texte($a?->LOCALISATION),
            'latitude' => self::coordonnee($a?->LATITUDE),
            'longitude' => self::coordonnee($a?->LONGITUDE),
            'resume' => self::texte($a?->RESUME),
            'description' => self::texte($a?->DESCRIPTION),
            'activites' => self::texte($a?->ACTIVITES),
            'forme_juridique' => self::texte($a?->FORME_JURIDIQUE),
            'video' => self::texte($a?->VIDEO),
            'galerie' => $a ? $a->photos->map(fn ($p) => ['id' => $p->ID, 'url' => self::urlAbsolue($p->url)])->values() : [],
            'departements' => $a ? $a->departements->map(fn ($d) => [
                'libelle' => $d->LIBELLE,
                'telephone' => self::texte($d->TEL) ?? self::texte($d->CEL),
                'email' => self::texte($d->EMAIL),
            ])->values() : [],
            // Agences et succursales rattachées à cette entreprise (siège).
            'succursales' => $a ? $a->succursales
                ->filter(fn ($s) => $s->entreprise && $s->entreprise->LIEN && (int) $s->ENTREPRISE_ID !== (int) $this->ID)
                ->take(30)
                ->map(fn ($s) => [
                    'titre' => $s->entreprise->TITRE,
                    'lien' => $s->entreprise->LIEN,
                    'commune' => $s->entreprise->commune?->DESIGNATION,
                    'adresse' => self::texte($s->ADRESSE),
                    'telephone' => self::texte($s->TEL_1) ?? self::texte($s->CEL_1),
                ])->values() : [],
            'thematiques' => $this->sousCategories->map(fn ($s) => [
                'id' => $s->ID,
                'designation' => $s->DESIGNATION,
                'lien' => $s->LIEN,
                'categorie' => $s->categorie ? ['id' => $s->categorie->ID, 'designation' => $s->categorie->DESIGNATION, 'lien' => $s->categorie->LIEN] : null,
            ])->unique('id')->values(),
            'url_web' => url('/details/'.$this->LIEN),
        ];
    }
}
