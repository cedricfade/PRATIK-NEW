<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Bien immobilier. TYPE_IMMOBILIER_ID : 1 agence location, 2 agence vente,
 * 3 promoteur vente, 4 aménageur foncier. CATEGORIE_IMMOBILIER_ID : appartement, villa…
 */
#[Table(name: 'immobilier', key: 'ID', timestamps: false)]
#[Guarded(['ID'])]
class Immobilier extends Model
{
    /** Image par défaut partagée : jamais supprimée. */
    public const IMAGE_DEFAUT = 'imm_default.jpg';

    protected static function booted(): void
    {
        // Suppression d'un bien : photo principale + toute la galerie.
        static::deleting(function (Immobilier $bien) {
            $bien->photos()->get()->each->delete();
        });

        static::deleted(function (Immobilier $bien) {
            static::supprimerImageSiInutilisee($bien->ILLUSTATION);
        });

        // Changement de la photo principale : l'ancienne est supprimée.
        static::updated(function (Immobilier $bien) {
            if ($bien->wasChanged('ILLUSTATION')) {
                static::supprimerImageSiInutilisee($bien->getOriginal('ILLUSTATION'));
            }
        });
    }

    public static function supprimerImageSiInutilisee(?string $fichier): void
    {
        if (! $fichier || $fichier === self::IMAGE_DEFAUT) {
            return;
        }

        $utilise = static::where('ILLUSTATION', $fichier)->exists()
            || PhotoImmobilier::where('IMAGE', $fichier)->exists();

        if (! $utilise) {
            Media::delete('immobilier', $fichier);
        }
    }

    /** Équipements booléens ("OUI"/"NON") affichés sur la fiche. */
    public const EQUIPEMENTS = [
        'PISCINE' => 'Piscine',
        'JARDIN' => 'Jardin',
        'PARKING' => 'Parking',
        'GARAGE' => 'Garage',
        'MEUBLEE' => 'Meublé',
        'ASCENSEUR' => 'Ascenseur',
        'GROUPE_ELECTROGENE' => 'Groupe électrogène',
        'VIDEO_SURVEILLANCE' => 'Vidéosurveillance',
        'GUERITE' => 'Guérite',
        'INTERPHONE' => 'Interphone',
        'CHAUFFE_EAU' => 'Chauffe-eau',
        'BUANDERIE' => 'Buanderie',
        'BAIE_VITREE' => 'Baies vitrées',
        'SPLIT' => 'Climatisation (split)',
        'SALLE_GYM' => 'Salle de sport',
        'STOCKAGE' => 'Pièce de stockage',
        'EAU' => 'Eau courante',
        'ELECTRICITE' => 'Électricité',
    ];

    /** Supprime le bien, sa fiche "entreprises", sa photo principale et sa galerie. */
    public function supprimer(): void
    {
        $this->entreprise ? $this->entreprise->delete() : $this->delete();
    }

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class, 'ENTREPRISE_ID', 'ID');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PhotoImmobilier::class, 'IMMOBILIER_ID', 'ID');
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(CategorieImmobilier::class, 'CATEGORIE_IMMOBILIER_ID', 'ID');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(TypeImmobilier::class, 'TYPE_IMMOBILIER_ID', 'ID');
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UTILISATEUR', 'id');
    }

    public function scopePublie(Builder $query): Builder
    {
        return $query->where('immobilier.VALIDATION', 'OUI');
    }

    public function scopeLocation(Builder $query): Builder
    {
        return $query->where('immobilier.TYPE_IMMOBILIER_ID', config('pratik.immo_types.agence_location'));
    }

    public function scopeVente(Builder $query): Builder
    {
        return $query->whereIn('immobilier.TYPE_IMMOBILIER_ID', [2, 3, 4]);
    }

    public function scopeLuxe(Builder $query): Builder
    {
        return $query->where('immobilier.LUXE', 'OUI');
    }

    public function getImageUrlAttribute(): ?string
    {
        return Media::url('immobilier', $this->ILLUSTATION ?: 'imm_default.jpg');
    }

    public function getEstLocationAttribute(): bool
    {
        return (int) $this->TYPE_IMMOBILIER_ID === 1;
    }

    public function getPrixAfficheAttribute(): string
    {
        $prix = fcfa($this->PRIX);

        if ($prix === 'Prix sur demande') {
            return $prix;
        }

        return match (strtoupper(trim((string) $this->PERIODE))) {
            'JOUR' => $prix.' / jour',
            'SEMAINE' => $prix.' / semaine',
            'MOIS' => $prix.' / mois',
            default => $this->est_location ? $prix.' / mois' : $prix,
        };
    }

    // Si TITRE / LIEN ont été sélectionnés par jointure, on les utilise sans recharger l'entreprise.
    public function getTitreAttribute(): string
    {
        return (string) ($this->attributes['TITRE'] ?? $this->entreprise?->TITRE ?? 'Bien immobilier');
    }

    public function getLienAttribute(): ?string
    {
        return $this->attributes['LIEN'] ?? $this->entreprise?->LIEN;
    }

    /** @return list<string> */
    public function getEquipementsListeAttribute(): array
    {
        $liste = [];
        foreach (self::EQUIPEMENTS as $col => $label) {
            if (in_array(strtoupper(trim((string) $this->{$col})), ['OUI', '1', 'ON', 'YES'], true)) {
                $liste[] = $label;
            }
        }

        return $liste;
    }

    public function getAGeolocAttribute(): bool
    {
        return (float) $this->LATITUDE !== 0.0 && (float) $this->LONGITUDE !== 0.0;
    }
}
