<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Message d'un internaute à une entreprise / un annonceur (boîte de réception pro). */
#[Table(name: 'contact_entreprises')]
#[Guarded(['id'])]
class ContactEntreprise extends Model
{
    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id', 'ID');
    }

    public function getEstLuAttribute(): bool
    {
        return (int) $this->status > 0;
    }
}
