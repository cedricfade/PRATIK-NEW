<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ApiResource;
use App\Support\PubLancement;
use Illuminate\Http\JsonResponse;

/** GET /api/v1/lancement — publicité plein écran à afficher à l'ouverture de l'app (ou null). */
class LancementController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $pub = PubLancement::active();

        if ($pub) {
            $pub['image_url'] = ApiResource::urlAbsolue($pub['image_url']);
        }

        return response()->json(['data' => ['pub' => $pub]]);
    }
}
