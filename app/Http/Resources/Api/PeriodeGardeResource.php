<?php

namespace App\Http\Resources\Api;

use App\Models\PeriodeGarde;
use Illuminate\Http\Request;

/** @mixin PeriodeGarde */
class PeriodeGardeResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'debut' => self::date($this->DEBUT),
            'fin' => self::date($this->FIN),
            'libelle' => $this->libelle,
        ];
    }
}
