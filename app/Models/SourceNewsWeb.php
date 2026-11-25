<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table(name: 'source_news_web', key: 'ID', timestamps: false)]
#[Guarded(['ID'])]
class SourceNewsWeb extends Model
{
    protected static function booted(): void
    {
        static::deleted(fn (SourceNewsWeb $source) => Media::delete('news_sources', $source->LOGO));

        static::updated(function (SourceNewsWeb $source) {
            if ($source->wasChanged('LOGO')) {
                Media::delete('news_sources', $source->getOriginal('LOGO'));
            }
        });
    }

    public function getLogoUrlAttribute(): ?string
    {
        return Media::url('news_sources', $this->LOGO);
    }
}
