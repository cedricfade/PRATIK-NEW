<?php

namespace App\Http\Resources\Api;

use App\Models\Immobilier;
use Illuminate\Http\Request;

/**
 * Bien immobilier (résumé pour les listes).
 *
 * @mixin Immobilier
 */
class BienResource extends ApiResource
{
    public static function transaction(Immobilier $bien): string
    {
        return $bien->est_location ? 'location' : 'vente';
    }

    public static function typeAnnonceur(?int $type): ?string
    {
        return match ($type) {
            1, 2 => 'agence',
            3 => 'promoteur',
            4 => 'amenageur',
            default => null,
        };
    }

    public function toArray(Request $request): array
    {
        $e = $this->relationLoaded('entreprise') ? $this->entreprise : null;
        $prix = (int) preg_replace('/\D+/', '', (string) $this->PRIX);

        return [
            'id' => $this->ID,
            'titre' => $e?->TITRE,
            'lien' => $e?->LIEN,
            'transaction' => self::transaction($this->resource),
            'type_annonceur' => self::typeAnnonceur($this->TYPE_IMMOBILIER_ID ? (int) $this->TYPE_IMMOBILIER_ID : null),
            'categorie' => $this->whenLoaded('categorie', fn () => $this->categorie ? ['id' => $this->categorie->ID, 'designation' => $this->categorie->DESIGNATION] : null),
            'commune' => $e && $e->relationLoaded('commune') && $e->commune ? new CommuneResource($e->commune) : null,
            'prix' => $prix ?: null,
            'prix_affiche' => $this->prix_affiche,
            'surface_m2' => self::texte($this->SURFACE),
            'pieces' => $this->PIECES ?: null,
            'chambres' => $this->CHAMBRE ?: null,
            'salles_de_bain' => $this->SALLE_BAIN ?: null,
            'luxe' => self::oui($this->LUXE),
            'image_url' => self::urlAbsolue($this->image_url),
            'statut' => self::oui($this->VALIDATION) ? 'publie' : 'en_attente',
            'publie_le' => self::date($this->DATE_LOG),
            'url_web' => $e?->LIEN ? url('/immobilier/proprietes-'.$e->LIEN) : null,
        ];
    }
}
