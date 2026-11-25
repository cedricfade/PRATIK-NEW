<?php

namespace App\Http\Resources\Api;

use App\Models\VideoInfo;
use Illuminate\Http\Request;

/** @mixin VideoInfo */
class VideoResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titre' => $this->libelle,
            'youtube_id' => $this->youtube_id,
            'embed_url' => $this->embed_url,
            'vignette_url' => $this->vignette,
            'publie_le' => self::dateHeure($this->created_at),
        ];
    }
}
