<?php

namespace App\Http\Resources\Api;

use App\Models\Commune;
use Illuminate\Http\Request;

/** @mixin Commune */
class CommuneResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->ID,
            'designation' => $this->DESIGNATION,
            'lien' => $this->LIEN,
            'abidjan' => in_array((int) $this->ID, config('pratik.communes_abidjan'), true),
        ];
    }
}
