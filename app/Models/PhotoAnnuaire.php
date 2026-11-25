<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table(name: 'photo_annuaire', key: 'ID', timestamps: false)]
#[Guarded(['ID'])]
class PhotoAnnuaire extends Model
{
    protected static function booted(): void
    {
        static::deleted(function (PhotoAnnuaire $photo) {
            if ($photo->IMAGE && ! static::where('IMAGE', $photo->IMAGE)->exists()) {
                Media::delete('galerie', $photo->IMAGE);
            }
        });
    }

    public function getUrlAttribute(): ?string
    {
        return Media::url('galerie', $this->IMAGE);
    }
}
