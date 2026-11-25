<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CATEGORIE_ID : 1 vidéo, 2 news web (article externe), 3 titrologie (une de journal).
 * TYPE_NEWS_ID (titrologie) : 1 quotidien, 2 hebdomadaire, 3 mensuel.
 */
#[Table(name: 'news', key: 'ID', timestamps: false)]
#[Guarded(['ID'])]
class News extends Model
{
    protected static function booted(): void
    {
        static::deleted(function (News $news) {
            if ($news->IMAGE && ! static::where('IMAGE', $news->IMAGE)->exists()) {
                Media::delete('news', $news->IMAGE);
            }
        });

        static::updated(function (News $news) {
            $ancien = $news->getOriginal('IMAGE');
            if ($news->wasChanged('IMAGE') && $ancien && ! static::where('IMAGE', $ancien)->exists()) {
                Media::delete('news', $ancien);
            }
        });
    }

    /** Supprime la news, sa fiche "entreprises" et son image. */
    public function supprimer(): void
    {
        $this->entreprise ? $this->entreprise->delete() : $this->delete();
    }

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class, 'ENTREPRISE_ID', 'ID');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(SourceNews::class, 'SOURCE_ID', 'ID');
    }

    public function sourceWeb(): BelongsTo
    {
        return $this->belongsTo(SourceNewsWeb::class, 'SOURCE_WEB_ID', 'ID');
    }

    public function typeNews(): BelongsTo
    {
        return $this->belongsTo(TypeNews::class, 'TYPE_NEWS_ID', 'ID');
    }

    public function scopeTitrologie(Builder $query): Builder
    {
        return $query->where('news.CATEGORIE_ID', config('pratik.news_categories.titrologie'));
    }

    public function scopeWeb(Builder $query): Builder
    {
        return $query->where('news.CATEGORIE_ID', config('pratik.news_categories.web'));
    }

    public function getImageUrlAttribute(): ?string
    {
        return Media::url('news', $this->IMAGE);
    }

    // Si TITRE / LIEN ont été sélectionnés par jointure, on les utilise sans recharger l'entreprise.
    public function getTitreAttribute(): string
    {
        return (string) ($this->attributes['TITRE'] ?? $this->entreprise?->TITRE ?? '');
    }

    public function getLienAttribute(): ?string
    {
        return $this->attributes['LIEN'] ?? $this->entreprise?->LIEN;
    }
}
