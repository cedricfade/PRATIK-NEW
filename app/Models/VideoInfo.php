<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

/** Vidéos d'actualité (liens YouTube "embed"). */
#[Table(name: 'videoinfos')]
#[Fillable(['libelle', 'lien'])]
class VideoInfo extends Model
{
    public function getYoutubeIdAttribute(): ?string
    {
        if (preg_match('~(?:embed/|v=|youtu\.be/|shorts/)([A-Za-z0-9_-]{11})~', (string) $this->lien, $m)) {
            return $m[1];
        }

        return null;
    }

    public function getEmbedUrlAttribute(): string
    {
        return $this->youtube_id ? 'https://www.youtube-nocookie.com/embed/'.$this->youtube_id : (string) $this->lien;
    }

    public function getVignetteAttribute(): ?string
    {
        return $this->youtube_id ? 'https://i.ytimg.com/vi/'.$this->youtube_id.'/hqdefault.jpg' : null;
    }
}
