<?php

namespace App\Http\Resources\Api;

use App\Models\CategorieImmobilier;
use Illuminate\Http\Request;

/** @mixin CategorieImmobilier */
class CategorieImmobilierResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->ID,
            'designation' => $this->DESIGNATION,
            'nombre_biens' => $this->when(isset($this->nombre_biens), fn () => (int) $this->nombre_biens),
        ];
    }
}
