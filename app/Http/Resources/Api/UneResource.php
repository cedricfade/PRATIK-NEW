<?php

namespace App\Http\Resources\Api;

use App\Models\News;
use Illuminate\Http\Request;

/**
 * Une de journal (titrologie).
 *
 * @mixin News
 */
class UneResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->ID,
            'titre' => $this->source?->DESIGNATION ?? $this->entreprise?->TITRE,
            'source' => $this->source ? ['id' => $this->source->ID, 'designation' => $this->source->DESIGNATION] : null,
            'type' => match ((int) $this->TYPE_NEWS_ID) {
                1 => 'quotidien',
                2 => 'hebdomadaire',
                3 => 'mensuel',
                default => null,
            },
            'image_url' => self::urlAbsolue($this->image_url),
            'date_parution' => self::date($this->DATE_PARUTION),
            'numero' => self::texte($this->NUMERO_PARUTION),
            'rang' => $this->RANG,
            'url_web' => $this->entreprise?->LIEN ? url('/news/'.$this->entreprise->LIEN) : null,
        ];
    }
}
