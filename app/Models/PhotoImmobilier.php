<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table(name: 'photo_immobilier', key: 'ID', timestamps: false)]
#[Guarded(['ID'])]
class PhotoImmobilier extends Model
{
    protected static function booted(): void
    {
        static::deleted(function (PhotoImmobilier $photo) {
            Immobilier::supprimerImageSiInutilisee($photo->IMAGE);
        });
    }

    public function getUrlAttribute(): ?string
    {
        return Media::url('immobilier', $this->IMAGE);
    }
}
