<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CommuneResource;
use App\Models\Commune;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class ReferenceController extends Controller
{
    public function communes(): AnonymousResourceCollection
    {
        $communes = Cache::remember('api:communes', 86400, fn () => Commune::where('DESIGNATION', '!=', '')
            ->orderBy('DESIGNATION')
            ->get(['ID', 'DESIGNATION', 'LIEN']));

        return CommuneResource::collection($communes);
    }
}
