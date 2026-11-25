<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Fiche détaillée d'une entreprise de l'annuaire (contacts, logo, réseaux…). */
#[Table(name: 'annuaires', key: 'ID', timestamps: false)]
#[Guarded(['ID'])]
class Annuaire extends Model
{
    /** Logos génériques de l'ancien site (≈ 32 000 fiches) : équivalent à "pas de logo". */
    public const MOTIF_LOGO_PAR_DEFAUT = '/^(logo_entrprise_default|default-logo-flou\d*|default)\.(png|jpe?g)$/i';

    public static function estLogoParDefaut(?string $fichier): bool
    {
        return $fichier === null || trim($fichier) === '' || (bool) preg_match(self::MOTIF_LOGO_PAR_DEFAUT, trim($fichier));
    }

    protected static function booted(): void
    {
        // Suppression d'une fiche : on efface aussi logo, galerie et départements.
        static::deleting(function (Annuaire $annuaire) {
            $annuaire->photos()->get()->each->delete();
            $annuaire->departements()->delete();

            foreach (['LOGO', 'LOGO1'] as $col) {
                $annuaire->supprimerFichierSiInutilise($annuaire->{$col});
            }
        });

        // Remplacement du logo : l'ancien fichier est supprimé.
        static::updated(function (Annuaire $annuaire) {
            foreach (['LOGO', 'LOGO1'] as $col) {
                if ($annuaire->wasChanged($col)) {
                    $annuaire->supprimerFichierSiInutilise($annuaire->getOriginal($col));
                }
            }
        });
    }

    private function supprimerFichierSiInutilise(?string $fichier): void
    {
        if (self::estLogoParDefaut($fichier)) {
            return; // fichier partagé, jamais supprimé
        }

        $utilise = static::whereKeyNot($this->ID)
            ->where(fn ($q) => $q->where('LOGO', $fichier)->orWhere('LOGO1', $fichier))
            ->exists();

        if (! $utilise) {
            Media::delete('logos', $fichier);
        }
    }

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class, 'ENTREPRISE_ID', 'ID');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PhotoAnnuaire::class, 'ANNUAIRE_ID', 'ID');
    }

    public function departements(): HasMany
    {
        return $this->hasMany(Departement::class, 'ANNUAIRE_ID', 'ID');
    }

    public function succursales(): HasMany
    {
        return $this->hasMany(Annuaire::class, 'SIEGE_ID', 'ENTREPRISE_ID');
    }

    public function nature(): BelongsTo
    {
        return $this->belongsTo(Nature::class, 'NATURE_ID', 'ID');
    }

    public function getLogoUrlAttribute(): ?string
    {
        foreach ([$this->LOGO, $this->LOGO1] as $logo) {
            if (! self::estLogoParDefaut($logo)) {
                return Media::url('logos', $logo);
            }
        }

        return null;
    }

    /**
     * Pro Pratik : fiche cochée VIP dans le backoffice.
     * Badge couronne « TOP 12 », bloc Top 12 de la rubrique, tête de liste.
     */
    public function getEstClientAttribute(): bool
    {
        return (int) $this->VIP === 1;
    }

    public function getEstTop12Attribute(): bool
    {
        return $this->est_client;
    }

    /** @return list<string> */
    public function getTelephonesAttribute(): array
    {
        return collect([$this->TEL_1, $this->TEL_2, $this->CEL_1, $this->CEL_2])
            ->map(fn ($t) => trim((string) $t))
            ->filter(fn ($t) => $t !== '' && $t !== '0')
            ->unique()
            ->values()
            ->all();
    }

    /** @return list<string> */
    public function getEmailsAttribute(): array
    {
        return collect([$this->EMAIL_1, $this->EMAIL_2])
            ->map(fn ($e) => trim((string) $e))
            ->filter(fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();
    }

    public function getSiteUrlAttribute(): ?string
    {
        $site = trim((string) $this->SITE);

        if ($site === '') {
            return null;
        }

        return preg_match('#^https?://#i', $site) ? $site : 'https://'.$site;
    }

    public function getAGeolocAttribute(): bool
    {
        return (float) $this->LATITUDE !== 0.0 && (float) $this->LONGITUDE !== 0.0;
    }
}
