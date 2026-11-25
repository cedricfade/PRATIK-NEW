<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table(name: 'communes', key: 'ID', timestamps: false)]
#[Guarded(['ID'])]
class Commune extends Model
{
    public function getEstAbidjanAttribute(): bool
    {
        return in_array((int) $this->ID, config('pratik.communes_abidjan'), true);
    }
}
