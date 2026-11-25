<?php

namespace App\Http\Resources\Api;

use App\Models\News;
use Illuminate\Http\Request;

/**
 * Article d'actualité externe.
 *
 * @mixin News
 */
class NewsWebResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->ID,
            'titre' => $this->entreprise?->TITRE,
            'resume' => self::texte(strip_tags((string) $this->RESUME)),
            'image_url' => self::urlAbsolue($this->image_url),
            'lien_article' => self::texte($this->LIEN_WEB),
            'source' => $this->sourceWeb ? [
                'id' => $this->sourceWeb->ID,
                'designation' => $this->sourceWeb->DESIGNATION,
                'logo_url' => self::urlAbsolue($this->sourceWeb->logo_url),
            ] : null,
            'date_parution' => self::date($this->DATE_PARUTION),
        ];
    }
}
