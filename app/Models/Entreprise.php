<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * Table pivot de tout le site : chaque fiche annuaire, bien immobilier,
 * news ou pharmacie possède une ligne "entreprises" (titre, lien SEO, commune).
 */
#[Table(name: 'entreprises', key: 'ID', timestamps: false)]
#[Guarded(['ID'])]
class Entreprise extends Model
{
    /**
     * Supprimer une entreprise supprime tout ce qui lui est rattaché
     * (fiche annuaire + logo + galerie, bien immobilier + photos, news + image,
     * thématiques, gardes). Toujours supprimer via le modèle ($entreprise->delete())
     * et non par requête de masse, sinon les fichiers resteraient sur le disque.
     */
    protected static function booted(): void
    {
        static::deleting(function (Entreprise $entreprise) {
            $entreprise->annuaire()->get()->each->delete();
            $entreprise->immobilier()->get()->each->delete();
            $entreprise->news()->get()->each->delete();
            $entreprise->thematiques()->delete();
            $entreprise->gardes()->delete();
            Annuaire::where('SIEGE_ID', $entreprise->ID)->update(['SIEGE_ID' => null]);
        });
    }

    public function annuaire(): HasOne
    {
        return $this->hasOne(Annuaire::class, 'ENTREPRISE_ID', 'ID');
    }

    public function immobilier(): HasOne
    {
        return $this->hasOne(Immobilier::class, 'ENTREPRISE_ID', 'ID');
    }

    public function news(): HasOne
    {
        return $this->hasOne(News::class, 'ENTREPRISE_ID', 'ID');
    }

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class, 'COMMUNE_ID', 'ID');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(TypeEntreprise::class, 'TYPE_ID', 'ID');
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UTILISATEUR', 'id');
    }

    public function thematiques(): HasMany
    {
        return $this->hasMany(ThematiqueAnnuaire::class, 'ENTREPRISE_ID', 'ID');
    }

    public function sousCategories(): BelongsToMany
    {
        return $this->belongsToMany(SousCategorie::class, 'thematique_annuaire', 'ENTREPRISE_ID', 'SOUS_CATEGORIE_ID', 'ID', 'ID');
    }

    public function gardes(): HasMany
    {
        return $this->hasMany(PharmacieGarde::class, 'ENTREPRISE_ID', 'ID');
    }

    public function scopeFichesAnnuaire(Builder $query): Builder
    {
        return $query->where('entreprises.TYPE_ID', config('pratik.types.annuaire'));
    }

    /**
     * Recherche par mots-clés. Utilise l'index FULLTEXT (TITRE, SIGLE, ACTIVITE)
     * pour rester rapide sur ~100 000 lignes ; repli LIKE pour les mots courts
     * (sigles de 2 lettres…) ou si la base ne gère pas le plein texte.
     */
    public function scopeRecherche(Builder $query, string $terme): Builder
    {
        $mots = collect(preg_split('/[\s\-\'’]+/u', mb_strtolower(trim($terme))))
            ->map(fn ($m) => preg_replace('/[^\p{L}\p{N}]+/u', '', $m))
            ->filter(fn ($m) => mb_strlen($m) >= 3)
            ->take(6);

        $driver = $query->getConnection()->getDriverName();

        if ($mots->isNotEmpty() && in_array($driver, ['mysql', 'mariadb'], true)) {
            $booleen = $mots->map(fn ($m) => '+'.$m.'*')->implode(' ');

            return $query->whereFullText(
                ['entreprises.TITRE', 'entreprises.SIGLE', 'entreprises.ACTIVITE'],
                $booleen,
                ['mode' => 'boolean'],
            );
        }

        $terme = trim($terme);

        return $query->where(function (Builder $q) use ($terme) {
            $q->where('entreprises.TITRE', 'like', "{$terme}%")
                ->orWhere('entreprises.SIGLE', 'like', "{$terme}%");
        });
    }

    public function incrementerVues(): void
    {
        static::whereKey($this->ID)->increment('COMPTEUR');

        // La valeur affichée inclut la visite en cours.
        if (array_key_exists('COMPTEUR', $this->attributes)) {
            $this->COMPTEUR = (int) $this->COMPTEUR + 1;
            $this->syncOriginalAttribute('COMPTEUR');
        }
    }

    /** Génère un lien SEO unique, au format de l'ancien site ("titre-du-bien-1234"). */
    public static function lienUnique(string $titre, ?int $id = null): string
    {
        $base = Str::slug($titre) ?: 'fiche';

        if ($id) {
            return $base.'-'.$id;
        }

        $lien = $base;
        $i = 1;
        while (static::where('LIEN', $lien)->exists()) {
            $lien = $base.'-'.(++$i);
        }

        return $lien;
    }

    /**
     * Vues affichées au public : vues réelles (COMPTEUR) + vues ajoutées depuis le backoffice.
     * Sélectionner COMPTEUR et VUES_BONUS dans les requêtes qui affichent ce chiffre.
     */
    public function getVuesAfficheesAttribute(): int
    {
        return (int) ($this->attributes['COMPTEUR'] ?? 0) + (int) ($this->attributes['VUES_BONUS'] ?? 0);
    }

    public function getNomAttribute(): string
    {
        return (string) ($this->TITRE ?: $this->SIGLE);
    }
}
